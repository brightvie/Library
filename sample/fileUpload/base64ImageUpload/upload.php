<?php

// FileUploadクラスを呼び出す処理は、各フレームワークの読み込み方に合わせてください
//
// ex) 直接ファイルを読み込む
//   require_once($path . '/FileUpload.php');
//
// ex) composerを利用している場合は、autoloadで呼び出す
//   require ROOT . DS . 'vendors' . DS . 'autoload.php';
//

// 念のため存在チェック
if (!isset($_POST['upfile']) || empty($_POST['upfile'])) {
  echo 'Base64エンコードされた文字列が指定されていません';
  exit();
}

// ライブラリを呼び出す
//$libFile = new \BrightVie\Library\FileUpload();
require '../../../src/BrightVie/Library/FileUpload.php';
$libFile = new \BrightVie\Library\FileUpload();

// ファイルをサーバにアップロードする
$filePath = $libFile->uploadBase64Image('uploadtest.png', $_POST['upfile']);

// ファイルがアップロードできなかったときのエラー処理
if (!$filePath ) {
  $e = $libFile->getErrorMessage();
  var_dump($e);
  exit();
}

echo "ファイルを下記にアップロードしました";
echo $filePath;

