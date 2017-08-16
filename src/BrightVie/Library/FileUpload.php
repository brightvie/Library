<?php
namespace BrightVie\Library;

/**
 * ファイルのアップロードを扱うためのメソッド
 *
 * POSTにてファイルを送信した際には$_FILESメソッドによって一時的な保管場所に
 * ファイルをアップロードするところはPHPが処理を実施してくれる。
 * そのアップロードされたファイルを指定されたフォルダに保存することをこの処理では実施する
 *
 * ```
 * if (!isset($_FILES['filename']) || $_FILES['filename']['size'] === 0) {
 *   // ファイルを受け取れなかったときのエラー処理
 *
 * }
 *
 * // ライブラリを呼び出す
 * $libFile = new BrightVie\Library\FileUpload();
 *
 * // ファイルをサーバにアップロードする
 * $filePath = $libFile->uploadFile($_FILES['upfile']);
 *
 * // ファイルがアップロードできなかったときのエラー処理
 * if (!$filePath ) {
 *     $e = $libFile->getErrorMessage();
 *     var_dump($e);
 *     exit();
 * }
 *
 * // ex) /tmp/upload-file/defaults/20170717/スタッフ情報_15003012511168.csv
 * echo $filePath;
 * ```
 */
class FileUpload {

  // 扱うシステム名称
  private $systemName = 'defaults';

  // ファイルの保存先
  private $baseUploadDir = '/tmp/upload-file';

  private $errorMessage = null;

  function __construct($systemName = 'defaults', $options = []) {

    // アップロードされるフォルダは最終的に下記の形式で保存される
    // $this->baseUploadDir . '/' . $this->systemName . '/' . 日付(Ymd) . '/' ファイル名 . '_' . time() .  mt_rand(1000,9999) . '.' . 拡張子;
    // ex) /tmp/upload-file/defaults/20170701/スタッフ情報_15002979311000.csv

    // フォルダの分類を指定する場合
    if (isset($options['baseUploadDir'])) {
      $this->baseUploadDir = $options['baseUploadDir'];
    }
    $this->_createDir($this->baseUploadDir);
  }

  /**
   * 指定されたパスにディレクトリを作成する
   */
  private function _createDir($dir) {

    //「$directory_path」で指定されたディレクトリが存在するか確認
    if(file_exists($dir)) {
      //存在したときの処理
      return true;
    } else {
      //存在しないときの処理（「$directory_path」で指定されたディレクトリを作成する）
      if(mkdir($dir, 0777, true)) {
        //作成したディレクトリのパーミッションを確実に変更
        chmod($dir, 0777);
        return true;
      } else {
        //作成に失敗した時の処理
        return false;
      }
    }
  }

  /**
   * アップロードするディレクトリを取得する
   */
  private function _getUploadDir() {
    $path = $this->baseUploadDir . '/' . $this->systemName . '/' . date('Ymd');
    return $path;
  }

  /**
   * アップロードした際のファイル名を取得する
   * -> 基本的に上書き更新はさせない
   *
   * $this->_getUploadFileName($_FILES['upfile']['name']);
   */
  private function _getUploadFileName($fileName, $overwrite = false) {

    if (!isset($fileName)) {
      return false;
    }

    $name      = pathinfo($fileName, PATHINFO_FILENAME);
    $extension = pathinfo($fileName, PATHINFO_EXTENSION);

    if ($overwrite) {
      return $name . '.' . $extension;
    } else {
      return $name . '_' . time() .  mt_rand(1000,9999) . '.' . $extension;
    }
  }



  /**
   * ファイルのアップロード処理
   * falseが返った場合のエラー内容はgetErrorMessageにて取得する
   *
   * 成功した場合は、アップロードしたファイルのパスを返す
   */
  public function uploadFile($fileObj)
  {

    $uploadDir = $this->_getUploadDir();

    if (!$this->_createDir($uploadDir)) {
      $this->errorMessage = 'アップロード先のディレクトリ作成に失敗しました。パスや権限を再度確認してください。';
      return false;
    }

    if (!isset($fileObj)) {
      $this->errorMessage = '$FILEのオブジェクトが引数に渡ってきていません。引数を再度確認ください。';
      return false;
    }

    //一時ファイルができているか（アップロードされているか）チェック
    if(!is_uploaded_file($fileObj['tmp_name'])) {
      //そもそもファイルが来ていない。
      $this->errorMessage = 'tmpフォルダにファイルがアップロードされていません。$_FILEにてファイルが受け取れているか確認ください。';
      return false;
    }

    $uploadPath = $uploadDir . '/' . $this->_getUploadFileName($fileObj['name']);

    //一字ファイルを保存ファイルにコピーできたか
    if(move_uploaded_file($fileObj['tmp_name'], $uploadPath)) {

      return $uploadPath;

    } else {
      //コピーに失敗（だいたい、ディレクトリがないか、パーミッションエラー）
      $this->errorMessage = 'tmpフォルダからアップロード先へのコピーに失敗しました。ディレクトリの存在有無や権限に問題ないか確認してください。';
      return false;
    }
  }

  /**
   * Base64エンコードされた画像ファイルのアップロード処理
   * falseが返った場合のエラー内容はgetErrorMessageにて取得する
   *
   * 成功した場合は、アップロードしたファイルのパスを返す
   */
  public function uploadBase64Image($fileName, $base64Image)
  {
    $uploadDir = $this->_getUploadDir();

    if (!$this->_createDir($uploadDir)) {
      $this->errorMessage = 'アップロード先のディレクトリ作成に失敗しました。パスや権限を再度確認してください。';
      return false;
    }

    // アップロード先を指定
    $uploadPath = $uploadDir . '/' . $this->_getUploadFileName($fileName);

    //POSTデーターの中にbase64で送られるのでPHPがデコードできるように修正
    $from_arr = array( " " , "data:image/png;base64," , "data:image/jpg;base64," , "data:image/jpeg;base64," , "data:image/gif;base64," );
    $to_arr   = array( "+" , "" , "" , "" , "" );
    //base64からバイナリ画像に変換
    $fileData    = base64_decode( str_replace( $from_arr , $to_arr , $base64Image ) );


    // 保存
    if(file_put_contents($uploadPath, $fileData)) {

      return $uploadPath;

    } else {
      //コピーに失敗（だいたい、ディレクトリがないか、パーミッションエラー）
      $this->errorMessage = 'Base64画像からファイルの書き出しに失敗しました。ディレクトリの存在有無や権限に問題ないか確認してください。';
      return false;
    }
  }

  public function getErrorMessage() {
    return $this->errorMessage;
  }

}




