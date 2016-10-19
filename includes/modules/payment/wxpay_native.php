<?php
/**
 * ECSHOP 微信扫码支付

 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: douqinghua $
 * $Id: unionpay.php 17063  $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}


// 包含配置文件
$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/wxpay_native.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'wxpay_native_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'Jason.C';

    /* 网址 */
    $modules[$i]['website'] = 'Jason.C';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
       $modules[$i]['config'] = array(
        // 微信公众号身份的唯一标识
        array(
            'name' => 'wxpay_native_appid',
            'type' => 'text',
            'value' => ''
        ),
        // JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
        array(
            'name' => 'wxpay_native_appsecret',
            'type' => 'text',
            'value' => ''
        ),
        // 商户支付密钥Key
        array(
            'name' => 'wxpay_native_key',
            'type' => 'text',
            'value' => ''
        ),
        // 受理商ID
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
 * 类
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
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
	function get_code($order, $payment)
	{

		$this->_config($payment);
		$root_url = str_replace('mobile/', '', $GLOBALS['ecs']->url());
		$notify_url = $root_url.'wxpay_native_notify.php';

		$out_trade_no = $order['order_sn'].'O'.$order['log_id'];

		$input = new WxPayUnifiedOrder();
		$input->SetBody( $order['order_sn'] );
		$input->SetAttach( $order['log_id'] );		//商户支付日志
		$input->SetOut_trade_no( $out_trade_no );		//商户订单号
		//$input->SetTotal_fee( strval(intval($order['order_amount']*100)) ); //总金额
    $input->SetTotal_fee(intval(1));//总金额
		$input->SetTime_start(date("YmdHis"));
		//$input->SetTime_expire(date("YmdHis", time() + 600));
		//$input->SetGoods_tag("test");
		$input->SetNotify_url( $notify_url );	//通知地址
		$input->SetTrade_type("NATIVE");	//交易类型
		$input->SetProduct_id( $order['order_sn'] );

		$result = $this->GetPayUrl($input);

		$err = '出错了';
		if( $result["return_code"] == 'FAIL'){
			$err = $result["return_msg"];
		}else{
			$url2 = $result["code_url"];
		}

        $html = '<button type="button" onclick="javascript:alert(\''. $err .'\')">微信支付</button>';
        if($url2 != NULL)
        {
            $code_url = $url2;

            $html = '<div class="wx_qrcode" style="text-align:center">';
            //$html .= $this->getcode($code_url);
			$html .= '<img alt="扫码支付" src="http://paysdk.weixin.qq.com/example/qrcode.php?data='.urlencode($url2).'" style="width:150px;height:150px;margin-left: 40%;"/>';
            $html .= "</div>";

           // $html .= "<div style=\"text-align:center\">支付后点击<a href=\"user.php?act=order_list\" style=\"color:red\">此处查看我的订单</a></div>";//电脑专用


			if ( $this->is_wechat_browser() ){
				$html .= "<div style=\"text-align:center;color:blue;font-weight:bold;font-size:20px;\">长按二维码图片，识别二维码支付</div>";//如果手机版也需要扫码支付,则长按
			}else{
				$html .= "<div style=\"text-align:center\">支付后点击<a href=\"user.php?act=order_list\" style=\"color:red\">此处查看我的订单</a></div>";
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

		//判断签名
			if ($data['result_code'] == 'SUCCESS') {

					$transaction_id = $data['transaction_id'];
				 // 获取log_id
                    $out_trade_no	= explode('O', $data['out_trade_no']);
                    $order_sn		= $out_trade_no[0];
					$log_id			= (int)$out_trade_no[1]; // 订单号log_id
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
					// 完成订单。
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
        // 纠错级别：L、M、Q、H
        $errorCorrectionLevel = 'Q';
        // 点的大小：1到10
        $matrixPointSize = 5;
        // 生成的文件名
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
	 * 生成扫描支付URL,模式一
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
	 * 参数数组转换为url参数
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
	 * 生成直接支付url，支付url有效期为2小时,模式二
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
		  //echo '非微信浏览器禁止浏览';
		  return false;
		} else {
		  //echo '微信浏览器，允许访问';
		  //preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches);
		  //echo '<br>你的微信版本号为:'.$matches[2];
		  return true;
		}
	}
}

?>