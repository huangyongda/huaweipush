# huaweipush
php服务端  华为推送 huawei push
```
$push=new  huaweiPush("100xxxx53","9c6484exxxxx05eb0a91c49b1bfc2a2d");

// 然后可以这样使用。

$title = '推送的消息标题';

$message = '需要推送的消息内容';

$AccessToken=$push->getAccessToken();//获取AccessToken 可以保存起来
$push->setTitle($title)
    ->setMessage($message)
    ->setAccessToken($AccessToken)
    ->setAppPkgName("com.cug.maintenance") //设置包名称
    ->setCustomize(["你好"]) //设置自定义参数 （点击app后可以应用可获取的参数）
//    ->addDeviceToken('0865831037206556300001986600CN01')
    ->addDeviceToken('0865831037206556300001986600CN01');
$push->sendMessage(); // 执行推送消息。
    
$push->sendMessage(); // 执行推送消息。
var_dump($push->isSendSuccess()); //是否推送成功
var_dump($push->isSendFail()); //是否推送失败
var_dump($push->getAccessTokenExpiresTime()); //获取AccessToken 过期时间
var_dump($push->getSendSuccessRequestId()); //获取推送成功后接口返回的请求id
//$push->test();
```
![image](https://github.com/huangyongda/huaweipush/blob/master/IMG_3057.JPG)
点击后打开
![image](https://github.com/huangyongda/huaweipush/blob/master/IMG_3058.JPG)
