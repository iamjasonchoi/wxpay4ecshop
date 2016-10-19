<?php
require_once "WxPay.Exception.php";
//require_once "WxPay.Config.php";
require_once "WxPay.Data.php";

/**
 *
 * �ӿڷ����࣬��������΢��֧��API�б�ķ�װ�����з���Ϊstatic������
 * ÿ���ӿ���Ĭ�ϳ�ʱʱ�䣨���ύ��ɨ֧��Ϊ10s���ϱ���ʱʱ��Ϊ1s�⣬������Ϊ6s��
 * @author widyhu
 *
 */
class WxPayApi
{
	/**
	 *
	 * ͳһ�µ���WxPayUnifiedOrder��out_trade_no��body��total_fee��trade_type����
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayUnifiedOrder $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return �ɹ�ʱ���أ��������쳣
	 */
	public static function unifiedOrder($inputObj, $timeOut = 30)
	{
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		//���������
		if(!$inputObj->IsOut_trade_noSet()) {
			throw new WxPayException("ȱ��ͳһ֧���ӿڱ������out_trade_no��");
		}else if(!$inputObj->IsBodySet()){
			throw new WxPayException("ȱ��ͳһ֧���ӿڱ������body��");
		}else if(!$inputObj->IsTotal_feeSet()) {
			throw new WxPayException("ȱ��ͳһ֧���ӿڱ������total_fee��");
		}else if(!$inputObj->IsTrade_typeSet()) {
			throw new WxPayException("ȱ��ͳһ֧���ӿڱ������trade_type��");
		}

		//��������
		if($inputObj->GetTrade_type() == "JSAPI" && !$inputObj->IsOpenidSet()){
			throw new WxPayException("ͳһ֧���ӿ��У�ȱ�ٱ������openid��trade_typeΪJSAPIʱ��openidΪ���������");
		}
		if($inputObj->GetTrade_type() == "NATIVE" && !$inputObj->IsProduct_idSet()){
			throw new WxPayException("ͳһ֧���ӿ��У�ȱ�ٱ������product_id��trade_typeΪJSAPIʱ��product_idΪ���������");
		}

		//�첽֪ͨurlδ���ã���ʹ�������ļ��е�url
		if(!$inputObj->IsNotify_urlSet()){
			$inputObj->SetNotify_url(WxPayConfig::$NOTIFY_URL);//�첽֪ͨurl
		}

		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//�ն�ip
		//$inputObj->SetSpbill_create_ip("1.1.1.1");
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		//ǩ��
		$inputObj->SetSign();
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//����ʼʱ��
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//�ϱ����󻨷�ʱ��

		return $result;
	}

	/**
	 *
	 * ��ѯ������WxPayOrderQuery��out_trade_no��transaction_id������һ��
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayOrderQuery $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return �ɹ�ʱ���أ��������쳣
	 */
	public static function orderQuery($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		//���������
		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WxPayException("������ѯ�ӿ��У�out_trade_no��transaction_id������һ����");
		}
		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		$inputObj->SetSign();//ǩ��
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//����ʼʱ��
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//�ϱ����󻨷�ʱ��

		return $result;
	}

	/**
	 *
	 * �رն�����WxPayCloseOrder��out_trade_no����
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayCloseOrder $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return �ɹ�ʱ���أ��������쳣
	 */
	public static function closeOrder($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/closeorder";
		//���������
		if(!$inputObj->IsOut_trade_noSet()) {
			throw new WxPayException("������ѯ�ӿ��У�out_trade_no���");
		}
		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		$inputObj->SetSign();//ǩ��
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//����ʼʱ��
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//�ϱ����󻨷�ʱ��

		return $result;
	}

	/**
	 *
	 * �����˿WxPayRefund��out_trade_no��transaction_id������һ����
	 * out_refund_no��total_fee��refund_fee��op_user_idΪ�������
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayRefund $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return �ɹ�ʱ���أ��������쳣
	 */
	public static function refund($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
		//���������
		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WxPayException("�˿�����ӿ��У�out_trade_no��transaction_id������һ����");
		}else if(!$inputObj->IsOut_refund_noSet()){
			throw new WxPayException("�˿�����ӿ��У�ȱ�ٱ������out_refund_no��");
		}else if(!$inputObj->IsTotal_feeSet()){
			throw new WxPayException("�˿�����ӿ��У�ȱ�ٱ������total_fee��");
		}else if(!$inputObj->IsRefund_feeSet()){
			throw new WxPayException("�˿�����ӿ��У�ȱ�ٱ������refund_fee��");
		}else if(!$inputObj->IsOp_user_idSet()){
			throw new WxPayException("�˿�����ӿ��У�ȱ�ٱ������op_user_id��");
		}
		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		$inputObj->SetSign();//ǩ��
		$xml = $inputObj->ToXml();
		$startTimeStamp = self::getMillisecond();//����ʼʱ��
		$response = self::postXmlCurl($xml, $url, true, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//�ϱ����󻨷�ʱ��

		return $result;
	}

	/**
	 *
	 * ��ѯ�˿�
	 * �ύ�˿������ͨ�����øýӿڲ�ѯ�˿�״̬���˿���һ����ʱ��
	 * ����Ǯ֧�����˿�20�����ڵ��ˣ����п�֧�����˿�3�������պ����²�ѯ�˿�״̬��
	 * WxPayRefundQuery��out_refund_no��out_trade_no��transaction_id��refund_id�ĸ���������һ��
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayRefundQuery $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return �ɹ�ʱ���أ��������쳣
	 */
	public static function refundQuery($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/refundquery";
		//���������
		if(!$inputObj->IsOut_refund_noSet() &&
			!$inputObj->IsOut_trade_noSet() &&
			!$inputObj->IsTransaction_idSet() &&
			!$inputObj->IsRefund_idSet()) {
			throw new WxPayException("�˿��ѯ�ӿ��У�out_refund_no��out_trade_no��transaction_id��refund_id�ĸ���������һ����");
		}
		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		$inputObj->SetSign();//ǩ��
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//����ʼʱ��
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//�ϱ����󻨷�ʱ��

		return $result;
	}

	/**
	 * ���ض��˵���WxPayDownloadBill��bill_dateΪ�������
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayDownloadBill $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return �ɹ�ʱ���أ��������쳣
	 */
	public static function downloadBill($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/downloadbill";
		//���������
		if(!$inputObj->IsBill_dateSet()) {
			throw new WxPayException("���˵��ӿ��У�ȱ�ٱ������bill_date��");
		}
		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		$inputObj->SetSign();//ǩ��
		$xml = $inputObj->ToXml();

		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		if(substr($response, 0 , 5) == "<xml>"){
			return "";
		}
		return $response;
	}

	/**
	 * �ύ��ɨ֧��API
	 * ����Աʹ��ɨ���豸��ȡ΢���û�ˢ����Ȩ���Ժ󣬶�ά���������Ϣ�������̻�����̨��
	 * ���̻�����̨�����̻���̨���øýӿڷ���֧����
	 * WxPayWxPayMicroPay��body��out_trade_no��total_fee��auth_code��������
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayWxPayMicroPay $inputObj
	 * @param int $timeOut
	 */
	public static function micropay($inputObj, $timeOut = 10)
	{
		$url = "https://api.mch.weixin.qq.com/pay/micropay";
		//���������
		if(!$inputObj->IsBodySet()) {
			throw new WxPayException("�ύ��ɨ֧��API�ӿ��У�ȱ�ٱ������body��");
		} else if(!$inputObj->IsOut_trade_noSet()) {
			throw new WxPayException("�ύ��ɨ֧��API�ӿ��У�ȱ�ٱ������out_trade_no��");
		} else if(!$inputObj->IsTotal_feeSet()) {
			throw new WxPayException("�ύ��ɨ֧��API�ӿ��У�ȱ�ٱ������total_fee��");
		} else if(!$inputObj->IsAuth_codeSet()) {
			throw new WxPayException("�ύ��ɨ֧��API�ӿ��У�ȱ�ٱ������auth_code��");
		}

		$inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//�ն�ip
		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		$inputObj->SetSign();//ǩ��
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//����ʼʱ��
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//�ϱ����󻨷�ʱ��

		return $result;
	}

	/**
	 *
	 * ��������API�ӿڣ�WxPayReverse�в���out_trade_no��transaction_id������дһ��
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayReverse $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 */
	public static function reverse($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/secapi/pay/reverse";
		//���������
		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WxPayException("��������API�ӿ��У�����out_trade_no��transaction_id������дһ����");
		}

		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		$inputObj->SetSign();//ǩ��
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//����ʼʱ��
		$response = self::postXmlCurl($xml, $url, true, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//�ϱ����󻨷�ʱ��

		return $result;
	}

	/**
	 *
	 * �����ϱ����÷����ڲ���װ��report�У�ʹ��ʱ��ע���쳣����
	 * WxPayReport��interface_url��return_code��result_code��user_ip��execute_time_����
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayReport $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return �ɹ�ʱ���أ��������쳣
	 */
	public static function report($inputObj, $timeOut = 1)
	{
		$url = "https://api.mch.weixin.qq.com/payitil/report";
		//���������
		if(!$inputObj->IsInterface_urlSet()) {
			throw new WxPayException("�ӿ�URL��ȱ�ٱ������interface_url��");
		} if(!$inputObj->IsReturn_codeSet()) {
			throw new WxPayException("����״̬�룬ȱ�ٱ������return_code��");
		} if(!$inputObj->IsResult_codeSet()) {
			throw new WxPayException("ҵ������ȱ�ٱ������result_code��");
		} if(!$inputObj->IsUser_ipSet()) {
			throw new WxPayException("���ʽӿ�IP��ȱ�ٱ������user_ip��");
		} if(!$inputObj->IsExecute_time_Set()) {
			throw new WxPayException("�ӿں�ʱ��ȱ�ٱ������execute_time_��");
		}
		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetUser_ip($_SERVER['REMOTE_ADDR']);//�ն�ip
		$inputObj->SetTime(date("YmdHis"));//�̻��ϱ�ʱ��
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		$inputObj->SetSign();//ǩ��
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//����ʼʱ��
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		return $response;
	}

	/**
	 *
	 * ���ɶ�ά�����,ģʽһ����֧����ά��
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayBizPayUrl $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return �ɹ�ʱ���أ��������쳣
	 */
	public static function bizpayurl($inputObj, $timeOut = 6)
	{
		if(!$inputObj->IsProduct_idSet()){
			throw new WxPayException("���ɶ�ά�룬ȱ�ٱ������product_id��");
		}

		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetTime_stamp(time());//ʱ���
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		$inputObj->SetSign();//ǩ��

		return $inputObj->GetValues();
	}

	/**
	 *
	 * ת��������
	 * �ýӿ���Ҫ����ɨ��ԭ��֧��ģʽһ�еĶ�ά������ת�ɶ�����(weixin://wxpay/s/XXXXXX)��
	 * ��С��ά��������������ɨ���ٶȺ;�ȷ�ȡ�
	 * appid��mchid��spbill_create_ip��nonce_str����Ҫ����
	 * @param WxPayShortUrl $inputObj
	 * @param int $timeOut
	 * @throws WxPayException
	 * @return �ɹ�ʱ���أ��������쳣
	 */
	public static function shorturl($inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/tools/shorturl";
		//���������
		if(!$inputObj->IsLong_urlSet()) {
			throw new WxPayException("��Ҫת����URL��ǩ����ԭ����������URL encode��");
		}
		$inputObj->SetAppid(WxPayConfig::$APPID);//�����˺�ID
		$inputObj->SetMch_id(WxPayConfig::$MCHID);//�̻���
		$inputObj->SetNonce_str(self::getNonceStr());//����ַ���

		$inputObj->SetSign();//ǩ��
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//����ʼʱ��
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = WxPayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//�ϱ����󻨷�ʱ��

		return $result;
	}

 	/**
 	 *
 	 * ֧�����ͨ��֪ͨ
 	 * @param function $callback
 	 * ֱ�ӻص�����ʹ�÷���: notify(you_function);
 	 * �ص����Ա��������:notify(array($this, you_function));
 	 * $callback  ԭ��Ϊ��function function_name($data){}
 	 */
	public static function notify($callback, &$msg)
	{
		//��ȡ֪ͨ������
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		//������سɹ�����֤ǩ��
		try {
			$result = WxPayResults::Init($xml);
		} catch (WxPayException $e){
			$msg = $e->errorMessage();
			return false;
		}

		return call_user_func($callback, $result);
	}

	/**
	 *
	 * ��������ַ�����������32λ
	 * @param int $length
	 * @return ����������ַ���
	 */
	public static function getNonceStr($length = 32)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
		}
		return $str;
	}

	/**
	 * ֱ�����xml
	 * @param string $xml
	 */
	public static function replyNotify($xml)
	{
		echo $xml;
	}

	/**
	 *
	 * �ϱ����ݣ� �ϱ���ʱ�����������쳣����
	 * @param string $usrl
	 * @param int $startTimeStamp
	 * @param array $data
	 */
	private static function reportCostTime($url, $startTimeStamp, $data)
	{
		//�������Ҫ�ϱ�����
		if(WxPayConfig::$REPORT_LEVENL == 0){
			return;
		}
		//�����ʧ���ϱ�
		if(WxPayConfig::$REPORT_LEVENL == 1 &&
			 array_key_exists("return_code", $data) &&
			 $data["return_code"] == "SUCCESS" &&
			 array_key_exists("result_code", $data) &&
			 $data["result_code"] == "SUCCESS")
		 {
		 	return;
		 }

		//�ϱ��߼�
		$endTimeStamp = self::getMillisecond();
		$objInput = new WxPayReport();
		$objInput->SetInterface_url($url);
		$objInput->SetExecute_time_($endTimeStamp - $startTimeStamp);
		//����״̬��
		if(array_key_exists("return_code", $data)){
			$objInput->SetReturn_code($data["return_code"]);
		}
		//������Ϣ
		if(array_key_exists("return_msg", $data)){
			$objInput->SetReturn_msg($data["return_msg"]);
		}
		//ҵ����
		if(array_key_exists("result_code", $data)){
			$objInput->SetResult_code($data["result_code"]);
		}
		//�������
		if(array_key_exists("err_code", $data)){
			$objInput->SetErr_code($data["err_code"]);
		}
		//�����������
		if(array_key_exists("err_code_des", $data)){
			$objInput->SetErr_code_des($data["err_code_des"]);
		}
		//�̻�������
		if(array_key_exists("out_trade_no", $data)){
			$objInput->SetOut_trade_no($data["out_trade_no"]);
		}
		//�豸��
		if(array_key_exists("device_info", $data)){
			$objInput->SetDevice_info($data["device_info"]);
		}

		try{
			self::report($objInput);
		} catch (WxPayException $e){
			//�����κδ���
		}
	}

	/**
	 * ��post��ʽ�ύxml����Ӧ�Ľӿ�url
	 *
	 * @param string $xml  ��Ҫpost��xml����
	 * @param string $url  url
	 * @param bool $useCert �Ƿ���Ҫ֤�飬Ĭ�ϲ���Ҫ
	 * @param int $second   urlִ�г�ʱʱ�䣬Ĭ��30s
	 * @throws WxPayException
	 */
	private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
	{
		$ch = curl_init();
		//���ó�ʱ
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		//��������ô�����������ô���
		if(WxPayConfig::$CURL_PROXY_HOST != "0.0.0.0"
			&& WxPayConfig::$CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::$CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::$CURL_PROXY_PORT);
		}
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);	// ����֤����
		//curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//�ϸ�У��
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // ��֤���м��SSL�����㷨�Ƿ����

		//����header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//Ҫ����Ϊ�ַ������������Ļ��
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		if($useCert == true){
			//����֤��
			//ʹ��֤�飺cert �� key �ֱ���������.pem�ļ�
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::$SSLCERT_PATH);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::$SSLKEY_PATH);
		}
		//post�ύ��ʽ
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//����curl
		$data = curl_exec($ch);

		//���ؽ��
		if($data){
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			curl_close($ch);
			throw new WxPayException("curl����������:$error");
		}
	}

	/**
	 * ��ȡ���뼶���ʱ���
	 */
	private static function getMillisecond()
	{
		//��ȡ�����ʱ���
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}
}

