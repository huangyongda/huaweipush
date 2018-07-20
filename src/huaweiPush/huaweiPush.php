<?php
namespace huangyongda\huaweiPush;
use huangyongda\Http\Http;
use huangyongda\Http\Request;
use huangyongda\Http\Response;

/**
 * Created by PhpStorm.
 * User: huangyd
 * Date: 2018/7/12
 * Time: 14:26
 */

class huaweiPush
{
    private $_accessTokenInfo;
    private $_clientId;
    private $_clientSecret;
    private $_http;
    private $title;
    private $message;
    private $deviceToken=[];
    private $AccessToken;
    private $appPkgName;
    /**@var  $response Response **/
    private $response;
    private $Customize;
    private $intent;//开发者可以自定义Intent，用户收到通知栏消息后点击通知栏消息打开应用定义的这个Intent页面

    /**
     * 构造函数。
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct($client_id,$client_secret)
    {
        $this->_clientId=$client_id;
        $this->_clientSecret=$client_secret;
        $this->_http = new Request();
        $this->_http->setHttpVersion(Http::HTTP_VERSION_1_1);
    }

    /**
     * 获取AccessToken信息
     * @return mixed
     * @throws \Exception
     */
    private function getAccessTokenInfo()
    {
        $response = $this->_http->post('https://login.cloud.huawei.com/oauth2/v2/token', [
            'data' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->_clientId,
                'client_secret' => $this->_clientSecret
            ]
        ]);
        $array=$response->getResponseArray();
        $this->_accessTokenInfo=$array;
        return $this->_accessTokenInfo;
    }

    /**
     * 请求新的 Access Token。
     * @return mixed
     * @throws \Exception
     */
    public function getAccessToken()
    {
        if(!$this->_accessTokenInfo){
            $this->_accessTokenInfo=$this->getAccessTokenInfo();
        }
        $access_token="";
        if( isset($this->_accessTokenInfo['access_token']) && $this->_accessTokenInfo['access_token'] ){
            $access_token=$this->_accessTokenInfo['access_token'];
        }
        if(!$access_token){
            throw  new \Exception("获取AccessToken失败");
        }
        return $access_token;
    }

    /**
     * 获取AccessToken过期时间
     * @return string
     */
    public function getAccessTokenExpiresTime()
    {
        if(!$this->_accessTokenInfo){
            $this->_accessTokenInfo=$this->getAccessTokenInfo();
        }
        $access_token="";
        if( isset($this->_accessTokenInfo['expires_in']) && $this->_accessTokenInfo['expires_in'] ){
            $access_token=$this->_accessTokenInfo['expires_in'];
        }
        return $access_token;
    }

    /**
     * 设置消息标题
     * @param string $title
     * @return $this
     */
    public function setTitle($title="")
    {
        $this->title=$title;
        return $this;
    }

    /**
     * 设置消息内容
     * @param string $message
     * @return $this
     */
    public function setMessage($message="")
    {
        $this->message=$message;
        return $this;
    }

    /**
     * 添加要推送的用户token
     * @param string $deviceToken
     * @return $this
     */
    public function addDeviceToken($deviceToken="")
    {
        $this->deviceToken[]=$deviceToken;
        return $this;
    }

    /**
     * 设置AccessToken
     * @param string $AccessToken
     * @return $this
     */
    public function setAccessToken($AccessToken="")
    {
        $this->AccessToken=$AccessToken;
        return $this;
    }

    /**
     * 设置包名称 如果没有设置则会收不到通知
     * @param string $appPkgName
     * @return $this
     */
    public function setAppPkgName($appPkgName="com.cug.maintenance")
    {
        $this->appPkgName=$appPkgName;
        return $this;
    }

    /**
     * 开发者可以自定义Intent，用户收到通知栏消息后点击通知栏消息打开应用定义的这个Intent页面
     * @param string $intent
     * @return $this
     */
    public function setIntent($intent="intent://com.cug.maintenance/jump?data={\"type\":0}#Intent;scheme=cug;end")
    {
        $this->intent=$intent;
        return $this;
    }

    /**
     * 设置自定义参数 （点击app后可以应用可获取的参数）
     * @param $array array
     * @return $this
     */
    public function setCustomize($array=array())
    {
        $this->Customize=$array;
        return $this;
    }

    /**
     * 参数检查
     * @throws \Exception
     */
    private function check()
    {
       $_clientId= trim($this->_clientId);
       $_clientSecret= trim($this->_clientSecret);
       $title= trim($this->title);
       $message= trim($this->message);
       $AccessToken= trim($this->AccessToken);
       $appPkgName= trim($this->appPkgName);

        $deviceToken= $this->deviceToken;
        foreach ($deviceToken as $key=>$val) {
            $deviceToken[$key]=trim($val);
        }
        array_filter($deviceToken);
        if(count($deviceToken)<=0){
            throw new \Exception("华为推送必须要设置deviceToken");
        }
        if(!$_clientId){
            throw new \Exception("华为推送必须要设置clientId");
        }
        if(!$_clientSecret){
            throw new \Exception("华为推送必须要设置clientSecret");
        }
        if(!$title){
            throw new \Exception("华为推送必须要设置title");
        }
        if(!$message){
            throw new \Exception("华为推送必须要设置message");
        }
        if(!$AccessToken){
            throw new \Exception("华为推送必须要设置AccessToken");
        }
        if(!$appPkgName){
            throw new \Exception("华为推送必须要设置appPkgName");
        }

        $this->_clientId=$_clientId;
        $this->_clientSecret=$_clientSecret;
        $this->title=$title;
        $this->message=$message;
        $this->AccessToken=$AccessToken;
        $this->appPkgName=$appPkgName;

    }

    private function isDictArray($array=array())
    {
        if(!is_array($array) && count($array)>0){
            return false;
        }
        if(substr(json_encode($array),0,1) == "{"){
            return true;
        }
        return false;
    }

    /**
     * 发送华为推送消息。
     * @param $deviceToken
     * @param $title
     * @param $message
     * @return Response
     * @throws
     */
    public function sendMessage()
    {
        $this->check();
        // 构建 Payload
        $message=$this->message;
        $title=$this->title;

        $array=[
            'hps' => [
                'msg' => [
                    'type' => 3,
                    'body' => [
                        'content' => $message,
                        'title' => $title
                    ],
                    'action' => [
                        'type' => 3,
                        'param' => [
                            'appPkgName' => $this->appPkgName
                        ]
                    ]
                ]
            ]
        ];

        if(is_array($this->Customize) && count($this->Customize)>0){
            if($this->isDictArray($this->Customize)){
                $array["hps"]["ext"]=[ "customize" =>[$this->Customize]  ];
            }
            else{
                $array["hps"]["ext"]=[ "customize" =>$this->Customize  ];
            }

        }
        if(is_string($this->Customize) && trim($this->Customize)!=""){
            $array["hps"]["ext"]=[
                "customize" =>[trim($this->Customize)]
            ];
        }
        if(is_string($this->intent) && trim($this->intent)!=""){
            $array["hps"]["msg"]['action']['param']['intent']=trim($this->intent);
            $array["hps"]["msg"]['action']['type']=1;
        }
        $payload = json_encode($array, JSON_UNESCAPED_UNICODE);



        $response = $this->_http->post('https://api.push.hicloud.com/pushsend.do', [
            'query' => [
                'nsp_ctx' => json_encode(['ver' => '1', 'appId' => $this->_clientId])
            ],
            'data' => [
                'access_token' => $this->AccessToken,
                'nsp_ts' => time(),
                'nsp_svc' => 'openpush.message.api.send',
                'device_token_list' => json_encode($this->deviceToken),
                'payload' => $payload
            ]
        ]);
        $this->response=$response;

        return $response;
    }

    /**
     * 是否是推送成功
     * @return bool
     */
    public function isSendSuccess()
    {
        $sendStatus=false;
        if ($this->response)
        {
            $array=$this->response->getResponseArray();
            if(isset($array['msg']) && isset($array['requestId']) && $array['msg']=="Success"  ){
                $sendStatus=true; 
            }
        }
       return $sendStatus;
    }

    /**
     * 是否是推送失败
     * @return bool
     */
    public function isSendFail()
    {
       return !$this->isSendSuccess();
    }

    /**
     * 获取发送成功后的请求id
     * @return bool
     */
    public function getSendSuccessRequestId()
    {
        $requestId=false;
        if($this->isSendSuccess()){
            $array=$this->response->getResponseArray();
            if( isset($array['requestId'])  ){
                $requestId= $array['requestId'];
            }
        }
        return $requestId;
    }

}