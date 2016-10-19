<?php
/**
 *
 * JSAPI֧��ʵ����
 * ����ʵ���˴�΢�Ź���ƽ̨��ȡcode��ͨ��code��ȡopenid��access_token��
 * ����jsapi֧��js�ӿ�����Ĳ��������ɻ�ȡ�����ջ���ַ����Ĳ���
 *
 * ������΢��֧���ṩ�����������̻��ɸ����Լ��������޸ģ�����ʹ��lib�е�api���п���
 *
 * @author widy
 *
 */
class JsApiPay
{
	/**
	 *
	 * ��ҳ��Ȩ�ӿ�΢�ŷ��������ص����ݣ�������������
	 * {
	 *  "access_token":"ACCESS_TOKEN",
	 *  "expires_in":7200,
	 *  "refresh_token":"REFRESH_TOKEN",
	 *  "openid":"OPENID",
	 *  "scope":"SCOPE",
	 *  "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
	 * }
	 * ����access_token�����ڻ�ȡ�����ջ���ַ
	 * openid��΢��֧��jsapi֧���ӿڱ���Ĳ���
	 * @var array
	 */
	public $data = null;

	public $curl_timeout = 30;
	/**
	 *
	 * ͨ����ת��ȡ�û���openid����ת�������£�
	 * 1�������Լ���Ҫ���ص�url����������������ת��΢�ŷ�����https://open.weixin.qq.com/connect/oauth2/authorize
	 * 2��΢�ŷ��������֮�����ת���û�redirect_uri��ַ����ʱ�����һЩ�������磺code
	 *
	 * @return �û���openid
	 */
	public function GetOpenid()
	{
		//ͨ��code���openid
		if (!isset($_GET['code'])){
			//����΢�ŷ���code��
			$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
			$url = $this->__CreateOauthUrlForCode($baseUrl);
			Header("Location: $url");
			exit();
		} else {
			//��ȡcode�룬�Ի�ȡopenid
		    $code = $_GET['code'];
			$openid = $this->getOpenidFromMp($code);
			return $openid;
		}
	}

	/**
	 *
	 * ��ȡjsapi֧���Ĳ���
	 * @param array $UnifiedOrderResult ͳһ֧���ӿڷ��ص�����
	 * @throws WxPayException
	 *
	 * @return json���ݣ���ֱ������js������Ϊ����
	 */
	public function GetJsApiParameters($UnifiedOrderResult)
	{
		if(!array_key_exists("appid", $UnifiedOrderResult)
		|| !array_key_exists("prepay_id", $UnifiedOrderResult)
		|| $UnifiedOrderResult['prepay_id'] == "")
		{
			throw new WxPayException("��������");
		}
		$jsapi = new WxPayJsApiPay();
		$jsapi->SetAppid($UnifiedOrderResult["appid"]);
		$timeStamp = time();
		$jsapi->SetTimeStamp("$timeStamp");
		$jsapi->SetNonceStr(WxPayApi::getNonceStr());
		$jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
		$jsapi->SetSignType("MD5");
		$jsapi->SetPaySign($jsapi->MakeSign());
		$parameters = json_encode($jsapi->GetValues());
		return $parameters;
	}

	/**
	 *
	 * ͨ��code�ӹ���ƽ̨��ȡopenid����access_token
	 * @param string $code ΢����ת�������ϵ�code
	 *
	 * @return openid
	 */
	public function GetOpenidFromMp($code)
	{
		$url = $this->__CreateOauthUrlForOpenid($code);
		//��ʼ��curl
		$ch = curl_init();
		//���ó�ʱ
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if(WxPayConfig::$CURL_PROXY_HOST != "0.0.0.0"
			&& WxPayConfig::$CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::$CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::$CURL_PROXY_PORT);
		}
		//����curl�������jason��ʽ����
		$res = curl_exec($ch);
		curl_close($ch);
		//ȡ��openid
		$data = json_decode($res,true);
		$this->data = $data;
		$openid = $data['openid'];
		return $openid;
	}

	/**
	 *
	 * ƴ��ǩ���ַ���
	 * @param array $urlObj
	 *
	 * @return �����Ѿ�ƴ�Ӻõ��ַ���
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			if($k != "sign"){
				$buff .= $k . "=" . $v . "&";
			}
		}

		$buff = trim($buff, "&");
		return $buff;
	}

	/**
	 *
	 * ��ȡ��ַjs����
	 *
	 * @return ��ȡ�����ջ���ַjs������Ҫ�Ĳ�����json��ʽ����ֱ��������ʹ��
	 */
	public function GetEditAddressParameters()
	{
		$getData = $this->data;
		$data = array();
		$data["appid"] = WxPayConfig::$APPID;
		$data["url"] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$time = time();
		$data["timestamp"] = "$time";
		$data["noncestr"] = "1234568";
		$data["accesstoken"] = $getData["access_token"];
		ksort($data);
		$params = $this->ToUrlParams($data);
		$addrSign = sha1($params);

		$afterData = array(
			"addrSign" => $addrSign,
			"signType" => "sha1",
			"scope" => "jsapi_address",
			"appId" => WxPayConfig::$APPID,
			"timeStamp" => $data["timestamp"],
			"nonceStr" => $data["noncestr"]
		);
		$parameters = json_encode($afterData);
		return $parameters;
	}

	/**
	 *
	 * �����ȡcode��url����
	 * @param string $redirectUrl ΢�ŷ�����������url����Ҫurl����
	 *
	 * @return ���ع���õ�url
	 */
	private function __CreateOauthUrlForCode($redirectUrl)
	{
		$urlObj["appid"] = WxPayConfig::$APPID;
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}

	/**
	 *
	 * �����ȡopen��access_toke��url��ַ
	 * @param string $code��΢����ת���ص�code
	 *
	 * @return �����url
	 */
	private function __CreateOauthUrlForOpenid($code)
	{
		$urlObj["appid"] = WxPayConfig::$APPID;
		$urlObj["secret"] = WxPayConfig::$APPSECRET;
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}
}