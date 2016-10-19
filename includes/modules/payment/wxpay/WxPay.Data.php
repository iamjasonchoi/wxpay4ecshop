<?php
/**
* 2015-06-29 �޸�ǩ������
**/
//require_once "WxPay.Config.php";
require_once "WxPay.Exception.php";

/**
 *
 * ���ݶ�������࣬�����ж������������������Ϊ��������
 * ����/����/��ȡǩ�������xml��ʽ�Ĳ�������xml��ȡ���ݶ����
 * @author widyhu
 *
 */
class WxPayDataBase
{
	protected $values = array();

	/**
	* ����ǩ�������ǩ�������㷨
	* @param string $value
	**/
	public function SetSign()
	{
		$sign = $this->MakeSign();
		$this->values['sign'] = $sign;
		return $sign;
	}

	/**
	* ��ȡǩ�������ǩ�������㷨��ֵ
	* @return ֵ
	**/
	public function GetSign()
	{
		return $this->values['sign'];
	}

	/**
	* �ж�ǩ�������ǩ�������㷨�Ƿ����
	* @return true �� false
	**/
	public function IsSignSet()
	{
		return array_key_exists('sign', $this->values);
	}

	/**
	 * ���xml�ַ�
	 * @throws WxPayException
	**/
	public function ToXml()
	{
		if(!is_array($this->values)
			|| count($this->values) <= 0)
		{
    		throw new WxPayException("���������쳣��");
    	}

    	$xml = "<xml>";
    	foreach ($this->values as $key=>$val)
    	{
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    		}
        }
        $xml.="</xml>";
        return $xml;
	}

    /**
     * ��xmlתΪarray
     * @param string $xml
     * @throws WxPayException
     */
	public function FromXml($xml)
	{
		if(!$xml){
			throw new WxPayException("xml�����쳣��");
		}
        //��XMLתΪarray
        //��ֹ�����ⲿxmlʵ��
        libxml_disable_entity_loader(true);
        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $this->values;
	}

	/**
	 * ��ʽ��������ʽ����url����
	 */
	public function ToUrlParams()
	{
		$buff = "";
		foreach ($this->values as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}

		$buff = trim($buff, "&");
		return $buff;
	}

	/**
	 * ����ǩ��
	 * @return ǩ����������������sign��Ա��������Ҫ����ǩ����Ҫ����SetSign������ֵ
	 */
	public function MakeSign()
	{
		//ǩ������һ�����ֵ����������
		ksort($this->values);
		$string = $this->ToUrlParams();
		//ǩ�����������string�����KEY
		$string = $string . "&key=".WxPayConfig::$KEY;
		//ǩ����������MD5����
		$string = md5($string);
		//ǩ�������ģ������ַ�תΪ��д
		$result = strtoupper($string);
		return $result;
	}

	/**
	 * ��ȡ���õ�ֵ
	 */
	public function GetValues()
	{
		return $this->values;
	}
}

/**
 *
 * �ӿڵ��ý����
 * @author widyhu
 *
 */
class WxPayResults extends WxPayDataBase
{
	/**
	 *
	 * ���ǩ��
	 */
	public function CheckSign()
	{
		//fix�쳣
		if(!$this->IsSignSet()){
			throw new WxPayException("ǩ������");
		}

		$sign = $this->MakeSign();
		if($this->GetSign() == $sign){
			return true;
		}
		throw new WxPayException("ǩ������");
	}

	/**
	 *
	 * ʹ�������ʼ��
	 * @param array $array
	 */
	public function FromArray($array)
	{
		$this->values = $array;
	}

	/**
	 *
	 * ʹ�������ʼ������
	 * @param array $array
	 * @param �Ƿ���ǩ�� $noCheckSign
	 */
	public static function InitFromArray($array, $noCheckSign = false)
	{
		$obj = new self();
		$obj->FromArray($array);
		if($noCheckSign == false){
			$obj->CheckSign();
		}
        return $obj;
	}

	/**
	 *
	 * ���ò���
	 * @param string $key
	 * @param string $value
	 */
	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}

    /**
     * ��xmlתΪarray
     * @param string $xml
     * @throws WxPayException
     */
	public static function Init($xml)
	{
		$obj = new self();
		$obj->FromXml($xml);
		//fix bug 2015-06-29
		if($obj->values['return_code'] != 'SUCCESS'){
			 return $obj->GetValues();
		}
		$obj->CheckSign();
        return $obj->GetValues();
	}
}

/**
 *
 * �ص�������
 * @author widyhu
 *
 */
class WxPayNotifyReply extends  WxPayDataBase
{
	/**
	 *
	 * ���ô����� FAIL ���� SUCCESS
	 * @param string
	 */
	public function SetReturn_code($return_code)
	{
		$this->values['return_code'] = $return_code;
	}

	/**
	 *
	 * ��ȡ������ FAIL ���� SUCCESS
	 * @return string $return_code
	 */
	public function GetReturn_code()
	{
		return $this->values['return_code'];
	}

	/**
	 *
	 * ���ô�����Ϣ
	 * @param string $return_code
	 */
	public function SetReturn_msg($return_msg)
	{
		$this->values['return_msg'] = $return_msg;
	}

	/**
	 *
	 * ��ȡ������Ϣ
	 * @return string
	 */
	public function GetReturn_msg()
	{
		return $this->values['return_msg'];
	}

	/**
	 *
	 * ���÷��ز���
	 * @param string $key
	 * @param string $value
	 */
	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}
}

/**
 *
 * ͳһ�µ��������
 * @author widyhu
 *
 */
class WxPayUnifiedOrder extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	/**
	* ����΢��֧��������ն��豸�ţ��̻��Զ���
	* @param string $value
	**/
	public function SetDevice_info($value)
	{
		$this->values['device_info'] = $value;
	}
	/**
	* ��ȡ΢��֧��������ն��豸�ţ��̻��Զ����ֵ
	* @return ֵ
	**/
	public function GetDevice_info()
	{
		return $this->values['device_info'];
	}
	/**
	* �ж�΢��֧��������ն��豸�ţ��̻��Զ����Ƿ����
	* @return true �� false
	**/
	public function IsDevice_infoSet()
	{
		return array_key_exists('device_info', $this->values);
	}


	/**
	* ��������ַ�����������32λ���Ƽ�����������㷨
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ����ַ�����������32λ���Ƽ�����������㷨��ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж�����ַ�����������32λ���Ƽ�����������㷨�Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}

	/**
	* ������Ʒ��֧������Ҫ����
	* @param string $value
	**/
	public function SetBody($value)
	{
		$this->values['body'] = $value;
	}
	/**
	* ��ȡ��Ʒ��֧������Ҫ������ֵ
	* @return ֵ
	**/
	public function GetBody()
	{
		return $this->values['body'];
	}
	/**
	* �ж���Ʒ��֧������Ҫ�����Ƿ����
	* @return true �� false
	**/
	public function IsBodySet()
	{
		return array_key_exists('body', $this->values);
	}


	/**
	* ������Ʒ������ϸ�б�
	* @param string $value
	**/
	public function SetDetail($value)
	{
		$this->values['detail'] = $value;
	}
	/**
	* ��ȡ��Ʒ������ϸ�б��ֵ
	* @return ֵ
	**/
	public function GetDetail()
	{
		return $this->values['detail'];
	}
	/**
	* �ж���Ʒ������ϸ�б��Ƿ����
	* @return true �� false
	**/
	public function IsDetailSet()
	{
		return array_key_exists('detail', $this->values);
	}


	/**
	* ���ø������ݣ��ڲ�ѯAPI��֧��֪ͨ��ԭ�����أ����ֶ���Ҫ�����̻�Я���������Զ�������
	* @param string $value
	**/
	public function SetAttach($value)
	{
		$this->values['attach'] = $value;
	}
	/**
	* ��ȡ�������ݣ��ڲ�ѯAPI��֧��֪ͨ��ԭ�����أ����ֶ���Ҫ�����̻�Я���������Զ������ݵ�ֵ
	* @return ֵ
	**/
	public function GetAttach()
	{
		return $this->values['attach'];
	}
	/**
	* �жϸ������ݣ��ڲ�ѯAPI��֧��֪ͨ��ԭ�����أ����ֶ���Ҫ�����̻�Я���������Զ��������Ƿ����
	* @return true �� false
	**/
	public function IsAttachSet()
	{
		return array_key_exists('attach', $this->values);
	}


	/**
	* �����̻�ϵͳ�ڲ��Ķ�����,32���ַ��ڡ��ɰ�����ĸ, ����˵�����̻�������
	* @param string $value
	**/
	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}
	/**
	* ��ȡ�̻�ϵͳ�ڲ��Ķ�����,32���ַ��ڡ��ɰ�����ĸ, ����˵�����̻������ŵ�ֵ
	* @return ֵ
	**/
	public function GetOut_trade_no()
	{
		return $this->values['out_trade_no'];
	}
	/**
	* �ж��̻�ϵͳ�ڲ��Ķ�����,32���ַ��ڡ��ɰ�����ĸ, ����˵�����̻��������Ƿ����
	* @return true �� false
	**/
	public function IsOut_trade_noSet()
	{
		return array_key_exists('out_trade_no', $this->values);
	}


	/**
	* ���÷���ISO 4217��׼����λ��ĸ���룬Ĭ������ң�CNY������ֵ�б������������
	* @param string $value
	**/
	public function SetFee_type($value)
	{
		$this->values['fee_type'] = $value;
	}
	/**
	* ��ȡ����ISO 4217��׼����λ��ĸ���룬Ĭ������ң�CNY������ֵ�б�����������͵�ֵ
	* @return ֵ
	**/
	public function GetFee_type()
	{
		return $this->values['fee_type'];
	}
	/**
	* �жϷ���ISO 4217��׼����λ��ĸ���룬Ĭ������ң�CNY������ֵ�б�������������Ƿ����
	* @return true �� false
	**/
	public function IsFee_typeSet()
	{
		return array_key_exists('fee_type', $this->values);
	}


	/**
	* ���ö����ܽ�ֻ��Ϊ���������֧�����
	* @param string $value
	**/
	public function SetTotal_fee($value)
	{
		$this->values['total_fee'] = $value;
	}
	/**
	* ��ȡ�����ܽ�ֻ��Ϊ���������֧������ֵ
	* @return ֵ
	**/
	public function GetTotal_fee()
	{
		return $this->values['total_fee'];
	}
	/**
	* �ж϶����ܽ�ֻ��Ϊ���������֧������Ƿ����
	* @return true �� false
	**/
	public function IsTotal_feeSet()
	{
		return array_key_exists('total_fee', $this->values);
	}


	/**
	* ����APP����ҳ֧���ύ�û���ip��Native֧�������΢��֧��API�Ļ���IP��
	* @param string $value
	**/
	public function SetSpbill_create_ip($value)
	{
		$this->values['spbill_create_ip'] = $value;
	}
	/**
	* ��ȡAPP����ҳ֧���ύ�û���ip��Native֧�������΢��֧��API�Ļ���IP����ֵ
	* @return ֵ
	**/
	public function GetSpbill_create_ip()
	{
		return $this->values['spbill_create_ip'];
	}
	/**
	* �ж�APP����ҳ֧���ύ�û���ip��Native֧�������΢��֧��API�Ļ���IP���Ƿ����
	* @return true �� false
	**/
	public function IsSpbill_create_ipSet()
	{
		return array_key_exists('spbill_create_ip', $this->values);
	}


	/**
	* ���ö�������ʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��25��9��10��10���ʾΪ20091225091010���������ʱ�����
	* @param string $value
	**/
	public function SetTime_start($value)
	{
		$this->values['time_start'] = $value;
	}
	/**
	* ��ȡ��������ʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��25��9��10��10���ʾΪ20091225091010���������ʱ������ֵ
	* @return ֵ
	**/
	public function GetTime_start()
	{
		return $this->values['time_start'];
	}
	/**
	* �ж϶�������ʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��25��9��10��10���ʾΪ20091225091010���������ʱ������Ƿ����
	* @return true �� false
	**/
	public function IsTime_startSet()
	{
		return array_key_exists('time_start', $this->values);
	}


	/**
	* ���ö���ʧЧʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��27��9��10��10���ʾΪ20091227091010���������ʱ�����
	* @param string $value
	**/
	public function SetTime_expire($value)
	{
		$this->values['time_expire'] = $value;
	}
	/**
	* ��ȡ����ʧЧʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��27��9��10��10���ʾΪ20091227091010���������ʱ������ֵ
	* @return ֵ
	**/
	public function GetTime_expire()
	{
		return $this->values['time_expire'];
	}
	/**
	* �ж϶���ʧЧʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��27��9��10��10���ʾΪ20091227091010���������ʱ������Ƿ����
	* @return true �� false
	**/
	public function IsTime_expireSet()
	{
		return array_key_exists('time_expire', $this->values);
	}


	/**
	* ������Ʒ��ǣ�����ȯ�������Żݹ��ܵĲ�����˵���������ȯ�������Ż�
	* @param string $value
	**/
	public function SetGoods_tag($value)
	{
		$this->values['goods_tag'] = $value;
	}
	/**
	* ��ȡ��Ʒ��ǣ�����ȯ�������Żݹ��ܵĲ�����˵���������ȯ�������Żݵ�ֵ
	* @return ֵ
	**/
	public function GetGoods_tag()
	{
		return $this->values['goods_tag'];
	}
	/**
	* �ж���Ʒ��ǣ�����ȯ�������Żݹ��ܵĲ�����˵���������ȯ�������Ż��Ƿ����
	* @return true �� false
	**/
	public function IsGoods_tagSet()
	{
		return array_key_exists('goods_tag', $this->values);
	}


	/**
	* ���ý���΢��֧���첽֪ͨ�ص���ַ
	* @param string $value
	**/
	public function SetNotify_url($value)
	{
		$this->values['notify_url'] = $value;
	}
	/**
	* ��ȡ����΢��֧���첽֪ͨ�ص���ַ��ֵ
	* @return ֵ
	**/
	public function GetNotify_url()
	{
		return $this->values['notify_url'];
	}
	/**
	* �жϽ���΢��֧���첽֪ͨ�ص���ַ�Ƿ����
	* @return true �� false
	**/
	public function IsNotify_urlSet()
	{
		return array_key_exists('notify_url', $this->values);
	}


	/**
	* ����ȡֵ���£�JSAPI��NATIVE��APP����ϸ˵���������涨
	* @param string $value
	**/
	public function SetTrade_type($value)
	{
		$this->values['trade_type'] = $value;
	}
	/**
	* ��ȡȡֵ���£�JSAPI��NATIVE��APP����ϸ˵���������涨��ֵ
	* @return ֵ
	**/
	public function GetTrade_type()
	{
		return $this->values['trade_type'];
	}
	/**
	* �ж�ȡֵ���£�JSAPI��NATIVE��APP����ϸ˵���������涨�Ƿ����
	* @return true �� false
	**/
	public function IsTrade_typeSet()
	{
		return array_key_exists('trade_type', $this->values);
	}


	/**
	* ����trade_type=NATIVE���˲����ش�����idΪ��ά���а�������ƷID���̻����ж��塣
	* @param string $value
	**/
	public function SetProduct_id($value)
	{
		$this->values['product_id'] = $value;
	}
	/**
	* ��ȡtrade_type=NATIVE���˲����ش�����idΪ��ά���а�������ƷID���̻����ж��塣��ֵ
	* @return ֵ
	**/
	public function GetProduct_id()
	{
		return $this->values['product_id'];
	}
	/**
	* �ж�trade_type=NATIVE���˲����ش�����idΪ��ά���а�������ƷID���̻����ж��塣�Ƿ����
	* @return true �� false
	**/
	public function IsProduct_idSet()
	{
		return array_key_exists('product_id', $this->values);
	}


	/**
	* ����trade_type=JSAPI���˲����ش����û����̻�appid�µ�Ψһ��ʶ���µ�ǰ��Ҫ���á���ҳ��Ȩ��ȡ�û���Ϣ���ӿڻ�ȡ���û���Openid��
	* @param string $value
	**/
	public function SetOpenid($value)
	{
		$this->values['openid'] = $value;
	}
	/**
	* ��ȡtrade_type=JSAPI���˲����ش����û����̻�appid�µ�Ψһ��ʶ���µ�ǰ��Ҫ���á���ҳ��Ȩ��ȡ�û���Ϣ���ӿڻ�ȡ���û���Openid�� ��ֵ
	* @return ֵ
	**/
	public function GetOpenid()
	{
		return $this->values['openid'];
	}
	/**
	* �ж�trade_type=JSAPI���˲����ش����û����̻�appid�µ�Ψһ��ʶ���µ�ǰ��Ҫ���á���ҳ��Ȩ��ȡ�û���Ϣ���ӿڻ�ȡ���û���Openid�� �Ƿ����
	* @return true �� false
	**/
	public function IsOpenidSet()
	{
		return array_key_exists('openid', $this->values);
	}
}

/**
 *
 * ������ѯ�������
 * @author widyhu
 *
 */
class WxPayOrderQuery extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	/**
	* ����΢�ŵĶ����ţ�����ʹ��
	* @param string $value
	**/
	public function SetTransaction_id($value)
	{
		$this->values['transaction_id'] = $value;
	}
	/**
	* ��ȡ΢�ŵĶ����ţ�����ʹ�õ�ֵ
	* @return ֵ
	**/
	public function GetTransaction_id()
	{
		return $this->values['transaction_id'];
	}
	/**
	* �ж�΢�ŵĶ����ţ�����ʹ���Ƿ����
	* @return true �� false
	**/
	public function IsTransaction_idSet()
	{
		return array_key_exists('transaction_id', $this->values);
	}


	/**
	* �����̻�ϵͳ�ڲ��Ķ����ţ���û�ṩtransaction_idʱ��Ҫ�������
	* @param string $value
	**/
	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}
	/**
	* ��ȡ�̻�ϵͳ�ڲ��Ķ����ţ���û�ṩtransaction_idʱ��Ҫ���������ֵ
	* @return ֵ
	**/
	public function GetOut_trade_no()
	{
		return $this->values['out_trade_no'];
	}
	/**
	* �ж��̻�ϵͳ�ڲ��Ķ����ţ���û�ṩtransaction_idʱ��Ҫ��������Ƿ����
	* @return true �� false
	**/
	public function IsOut_trade_noSet()
	{
		return array_key_exists('out_trade_no', $this->values);
	}


	/**
	* ��������ַ�����������32λ���Ƽ�����������㷨
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ����ַ�����������32λ���Ƽ�����������㷨��ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж�����ַ�����������32λ���Ƽ�����������㷨�Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}
}

/**
 *
 * �رն����������
 * @author widyhu
 *
 */
class WxPayCloseOrder extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	/**
	* �����̻�ϵͳ�ڲ��Ķ�����
	* @param string $value
	**/
	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}
	/**
	* ��ȡ�̻�ϵͳ�ڲ��Ķ����ŵ�ֵ
	* @return ֵ
	**/
	public function GetOut_trade_no()
	{
		return $this->values['out_trade_no'];
	}
	/**
	* �ж��̻�ϵͳ�ڲ��Ķ������Ƿ����
	* @return true �� false
	**/
	public function IsOut_trade_noSet()
	{
		return array_key_exists('out_trade_no', $this->values);
	}


	/**
	* �����̻�ϵͳ�ڲ��Ķ�����,32���ַ��ڡ��ɰ�����ĸ, ����˵�����̻�������
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ�̻�ϵͳ�ڲ��Ķ�����,32���ַ��ڡ��ɰ�����ĸ, ����˵�����̻������ŵ�ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж��̻�ϵͳ�ڲ��Ķ�����,32���ַ��ڡ��ɰ�����ĸ, ����˵�����̻��������Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}
}

/**
 *
 * �ύ�˿��������
 * @author widyhu
 *
 */
class WxPayRefund extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	/**
	* ����΢��֧��������ն��豸�ţ����µ�һ��
	* @param string $value
	**/
	public function SetDevice_info($value)
	{
		$this->values['device_info'] = $value;
	}
	/**
	* ��ȡ΢��֧��������ն��豸�ţ����µ�һ�µ�ֵ
	* @return ֵ
	**/
	public function GetDevice_info()
	{
		return $this->values['device_info'];
	}
	/**
	* �ж�΢��֧��������ն��豸�ţ����µ�һ���Ƿ����
	* @return true �� false
	**/
	public function IsDevice_infoSet()
	{
		return array_key_exists('device_info', $this->values);
	}


	/**
	* ��������ַ�����������32λ���Ƽ�����������㷨
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ����ַ�����������32λ���Ƽ�����������㷨��ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж�����ַ�����������32λ���Ƽ�����������㷨�Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}

	/**
	* ����΢�Ŷ�����
	* @param string $value
	**/
	public function SetTransaction_id($value)
	{
		$this->values['transaction_id'] = $value;
	}
	/**
	* ��ȡ΢�Ŷ����ŵ�ֵ
	* @return ֵ
	**/
	public function GetTransaction_id()
	{
		return $this->values['transaction_id'];
	}
	/**
	* �ж�΢�Ŷ������Ƿ����
	* @return true �� false
	**/
	public function IsTransaction_idSet()
	{
		return array_key_exists('transaction_id', $this->values);
	}


	/**
	* �����̻�ϵͳ�ڲ��Ķ�����,transaction_id��out_trade_no��ѡһ�����ͬʱ�������ȼ���transaction_id> out_trade_no
	* @param string $value
	**/
	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}
	/**
	* ��ȡ�̻�ϵͳ�ڲ��Ķ�����,transaction_id��out_trade_no��ѡһ�����ͬʱ�������ȼ���transaction_id> out_trade_no��ֵ
	* @return ֵ
	**/
	public function GetOut_trade_no()
	{
		return $this->values['out_trade_no'];
	}
	/**
	* �ж��̻�ϵͳ�ڲ��Ķ�����,transaction_id��out_trade_no��ѡһ�����ͬʱ�������ȼ���transaction_id> out_trade_no�Ƿ����
	* @return true �� false
	**/
	public function IsOut_trade_noSet()
	{
		return array_key_exists('out_trade_no', $this->values);
	}


	/**
	* �����̻�ϵͳ�ڲ����˿�ţ��̻�ϵͳ�ڲ�Ψһ��ͬһ�˿�Ŷ������ֻ��һ��
	* @param string $value
	**/
	public function SetOut_refund_no($value)
	{
		$this->values['out_refund_no'] = $value;
	}
	/**
	* ��ȡ�̻�ϵͳ�ڲ����˿�ţ��̻�ϵͳ�ڲ�Ψһ��ͬһ�˿�Ŷ������ֻ��һ�ʵ�ֵ
	* @return ֵ
	**/
	public function GetOut_refund_no()
	{
		return $this->values['out_refund_no'];
	}
	/**
	* �ж��̻�ϵͳ�ڲ����˿�ţ��̻�ϵͳ�ڲ�Ψһ��ͬһ�˿�Ŷ������ֻ��һ���Ƿ����
	* @return true �� false
	**/
	public function IsOut_refund_noSet()
	{
		return array_key_exists('out_refund_no', $this->values);
	}


	/**
	* ���ö����ܽ���λΪ�֣�ֻ��Ϊ���������֧�����
	* @param string $value
	**/
	public function SetTotal_fee($value)
	{
		$this->values['total_fee'] = $value;
	}
	/**
	* ��ȡ�����ܽ���λΪ�֣�ֻ��Ϊ���������֧������ֵ
	* @return ֵ
	**/
	public function GetTotal_fee()
	{
		return $this->values['total_fee'];
	}
	/**
	* �ж϶����ܽ���λΪ�֣�ֻ��Ϊ���������֧������Ƿ����
	* @return true �� false
	**/
	public function IsTotal_feeSet()
	{
		return array_key_exists('total_fee', $this->values);
	}


	/**
	* �����˿��ܽ������ܽ���λΪ�֣�ֻ��Ϊ���������֧�����
	* @param string $value
	**/
	public function SetRefund_fee($value)
	{
		$this->values['refund_fee'] = $value;
	}
	/**
	* ��ȡ�˿��ܽ������ܽ���λΪ�֣�ֻ��Ϊ���������֧������ֵ
	* @return ֵ
	**/
	public function GetRefund_fee()
	{
		return $this->values['refund_fee'];
	}
	/**
	* �ж��˿��ܽ������ܽ���λΪ�֣�ֻ��Ϊ���������֧������Ƿ����
	* @return true �� false
	**/
	public function IsRefund_feeSet()
	{
		return array_key_exists('refund_fee', $this->values);
	}


	/**
	* ���û������ͣ�����ISO 4217��׼����λ��ĸ���룬Ĭ������ң�CNY������ֵ�б������������
	* @param string $value
	**/
	public function SetRefund_fee_type($value)
	{
		$this->values['refund_fee_type'] = $value;
	}
	/**
	* ��ȡ�������ͣ�����ISO 4217��׼����λ��ĸ���룬Ĭ������ң�CNY������ֵ�б�����������͵�ֵ
	* @return ֵ
	**/
	public function GetRefund_fee_type()
	{
		return $this->values['refund_fee_type'];
	}
	/**
	* �жϻ������ͣ�����ISO 4217��׼����λ��ĸ���룬Ĭ������ң�CNY������ֵ�б�������������Ƿ����
	* @return true �� false
	**/
	public function IsRefund_fee_typeSet()
	{
		return array_key_exists('refund_fee_type', $this->values);
	}


	/**
	* ���ò���Ա�ʺ�, Ĭ��Ϊ�̻���
	* @param string $value
	**/
	public function SetOp_user_id($value)
	{
		$this->values['op_user_id'] = $value;
	}
	/**
	* ��ȡ����Ա�ʺ�, Ĭ��Ϊ�̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetOp_user_id()
	{
		return $this->values['op_user_id'];
	}
	/**
	* �жϲ���Ա�ʺ�, Ĭ��Ϊ�̻����Ƿ����
	* @return true �� false
	**/
	public function IsOp_user_idSet()
	{
		return array_key_exists('op_user_id', $this->values);
	}
}

/**
 *
 * �˿��ѯ�������
 * @author widyhu
 *
 */
class WxPayRefundQuery extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	/**
	* ����΢��֧��������ն��豸��
	* @param string $value
	**/
	public function SetDevice_info($value)
	{
		$this->values['device_info'] = $value;
	}
	/**
	* ��ȡ΢��֧��������ն��豸�ŵ�ֵ
	* @return ֵ
	**/
	public function GetDevice_info()
	{
		return $this->values['device_info'];
	}
	/**
	* �ж�΢��֧��������ն��豸���Ƿ����
	* @return true �� false
	**/
	public function IsDevice_infoSet()
	{
		return array_key_exists('device_info', $this->values);
	}


	/**
	* ��������ַ�����������32λ���Ƽ�����������㷨
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ����ַ�����������32λ���Ƽ�����������㷨��ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж�����ַ�����������32λ���Ƽ�����������㷨�Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}

	/**
	* ����΢�Ŷ�����
	* @param string $value
	**/
	public function SetTransaction_id($value)
	{
		$this->values['transaction_id'] = $value;
	}
	/**
	* ��ȡ΢�Ŷ����ŵ�ֵ
	* @return ֵ
	**/
	public function GetTransaction_id()
	{
		return $this->values['transaction_id'];
	}
	/**
	* �ж�΢�Ŷ������Ƿ����
	* @return true �� false
	**/
	public function IsTransaction_idSet()
	{
		return array_key_exists('transaction_id', $this->values);
	}


	/**
	* �����̻�ϵͳ�ڲ��Ķ�����
	* @param string $value
	**/
	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}
	/**
	* ��ȡ�̻�ϵͳ�ڲ��Ķ����ŵ�ֵ
	* @return ֵ
	**/
	public function GetOut_trade_no()
	{
		return $this->values['out_trade_no'];
	}
	/**
	* �ж��̻�ϵͳ�ڲ��Ķ������Ƿ����
	* @return true �� false
	**/
	public function IsOut_trade_noSet()
	{
		return array_key_exists('out_trade_no', $this->values);
	}


	/**
	* �����̻��˿��
	* @param string $value
	**/
	public function SetOut_refund_no($value)
	{
		$this->values['out_refund_no'] = $value;
	}
	/**
	* ��ȡ�̻��˿�ŵ�ֵ
	* @return ֵ
	**/
	public function GetOut_refund_no()
	{
		return $this->values['out_refund_no'];
	}
	/**
	* �ж��̻��˿���Ƿ����
	* @return true �� false
	**/
	public function IsOut_refund_noSet()
	{
		return array_key_exists('out_refund_no', $this->values);
	}


	/**
	* ����΢���˿��refund_id��out_refund_no��out_trade_no��transaction_id�ĸ���������һ�������ͬʱ�������ȼ�Ϊ��refund_id>out_refund_no>transaction_id>out_trade_no
	* @param string $value
	**/
	public function SetRefund_id($value)
	{
		$this->values['refund_id'] = $value;
	}
	/**
	* ��ȡ΢���˿��refund_id��out_refund_no��out_trade_no��transaction_id�ĸ���������һ�������ͬʱ�������ȼ�Ϊ��refund_id>out_refund_no>transaction_id>out_trade_no��ֵ
	* @return ֵ
	**/
	public function GetRefund_id()
	{
		return $this->values['refund_id'];
	}
	/**
	* �ж�΢���˿��refund_id��out_refund_no��out_trade_no��transaction_id�ĸ���������һ�������ͬʱ�������ȼ�Ϊ��refund_id>out_refund_no>transaction_id>out_trade_no�Ƿ����
	* @return true �� false
	**/
	public function IsRefund_idSet()
	{
		return array_key_exists('refund_id', $this->values);
	}
}

/**
 *
 * ���ض��˵��������
 * @author widyhu
 *
 */
class WxPayDownloadBill extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	/**
	* ����΢��֧��������ն��豸�ţ���д���ֶΣ�ֻ���ظ��豸�ŵĶ��˵�
	* @param string $value
	**/
	public function SetDevice_info($value)
	{
		$this->values['device_info'] = $value;
	}
	/**
	* ��ȡ΢��֧��������ն��豸�ţ���д���ֶΣ�ֻ���ظ��豸�ŵĶ��˵���ֵ
	* @return ֵ
	**/
	public function GetDevice_info()
	{
		return $this->values['device_info'];
	}
	/**
	* �ж�΢��֧��������ն��豸�ţ���д���ֶΣ�ֻ���ظ��豸�ŵĶ��˵��Ƿ����
	* @return true �� false
	**/
	public function IsDevice_infoSet()
	{
		return array_key_exists('device_info', $this->values);
	}


	/**
	* ��������ַ�����������32λ���Ƽ�����������㷨
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ����ַ�����������32λ���Ƽ�����������㷨��ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж�����ַ�����������32λ���Ƽ�����������㷨�Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}

	/**
	* �������ض��˵������ڣ���ʽ��20140603
	* @param string $value
	**/
	public function SetBill_date($value)
	{
		$this->values['bill_date'] = $value;
	}
	/**
	* ��ȡ���ض��˵������ڣ���ʽ��20140603��ֵ
	* @return ֵ
	**/
	public function GetBill_date()
	{
		return $this->values['bill_date'];
	}
	/**
	* �ж����ض��˵������ڣ���ʽ��20140603�Ƿ����
	* @return true �� false
	**/
	public function IsBill_dateSet()
	{
		return array_key_exists('bill_date', $this->values);
	}


	/**
	* ����ALL�����ص������ж�����Ϣ��Ĭ��ֵSUCCESS�����ص��ճɹ�֧���Ķ���REFUND�����ص����˿��REVOKED���ѳ����Ķ���
	* @param string $value
	**/
	public function SetBill_type($value)
	{
		$this->values['bill_type'] = $value;
	}
	/**
	* ��ȡALL�����ص������ж�����Ϣ��Ĭ��ֵSUCCESS�����ص��ճɹ�֧���Ķ���REFUND�����ص����˿��REVOKED���ѳ����Ķ�����ֵ
	* @return ֵ
	**/
	public function GetBill_type()
	{
		return $this->values['bill_type'];
	}
	/**
	* �ж�ALL�����ص������ж�����Ϣ��Ĭ��ֵSUCCESS�����ص��ճɹ�֧���Ķ���REFUND�����ص����˿��REVOKED���ѳ����Ķ����Ƿ����
	* @return true �� false
	**/
	public function IsBill_typeSet()
	{
		return array_key_exists('bill_type', $this->values);
	}
}

/**
 *
 * �����ϱ��������
 * @author widyhu
 *
 */
class WxPayReport extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	/**
	* ����΢��֧��������ն��豸�ţ��̻��Զ���
	* @param string $value
	**/
	public function SetDevice_info($value)
	{
		$this->values['device_info'] = $value;
	}
	/**
	* ��ȡ΢��֧��������ն��豸�ţ��̻��Զ����ֵ
	* @return ֵ
	**/
	public function GetDevice_info()
	{
		return $this->values['device_info'];
	}
	/**
	* �ж�΢��֧��������ն��豸�ţ��̻��Զ����Ƿ����
	* @return true �� false
	**/
	public function IsDevice_infoSet()
	{
		return array_key_exists('device_info', $this->values);
	}


	/**
	* ��������ַ�����������32λ���Ƽ�����������㷨
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ����ַ�����������32λ���Ƽ�����������㷨��ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж�����ַ�����������32λ���Ƽ�����������㷨�Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}


	/**
	* �����ϱ���Ӧ�Ľӿڵ�����URL�����ƣ�https://api.mch.weixin.qq.com/pay/unifiedorder���ڱ�ɨ֧����Ϊ���õĺ��̻���ͬ����һ��ҵ����Ϊ�������ʱ������������ֽ���ģʽ���붼���ŵ���һ�α�ɨ��Ϊ����һ�ε����������ϱ����ϱ�URLָ��Ϊ��https://api.mch.weixin.qq.com/pay/micropay/total�������ֽ���ģʽ����ɲο����ĵ��½ڣ���ɨ֧���̻�����ģʽ�����ӿڵ�����Ȼ���յ���һ�Σ��ϱ�һ�������С�
	* @param string $value
	**/
	public function SetInterface_url($value)
	{
		$this->values['interface_url'] = $value;
	}
	/**
	* ��ȡ�ϱ���Ӧ�Ľӿڵ�����URL�����ƣ�https://api.mch.weixin.qq.com/pay/unifiedorder���ڱ�ɨ֧����Ϊ���õĺ��̻���ͬ����һ��ҵ����Ϊ�������ʱ������������ֽ���ģʽ���붼���ŵ���һ�α�ɨ��Ϊ����һ�ε����������ϱ����ϱ�URLָ��Ϊ��https://api.mch.weixin.qq.com/pay/micropay/total�������ֽ���ģʽ����ɲο����ĵ��½ڣ���ɨ֧���̻�����ģʽ�����ӿڵ�����Ȼ���յ���һ�Σ��ϱ�һ�������С���ֵ
	* @return ֵ
	**/
	public function GetInterface_url()
	{
		return $this->values['interface_url'];
	}
	/**
	* �ж��ϱ���Ӧ�Ľӿڵ�����URL�����ƣ�https://api.mch.weixin.qq.com/pay/unifiedorder���ڱ�ɨ֧����Ϊ���õĺ��̻���ͬ����һ��ҵ����Ϊ�������ʱ������������ֽ���ģʽ���붼���ŵ���һ�α�ɨ��Ϊ����һ�ε����������ϱ����ϱ�URLָ��Ϊ��https://api.mch.weixin.qq.com/pay/micropay/total�������ֽ���ģʽ����ɲο����ĵ��½ڣ���ɨ֧���̻�����ģʽ�����ӿڵ�����Ȼ���յ���һ�Σ��ϱ�һ�������С��Ƿ����
	* @return true �� false
	**/
	public function IsInterface_urlSet()
	{
		return array_key_exists('interface_url', $this->values);
	}


	/**
	* ���ýӿں�ʱ�������λΪ����
	* @param string $value
	**/
	public function SetExecute_time_($value)
	{
		$this->values['execute_time_'] = $value;
	}
	/**
	* ��ȡ�ӿں�ʱ�������λΪ�����ֵ
	* @return ֵ
	**/
	public function GetExecute_time_()
	{
		return $this->values['execute_time_'];
	}
	/**
	* �жϽӿں�ʱ�������λΪ�����Ƿ����
	* @return true �� false
	**/
	public function IsExecute_time_Set()
	{
		return array_key_exists('execute_time_', $this->values);
	}


	/**
	* ����SUCCESS/FAIL���ֶ���ͨ�ű�ʶ���ǽ��ױ�ʶ�������Ƿ�ɹ���Ҫ�鿴trade_state���ж�
	* @param string $value
	**/
	public function SetReturn_code($value)
	{
		$this->values['return_code'] = $value;
	}
	/**
	* ��ȡSUCCESS/FAIL���ֶ���ͨ�ű�ʶ���ǽ��ױ�ʶ�������Ƿ�ɹ���Ҫ�鿴trade_state���жϵ�ֵ
	* @return ֵ
	**/
	public function GetReturn_code()
	{
		return $this->values['return_code'];
	}
	/**
	* �ж�SUCCESS/FAIL���ֶ���ͨ�ű�ʶ���ǽ��ױ�ʶ�������Ƿ�ɹ���Ҫ�鿴trade_state���ж��Ƿ����
	* @return true �� false
	**/
	public function IsReturn_codeSet()
	{
		return array_key_exists('return_code', $this->values);
	}


	/**
	* ���÷�����Ϣ����ǿգ�Ϊ����ԭ��ǩ��ʧ�ܲ�����ʽУ�����
	* @param string $value
	**/
	public function SetReturn_msg($value)
	{
		$this->values['return_msg'] = $value;
	}
	/**
	* ��ȡ������Ϣ����ǿգ�Ϊ����ԭ��ǩ��ʧ�ܲ�����ʽУ������ֵ
	* @return ֵ
	**/
	public function GetReturn_msg()
	{
		return $this->values['return_msg'];
	}
	/**
	* �жϷ�����Ϣ����ǿգ�Ϊ����ԭ��ǩ��ʧ�ܲ�����ʽУ������Ƿ����
	* @return true �� false
	**/
	public function IsReturn_msgSet()
	{
		return array_key_exists('return_msg', $this->values);
	}


	/**
	* ����SUCCESS/FAIL
	* @param string $value
	**/
	public function SetResult_code($value)
	{
		$this->values['result_code'] = $value;
	}
	/**
	* ��ȡSUCCESS/FAIL��ֵ
	* @return ֵ
	**/
	public function GetResult_code()
	{
		return $this->values['result_code'];
	}
	/**
	* �ж�SUCCESS/FAIL�Ƿ����
	* @return true �� false
	**/
	public function IsResult_codeSet()
	{
		return array_key_exists('result_code', $this->values);
	}


	/**
	* ����ORDERNOTEXIST������������SYSTEMERROR��ϵͳ����
	* @param string $value
	**/
	public function SetErr_code($value)
	{
		$this->values['err_code'] = $value;
	}
	/**
	* ��ȡORDERNOTEXIST������������SYSTEMERROR��ϵͳ�����ֵ
	* @return ֵ
	**/
	public function GetErr_code()
	{
		return $this->values['err_code'];
	}
	/**
	* �ж�ORDERNOTEXIST������������SYSTEMERROR��ϵͳ�����Ƿ����
	* @return true �� false
	**/
	public function IsErr_codeSet()
	{
		return array_key_exists('err_code', $this->values);
	}


	/**
	* ���ý����Ϣ����
	* @param string $value
	**/
	public function SetErr_code_des($value)
	{
		$this->values['err_code_des'] = $value;
	}
	/**
	* ��ȡ�����Ϣ������ֵ
	* @return ֵ
	**/
	public function GetErr_code_des()
	{
		return $this->values['err_code_des'];
	}
	/**
	* �жϽ����Ϣ�����Ƿ����
	* @return true �� false
	**/
	public function IsErr_code_desSet()
	{
		return array_key_exists('err_code_des', $this->values);
	}


	/**
	* �����̻�ϵͳ�ڲ��Ķ�����,�̻��������ϱ�ʱ�ṩ����̻������ŷ���΢��֧�����õ���߷���������
	* @param string $value
	**/
	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}
	/**
	* ��ȡ�̻�ϵͳ�ڲ��Ķ�����,�̻��������ϱ�ʱ�ṩ����̻������ŷ���΢��֧�����õ���߷��������� ��ֵ
	* @return ֵ
	**/
	public function GetOut_trade_no()
	{
		return $this->values['out_trade_no'];
	}
	/**
	* �ж��̻�ϵͳ�ڲ��Ķ�����,�̻��������ϱ�ʱ�ṩ����̻������ŷ���΢��֧�����õ���߷��������� �Ƿ����
	* @return true �� false
	**/
	public function IsOut_trade_noSet()
	{
		return array_key_exists('out_trade_no', $this->values);
	}


	/**
	* ���÷���ӿڵ���ʱ�Ļ���IP
	* @param string $value
	**/
	public function SetUser_ip($value)
	{
		$this->values['user_ip'] = $value;
	}
	/**
	* ��ȡ����ӿڵ���ʱ�Ļ���IP ��ֵ
	* @return ֵ
	**/
	public function GetUser_ip()
	{
		return $this->values['user_ip'];
	}
	/**
	* �жϷ���ӿڵ���ʱ�Ļ���IP �Ƿ����
	* @return true �� false
	**/
	public function IsUser_ipSet()
	{
		return array_key_exists('user_ip', $this->values);
	}


	/**
	* ����ϵͳʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��27��9��10��10���ʾΪ20091227091010���������ʱ�����
	* @param string $value
	**/
	public function SetTime($value)
	{
		$this->values['time'] = $value;
	}
	/**
	* ��ȡϵͳʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��27��9��10��10���ʾΪ20091227091010���������ʱ������ֵ
	* @return ֵ
	**/
	public function GetTime()
	{
		return $this->values['time'];
	}
	/**
	* �ж�ϵͳʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��27��9��10��10���ʾΪ20091227091010���������ʱ������Ƿ����
	* @return true �� false
	**/
	public function IsTimeSet()
	{
		return array_key_exists('time', $this->values);
	}
}

/**
 *
 * ����ת���������
 * @author widyhu
 *
 */
class WxPayShortUrl extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	/**
	* ������Ҫת����URL��ǩ����ԭ����������URL encode
	* @param string $value
	**/
	public function SetLong_url($value)
	{
		$this->values['long_url'] = $value;
	}
	/**
	* ��ȡ��Ҫת����URL��ǩ����ԭ����������URL encode��ֵ
	* @return ֵ
	**/
	public function GetLong_url()
	{
		return $this->values['long_url'];
	}
	/**
	* �ж���Ҫת����URL��ǩ����ԭ����������URL encode�Ƿ����
	* @return true �� false
	**/
	public function IsLong_urlSet()
	{
		return array_key_exists('long_url', $this->values);
	}


	/**
	* ��������ַ�����������32λ���Ƽ�����������㷨
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ����ַ�����������32λ���Ƽ�����������㷨��ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж�����ַ�����������32λ���Ƽ�����������㷨�Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}
}

/**
 *
 * �ύ��ɨ�������
 * @author widyhu
 *
 */
class WxPayMicroPay extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	/**
	* �����ն��豸��(�̻��Զ��壬���ŵ���)
	* @param string $value
	**/
	public function SetDevice_info($value)
	{
		$this->values['device_info'] = $value;
	}
	/**
	* ��ȡ�ն��豸��(�̻��Զ��壬���ŵ���)��ֵ
	* @return ֵ
	**/
	public function GetDevice_info()
	{
		return $this->values['device_info'];
	}
	/**
	* �ж��ն��豸��(�̻��Զ��壬���ŵ���)�Ƿ����
	* @return true �� false
	**/
	public function IsDevice_infoSet()
	{
		return array_key_exists('device_info', $this->values);
	}


	/**
	* ��������ַ�����������32λ���Ƽ�����������㷨
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ����ַ�����������32λ���Ƽ�����������㷨��ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж�����ַ�����������32λ���Ƽ�����������㷨�Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}

	/**
	* ������Ʒ��֧������Ҫ����
	* @param string $value
	**/
	public function SetBody($value)
	{
		$this->values['body'] = $value;
	}
	/**
	* ��ȡ��Ʒ��֧������Ҫ������ֵ
	* @return ֵ
	**/
	public function GetBody()
	{
		return $this->values['body'];
	}
	/**
	* �ж���Ʒ��֧������Ҫ�����Ƿ����
	* @return true �� false
	**/
	public function IsBodySet()
	{
		return array_key_exists('body', $this->values);
	}


	/**
	* ������Ʒ������ϸ�б�
	* @param string $value
	**/
	public function SetDetail($value)
	{
		$this->values['detail'] = $value;
	}
	/**
	* ��ȡ��Ʒ������ϸ�б��ֵ
	* @return ֵ
	**/
	public function GetDetail()
	{
		return $this->values['detail'];
	}
	/**
	* �ж���Ʒ������ϸ�б��Ƿ����
	* @return true �� false
	**/
	public function IsDetailSet()
	{
		return array_key_exists('detail', $this->values);
	}


	/**
	* ���ø������ݣ��ڲ�ѯAPI��֧��֪ͨ��ԭ�����أ����ֶ���Ҫ�����̻�Я���������Զ�������
	* @param string $value
	**/
	public function SetAttach($value)
	{
		$this->values['attach'] = $value;
	}
	/**
	* ��ȡ�������ݣ��ڲ�ѯAPI��֧��֪ͨ��ԭ�����أ����ֶ���Ҫ�����̻�Я���������Զ������ݵ�ֵ
	* @return ֵ
	**/
	public function GetAttach()
	{
		return $this->values['attach'];
	}
	/**
	* �жϸ������ݣ��ڲ�ѯAPI��֧��֪ͨ��ԭ�����أ����ֶ���Ҫ�����̻�Я���������Զ��������Ƿ����
	* @return true �� false
	**/
	public function IsAttachSet()
	{
		return array_key_exists('attach', $this->values);
	}


	/**
	* �����̻�ϵͳ�ڲ��Ķ�����,32���ַ��ڡ��ɰ�����ĸ, ����˵�����̻�������
	* @param string $value
	**/
	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}
	/**
	* ��ȡ�̻�ϵͳ�ڲ��Ķ�����,32���ַ��ڡ��ɰ�����ĸ, ����˵�����̻������ŵ�ֵ
	* @return ֵ
	**/
	public function GetOut_trade_no()
	{
		return $this->values['out_trade_no'];
	}
	/**
	* �ж��̻�ϵͳ�ڲ��Ķ�����,32���ַ��ڡ��ɰ�����ĸ, ����˵�����̻��������Ƿ����
	* @return true �� false
	**/
	public function IsOut_trade_noSet()
	{
		return array_key_exists('out_trade_no', $this->values);
	}


	/**
	* ���ö����ܽ���λΪ�֣�ֻ��Ϊ���������֧�����
	* @param string $value
	**/
	public function SetTotal_fee($value)
	{
		$this->values['total_fee'] = $value;
	}
	/**
	* ��ȡ�����ܽ���λΪ�֣�ֻ��Ϊ���������֧������ֵ
	* @return ֵ
	**/
	public function GetTotal_fee()
	{
		return $this->values['total_fee'];
	}
	/**
	* �ж϶����ܽ���λΪ�֣�ֻ��Ϊ���������֧������Ƿ����
	* @return true �� false
	**/
	public function IsTotal_feeSet()
	{
		return array_key_exists('total_fee', $this->values);
	}


	/**
	* ���÷���ISO 4217��׼����λ��ĸ���룬Ĭ������ң�CNY������ֵ�б������������
	* @param string $value
	**/
	public function SetFee_type($value)
	{
		$this->values['fee_type'] = $value;
	}
	/**
	* ��ȡ����ISO 4217��׼����λ��ĸ���룬Ĭ������ң�CNY������ֵ�б�����������͵�ֵ
	* @return ֵ
	**/
	public function GetFee_type()
	{
		return $this->values['fee_type'];
	}
	/**
	* �жϷ���ISO 4217��׼����λ��ĸ���룬Ĭ������ң�CNY������ֵ�б�������������Ƿ����
	* @return true �� false
	**/
	public function IsFee_typeSet()
	{
		return array_key_exists('fee_type', $this->values);
	}


	/**
	* ���õ���΢��֧��API�Ļ���IP
	* @param string $value
	**/
	public function SetSpbill_create_ip($value)
	{
		$this->values['spbill_create_ip'] = $value;
	}
	/**
	* ��ȡ����΢��֧��API�Ļ���IP ��ֵ
	* @return ֵ
	**/
	public function GetSpbill_create_ip()
	{
		return $this->values['spbill_create_ip'];
	}
	/**
	* �жϵ���΢��֧��API�Ļ���IP �Ƿ����
	* @return true �� false
	**/
	public function IsSpbill_create_ipSet()
	{
		return array_key_exists('spbill_create_ip', $this->values);
	}


	/**
	* ���ö�������ʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��25��9��10��10���ʾΪ20091225091010�����ʱ�����
	* @param string $value
	**/
	public function SetTime_start($value)
	{
		$this->values['time_start'] = $value;
	}
	/**
	* ��ȡ��������ʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��25��9��10��10���ʾΪ20091225091010�����ʱ������ֵ
	* @return ֵ
	**/
	public function GetTime_start()
	{
		return $this->values['time_start'];
	}
	/**
	* �ж϶�������ʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��25��9��10��10���ʾΪ20091225091010�����ʱ������Ƿ����
	* @return true �� false
	**/
	public function IsTime_startSet()
	{
		return array_key_exists('time_start', $this->values);
	}


	/**
	* ���ö���ʧЧʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��27��9��10��10���ʾΪ20091227091010�����ʱ�����
	* @param string $value
	**/
	public function SetTime_expire($value)
	{
		$this->values['time_expire'] = $value;
	}
	/**
	* ��ȡ����ʧЧʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��27��9��10��10���ʾΪ20091227091010�����ʱ������ֵ
	* @return ֵ
	**/
	public function GetTime_expire()
	{
		return $this->values['time_expire'];
	}
	/**
	* �ж϶���ʧЧʱ�䣬��ʽΪyyyyMMddHHmmss����2009��12��27��9��10��10���ʾΪ20091227091010�����ʱ������Ƿ����
	* @return true �� false
	**/
	public function IsTime_expireSet()
	{
		return array_key_exists('time_expire', $this->values);
	}


	/**
	* ������Ʒ��ǣ�����ȯ�������Żݹ��ܵĲ�����˵���������ȯ�������Ż�
	* @param string $value
	**/
	public function SetGoods_tag($value)
	{
		$this->values['goods_tag'] = $value;
	}
	/**
	* ��ȡ��Ʒ��ǣ�����ȯ�������Żݹ��ܵĲ�����˵���������ȯ�������Żݵ�ֵ
	* @return ֵ
	**/
	public function GetGoods_tag()
	{
		return $this->values['goods_tag'];
	}
	/**
	* �ж���Ʒ��ǣ�����ȯ�������Żݹ��ܵĲ�����˵���������ȯ�������Ż��Ƿ����
	* @return true �� false
	**/
	public function IsGoods_tagSet()
	{
		return array_key_exists('goods_tag', $this->values);
	}


	/**
	* ����ɨ��֧����Ȩ�룬�豸��ȡ�û�΢���е�������߶�ά����Ϣ
	* @param string $value
	**/
	public function SetAuth_code($value)
	{
		$this->values['auth_code'] = $value;
	}
	/**
	* ��ȡɨ��֧����Ȩ�룬�豸��ȡ�û�΢���е�������߶�ά����Ϣ��ֵ
	* @return ֵ
	**/
	public function GetAuth_code()
	{
		return $this->values['auth_code'];
	}
	/**
	* �ж�ɨ��֧����Ȩ�룬�豸��ȡ�û�΢���е�������߶�ά����Ϣ�Ƿ����
	* @return true �� false
	**/
	public function IsAuth_codeSet()
	{
		return array_key_exists('auth_code', $this->values);
	}
}

/**
 *
 * �����������
 * @author widyhu
 *
 */
class WxPayReverse extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	/**
	* ����΢�ŵĶ����ţ�����ʹ��
	* @param string $value
	**/
	public function SetTransaction_id($value)
	{
		$this->values['transaction_id'] = $value;
	}
	/**
	* ��ȡ΢�ŵĶ����ţ�����ʹ�õ�ֵ
	* @return ֵ
	**/
	public function GetTransaction_id()
	{
		return $this->values['transaction_id'];
	}
	/**
	* �ж�΢�ŵĶ����ţ�����ʹ���Ƿ����
	* @return true �� false
	**/
	public function IsTransaction_idSet()
	{
		return array_key_exists('transaction_id', $this->values);
	}


	/**
	* �����̻�ϵͳ�ڲ��Ķ�����,transaction_id��out_trade_no��ѡһ�����ͬʱ�������ȼ���transaction_id> out_trade_no
	* @param string $value
	**/
	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}
	/**
	* ��ȡ�̻�ϵͳ�ڲ��Ķ�����,transaction_id��out_trade_no��ѡһ�����ͬʱ�������ȼ���transaction_id> out_trade_no��ֵ
	* @return ֵ
	**/
	public function GetOut_trade_no()
	{
		return $this->values['out_trade_no'];
	}
	/**
	* �ж��̻�ϵͳ�ڲ��Ķ�����,transaction_id��out_trade_no��ѡһ�����ͬʱ�������ȼ���transaction_id> out_trade_no�Ƿ����
	* @return true �� false
	**/
	public function IsOut_trade_noSet()
	{
		return array_key_exists('out_trade_no', $this->values);
	}


	/**
	* ��������ַ�����������32λ���Ƽ�����������㷨
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ����ַ�����������32λ���Ƽ�����������㷨��ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж�����ַ�����������32λ���Ƽ�����������㷨�Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}
}

/**
 *
 * �ύJSAPI�������
 * @author widyhu
 *
 */
class WxPayJsApiPay extends WxPayDataBase
{
	/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appId'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appId'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appId', $this->values);
	}


	/**
	* ����֧��ʱ���
	* @param string $value
	**/
	public function SetTimeStamp($value)
	{
		$this->values['timeStamp'] = $value;
	}
	/**
	* ��ȡ֧��ʱ�����ֵ
	* @return ֵ
	**/
	public function GetTimeStamp()
	{
		return $this->values['timeStamp'];
	}
	/**
	* �ж�֧��ʱ����Ƿ����
	* @return true �� false
	**/
	public function IsTimeStampSet()
	{
		return array_key_exists('timeStamp', $this->values);
	}

	/**
	* ����ַ���
	* @param string $value
	**/
	public function SetNonceStr($value)
	{
		$this->values['nonceStr'] = $value;
	}
	/**
	* ��ȡnotify����ַ���ֵ
	* @return ֵ
	**/
	public function GetReturn_code()
	{
		return $this->values['nonceStr'];
	}
	/**
	* �ж�����ַ����Ƿ����
	* @return true �� false
	**/
	public function IsReturn_codeSet()
	{
		return array_key_exists('nonceStr', $this->values);
	}


	/**
	* ���ö���������չ�ַ���
	* @param string $value
	**/
	public function SetPackage($value)
	{
		$this->values['package'] = $value;
	}
	/**
	* ��ȡ����������չ�ַ�����ֵ
	* @return ֵ
	**/
	public function GetPackage()
	{
		return $this->values['package'];
	}
	/**
	* �ж϶���������չ�ַ����Ƿ����
	* @return true �� false
	**/
	public function IsPackageSet()
	{
		return array_key_exists('package', $this->values);
	}

	/**
	* ����ǩ����ʽ
	* @param string $value
	**/
	public function SetSignType($value)
	{
		$this->values['signType'] = $value;
	}
	/**
	* ��ȡǩ����ʽ
	* @return ֵ
	**/
	public function GetSignType()
	{
		return $this->values['signType'];
	}
	/**
	* �ж�ǩ����ʽ�Ƿ����
	* @return true �� false
	**/
	public function IsSignTypeSet()
	{
		return array_key_exists('signType', $this->values);
	}

	/**
	* ����ǩ����ʽ
	* @param string $value
	**/
	public function SetPaySign($value)
	{
		$this->values['paySign'] = $value;
	}
	/**
	* ��ȡǩ����ʽ
	* @return ֵ
	**/
	public function GetPaySign()
	{
		return $this->values['paySign'];
	}
	/**
	* �ж�ǩ����ʽ�Ƿ����
	* @return true �� false
	**/
	public function IsPaySignSet()
	{
		return array_key_exists('paySign', $this->values);
	}
}

/**
 *
 * ɨ��֧��ģʽһ���ɶ�ά�����
 * @author widyhu
 *
 */
class WxPayBizPayUrl extends WxPayDataBase
{
		/**
	* ����΢�ŷ���Ĺ����˺�ID
	* @param string $value
	**/
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}
	/**
	* ��ȡ΢�ŷ���Ĺ����˺�ID��ֵ
	* @return ֵ
	**/
	public function GetAppid()
	{
		return $this->values['appid'];
	}
	/**
	* �ж�΢�ŷ���Ĺ����˺�ID�Ƿ����
	* @return true �� false
	**/
	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}


	/**
	* ����΢��֧��������̻���
	* @param string $value
	**/
	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}
	/**
	* ��ȡ΢��֧��������̻��ŵ�ֵ
	* @return ֵ
	**/
	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}
	/**
	* �ж�΢��֧��������̻����Ƿ����
	* @return true �� false
	**/
	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}

	/**
	* ����֧��ʱ���
	* @param string $value
	**/
	public function SetTime_stamp($value)
	{
		$this->values['time_stamp'] = $value;
	}
	/**
	* ��ȡ֧��ʱ�����ֵ
	* @return ֵ
	**/
	public function GetTime_stamp()
	{
		return $this->values['time_stamp'];
	}
	/**
	* �ж�֧��ʱ����Ƿ����
	* @return true �� false
	**/
	public function IsTime_stampSet()
	{
		return array_key_exists('time_stamp', $this->values);
	}

	/**
	* ��������ַ���
	* @param string $value
	**/
	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}
	/**
	* ��ȡ����ַ�����ֵ
	* @return ֵ
	**/
	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}
	/**
	* �ж�����ַ����Ƿ����
	* @return true �� false
	**/
	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}

	/**
	* ������ƷID
	* @param string $value
	**/
	public function SetProduct_id($value)
	{
		$this->values['product_id'] = $value;
	}
	/**
	* ��ȡ��ƷID��ֵ
	* @return ֵ
	**/
	public function GetProduct_id()
	{
		return $this->values['product_id'];
	}
	/**
	* �ж���ƷID�Ƿ����
	* @return true �� false
	**/
	public function IsProduct_idSet()
	{
		return array_key_exists('product_id', $this->values);
	}
}
