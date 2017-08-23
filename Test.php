<?php
namespace app\app\controller;


use think\Request;
use think\Db;

use think\Cache;
use think\Config;
use think\Exception;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use think\Session;


class Test extends Base{

    //生成鉴权token
    public function index() {
        // 用于签名的公钥和私钥
        $config = Config("qiniu");
        $accessKey = $config['accesskey'];
        $secretKey = $config['secretkey'];
        // 初始化签权对象
        $auth = new Auth($accessKey, $secretKey);

        // 空间名
        $bucket = $config['bucket'];

        // 生成上传Token
        $token = $auth->uploadToken($bucket);

        Session::set("token",$token);

        ajaxmsg("ok",1,$token);
    }

    //上传一张图片到七牛，上传多张图片到七牛，暂时不支持
    public function upload() {
       //var_dump($_FILES['files']['tmp_name']);
        // 用于签名的公钥和私钥
        $config = Config("qiniu");
        $accessKey = $config['accesskey'];
        $secretKey = $config['secretkey'];
        // 初始化签权对象
        $auth = new Auth($accessKey, $secretKey);

        // 空间名
        $bucket = $config['bucket'];

        // 生成上传Token
        $token = $auth->uploadToken($bucket);

        //ajaxmsg("ok",1);


       //上传到七牛云存储
        // 要上传文件的本地路径
        $filePath = $_FILES['files']['tmp_name'];//'C:\Users\Administrator\Desktop\1111.png';
        //var_dump($filePath);
        // 上传到七牛后保存的文件名
        //$key = $_FILES['files']['name'];
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token ,null,$filePath);
        //echo "\n====> putFile result: \n";
        if ($err !== null) {
            //var_dump($err);
        } else {
            //var_dump($ret);
            //baseUrl构造成私有空间的域名/key的形式
            $baseUrl = 'http://otzv90baq.bkt.clouddn.com/'.$ret['key'];
            $authUrl = $this->privateDownloadUrl($baseUrl);



            ajaxmsg("ok",1,$authUrl);
        }
    }
    //测试删除一张七牛的图片
    public function del() {
        $key="FjhVGDYleflbIcsL7GYCN9i7M7-2";
        $res = $this->delete($key);

        var_dump($res);

    }
    //删除七牛的图片
    public function delete($key) {

        // 用于签名的公钥和私钥
        $config = Config("qiniu");
        $accessKey = $config['accesskey'];
        $secretKey = $config['secretkey'];
        // 空间名
        $bucket = $config['bucket'];
        //初始化Auth状态
        $auth = new Auth($accessKey, $secretKey);
        //初始化BucketManager
        $bucketMgr = new BucketManager($auth);
        //你要测试的空间， 并且这个key在你空间中存在
        //删除$bucket 中的文件 $key
        $err = $bucketMgr->delete($bucket, $key);

        if ($err !== null) {
            return false;
        } else {
            return true;
        }
    }

    public function sign($data)
    {
        // 用于签名的公钥和私钥
        $config = Config("qiniu");
        $accessKey = $config['accesskey'];
        $secretKey = $config['secretkey'];

        $hmac = hash_hmac('sha1', $data, $secretKey, true);
        return $accessKey . ':' . \Qiniu\base64_urlSafeEncode($hmac);
    }

    public function privateDownloadUrl($baseUrl, $expires = 3600)
    {
        $deadline = time() + $expires;
        $pos = strpos($baseUrl, '?');
        if ($pos !== false) {
            $baseUrl .= '&e=';
        } else {
            $baseUrl .= '?e=';
        }
        $baseUrl .= $deadline;
        $token = $this->sign($baseUrl);
        return "$baseUrl&token=$token";
    }
}