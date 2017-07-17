<?php
namespace BrightVie\Library;

// Load Composer Vendor Modules
//require_once('vendor/autoload.php');

/**
 *
 * ```
 * $systemName = 'defaults';
 *
 * // constructにシステム名を入れることで、
 * // システム毎にフォルダを分離することを目的とする
 * $libS3 = new BrightVie\Library\S3($systemName);
 *
 * // 下記のファイルをアップロードする場合
 * //  /tmp/upload-file/defaults/20170717/スタッフ情報_15003012511168.csv
 * $dir  = '/tmp/upload-file/defaults/20170717';
 * $file = 'スタッフ情報_15003012511168.csv';
 *
 * $objUrl = $libS3->putObject($dir, $file);
 * echo $objUrl;
 */

class S3
{
  private $s3;

  private $systemName;

  private $bucketName = 'upload-file';


  /**
   * システム名を受け取ることで、アップロードするバケットのスキーマを決めておく
   *
   * 基本的には、s3://upload-file/{systemName}/{年月日}/ファイル名.php としておく
   */
  function __construct($systemName = 'defaults') {

    $this->systemName = $systemName;
    $region = (getenv('AWS_DEFAULT_REGION'))?getenv('AWS_DEFAULT_REGION'):'ap-northeast-1';

    $this->s3 = \Aws\S3\S3Client::factory(array(
      'version' => '2006-03-01',
      'region'  => $region,
//        'key'    => getenv('AWS_ACCESS_KEY_ID'),
//        'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
//        'region' => getenv('AWS_DEFAULT_REGION'),
    ));
  }

  /**
   * 保存するObjectのS3のパスを取得する
   */
  private function _createObjectPath($fileName) {
    $ext = substr($fileName, strrpos($fileName, '.') + 1);

    $tmp = $this->systemName . time() .  mt_rand(1000,9999) . $fileName;
    $fileHash = hash("sha256", $tmp);
    return $this->systemName . '/' . date('Ymd') . '/' . $fileHash . '.' . $ext;
  }

  function setBucketName($bucketName) {
    $this->bucketName = $bucketName;
  }


  function putObject($uploadFileDir, $uploadFileName) {

    $s3Path = $this->_createObjectPath($uploadFileName);

    $result = $this->s3->putObject(array(
        'Bucket' => $this->bucketName,
        'Key'    => $s3Path,
        'Body'   => fopen($uploadFileDir.'/'.$uploadFileName, 'r')
    ));

    return $result['ObjectURL'];
  }

}


