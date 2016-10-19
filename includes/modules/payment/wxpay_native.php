<?php
/**
 * ECSHOP ΢��ɨ��֧��

 * ----------------------------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã�������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Author: douqinghua $
 * $Id: unionpay.php 17063  $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}


// ���������ļ�
$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/wxpay_native.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

/* ģ��Ļ�����Ϣ */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* ���� */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* ������Ӧ�������� */
    $modules[$i]['desc']    = 'wxpay_native_desc';

    /* �Ƿ�֧�ֻ������� */
    $modules[$i]['is_cod']  = '0';

    /* �Ƿ�֧������֧�� */
    $modules[$i]['is_online']  = '1';

    /* ���� */
    $modules[$i]['author']  = 'Jason.C';

    /* ��ַ */
    $modules[$i]['website'] = 'Jason.C';

    /* �汾�� */
    $modules[$i]['version'] = '1.0.0';

    /* ������Ϣ */
       $modules[$i]['config'] = array(
        // ΢�Ź��ں���ݵ�Ψһ��ʶ
        array(
            'name' => 'wxpay_native_appid',
            'type' => 'text',
            'value' => ''
        ),
        // JSAPI�ӿ��л�ȡopenid����˺��ڹ���ƽ̨��������ģʽ��ɲ鿴
        array(
            'name' => 'wxpay_native_appsecret',
            'type' => 'text',
            'value' => ''
        ),
        // �̻�֧����ԿKey
        array(
            'name' => 'wxpay_native_key',
            'type' => 'text',
            'value' => ''
        ),
        // ������ID
        array(
            'name' => 'wxpay_native_mchid',
            'type' => 'text',
            'value' => ''
        )
    );

    return;
}

require_once ( dirname(__FILE__).'/wxpay/WxPay.Config.php' );
require_once ( dirname(__FILE__).'/wxpay/WxPay.Api.php' );
require_once ( dirname(__FILE__).'/wxpay/WxPay.Notify.php' );
require_once ( dirname(__FILE__).'/wxpay/WxPay.PayNotifyCallBack.php' );
require_once ( dirname(__FILE__).'/wxpay/log.php' );

/**
 * ��
 */
class wxpay_native
{
	private $dir  ;
	private $site_url;


	function _config( $payment )
	{
		WxPayConfig::set_appid( $payment['wxpay_native_appid'] );
		WxPayConfig::set_mchid( $payment['wxpay_native_mchid'] );
		WxPayConfig::set_key( $payment['wxpay_native_key'] );
		WxPayConfig::set_appsecret( $payment['wxpay_native_appsecret']);
	}

	/**
     * ����֧������
     * @param   array   $order  ������Ϣ
     * @param   array   $payment    ֧����ʽ��Ϣ
     */
	function get_code($order, $payment)
	{

		$this->_config($payment);
		$root_url = str_replace('mobile/', '', $GLOBALS['ecs']->url());
		$notify_url = $root_url.'wxpay_native_notify.php';

		$out_trade_no = $order['order_sn'].'O'.$order['log_id'];

		$input = new WxPayUnifiedOrder();
		$input->SetBody( $order['order_sn'] );
		$input->SetAttach( $order['log_id'] );		//�̻�֧����־
		$input->SetOut_trade_no( $out_trade_no );		//�̻�������
		//$input->SetTotal_fee( strval(intval($order['order_amount']*100)) ); //�ܽ��
    $input->SetTotal_fee(intval(1));//�ܽ��
		$input->SetTime_start(date("YmdHis"));
		//$input->SetTime_expire(date("YmdHis", time() + 600));
		//$input->SetGoods_tag("test");
		$input->SetNotify_url( $notify_url );	//֪ͨ��ַ
		$input->SetTrade_type("NATIVE");	//��������
		$input->SetProduct_id( $order['order_sn'] );

		$result = $this->GetPayUrl($input);

		$err = '������';
		if( $result["return_code"] == 'FAIL'){
			$err = $result["return_msg"];
		}else{
			$url2 = $result["code_url"];
		}

        $html = '<button type="button" onclick="javascript:alert(\''. $err .'\')">΢��֧��</button>';
        if($url2 != NULL)
        {
            $code_url = $url2;

            $html = '<div class="wx_qrcode" style="text-align:center">';
            //$html .= $this->getcode($code_url);
			$html .= '<img alt="ɨ��֧��" src="http://paysdk.weixin.qq.com/example/qrcode.php?data='.urlencode($url2).'" style="width:150px;height:150px;margin-left: 40%;"/>';
            $html .= "</div>";

           // $html .= "<div style=\"text-align:center\">֧������<a href=\"user.php?act=order_list\" style=\"color:red\">�˴��鿴�ҵĶ���</a></div>";//����ר��


			if ( $this->is_wechat_browser() ){
				$html .= "<div style=\"text-align:center;color:blue;font-weight:bold;font-size:20px;\">������ά��ͼƬ��ʶ���ά��֧��</div>";//����ֻ���Ҳ��Ҫɨ��֧��,�򳤰�
			}else{
				$html .= "<div style=\"text-align:center\">֧������<a href=\"user.php?act=order_list\" style=\"color:red\">�˴��鿴�ҵĶ���</a></div>";
			}


			$html .='<script type="text/javascript">
				function get_wxpay_native_status( id ){
				//	$.get("user.php", "act=wxpay_native_query&id="+id,function( result ){

						//if ( result.error == 0 && result.is_paid == 1 ){

					//		window.location.href = result.url;
					//	}
					//}, "json");

					Ajax.call("user.php", "act=wxpay_native_query&id="+id, return_wxpay_order_status, "GET", "JSON");

				}
				function return_wxpay_order_status(  result ){
					if ( result.error == 0 && result.is_paid == 1 ){
						window.location.href = result.url;
					}
				}
				window.setInterval(function(){ get_wxpay_native_status("'. $order['log_id'] .'"); }, 2000);
			</script>';

        }

        return $html;
	}


    function respond()
    {
		$payment  = get_payment('wxpay_native');
		$this->_config($payment);

		$lib_path	= dirname(__FILE__).'/wxpay/';
		$logHandler= new CLogFileHandler($lib_path."logs/".date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 15);

		Log::DEBUG("begin notify");
		$notify = new PayNotifyCallBack( );
		$notify->Handle(true);

		$data = $notify->data;

		//�ж�ǩ��
			if ($data['result_code'] == 'SUCCESS') {

					$transaction_id = $data['transaction_id'];
				 // ��ȡlog_id
                    $out_trade_no	= explode('O', $data['out_trade_no']);
                    $order_sn		= $out_trade_no[0];
					$log_id			= (int)$out_trade_no[1]; // ������log_id
					$payment_amount = $data['total_fee']/100;

					$action_note = 'result_code' . ':'
					. $data['result_code']
					. ' return_code:'
					. $data['return_code']
					. ' orderId:'
					. $data['out_trade_no']
					. ' openid:'
					. $data['openid']
					. ' '.$GLOBALS['_LANG']['wxpay_native_transaction_id'] . ':'
					. $transaction_id;
					// ��ɶ�����
					order_paid($log_id, PS_PAYED, $action_note);
					return true;
			}else{
				 echo 'fail';
			}

		return false;

    }


    function getcode($url){
        if(file_exists(ROOT_PATH . 'includes/phpqrcode.php')){
            include(ROOT_PATH . 'includes/phpqrcode.php');
        }
        // ������L��M��Q��H
        $errorCorrectionLevel = 'Q';
        // ��Ĵ�С��1��10
        $matrixPointSize = 5;
        // ���ɵ��ļ���
        $tmp = ROOT_PATH .'images/qrcode/';
        if(!is_dir($tmp)){
            @mkdir($tmp);
        }
        $filename = $tmp . $errorCorrectionLevel . $matrixPointSize . '.png';
        QRcode::png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        return '<img src="'.$GLOBALS['ecs']->url(). 'images/qrcode/'.basename($filename).'" />';
    }

    function log($file,$txt)
    {
       $fp =  fopen($file,'ab+');
       fwrite($fp,'-'.local_date('Y-m-d H:i:s').'---');
       fwrite($fp,$txt);
       fwrite($fp,"\r\n");
       fclose($fp);
    }

/**
	 *
	 * ����ɨ��֧��URL,ģʽһ
	 * @param BizPayUrlInput $bizUrlInfo
	 */
	public function GetPrePayUrl($productId)
	{
		$biz = new WxPayBizPayUrl();
		$biz->SetProduct_id($productId);
		$values = WxpayApi::bizpayurl($biz);
		$url = "weixin://wxpay/bizpayurl?" . $this->ToUrlParams($values);
		return $url;
	}

	/**
	 *
	 * ��������ת��Ϊurl����
	 * @param array $urlObj
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			$buff .= $k . "=" . $v . "&";
		}

		$buff = trim($buff, "&");
		return $buff;
	}

	/**
	 *
	 * ����ֱ��֧��url��֧��url��Ч��Ϊ2Сʱ,ģʽ��
	 * @param UnifiedOrderInput $input
	 */
	public function GetPayUrl($input)
	{
		if($input->GetTrade_type() == "NATIVE")
		{
			$result = WxPayApi::unifiedOrder($input);
			return $result;
		}
	}

function is_wechat_browser(){
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false){
		  //echo '��΢���������ֹ���';
		  return false;
		} else {
		  //echo '΢����������������';
		  //preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches);
		  //echo '<br>���΢�Ű汾��Ϊ:'.$matches[2];
		  return true;
		}
	}
}

?>