<?php
/**
* 	�����˺���Ϣ
*/

class WxPayConfig
{
	//=======��������Ϣ���á�=====================================
	//
	/**
	 * TODO: �޸���������Ϊ���Լ�������̻���Ϣ
	 * ΢�Ź��ں���Ϣ����
	 *
	 * APPID����֧����APPID���������ã������ʼ��пɲ鿴��
	 *
	 * MCHID���̻��ţ��������ã������ʼ��пɲ鿴��
	 *
	 * KEY���̻�֧����Կ���ο������ʼ����ã��������ã���¼�̻�ƽ̨�������ã�
	 * ���õ�ַ��https://pay.weixin.qq.com/index.php/account/api_cert
	 *
	 * APPSECRET�������ʺ�secert����JSAPI֧����ʱ����Ҫ���ã� ��¼����ƽ̨�����뿪�������Ŀ����ã���
	 * ��ȡ��ַ��https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN
	 * @var string
	 */
	static $APPID = 'wx426b3015555a46be';
	static $MCHID = '1225312702';
	static $KEY = 'e10adc3949ba59abbe56e057f20f883e';
	static $APPSECRET = '01c6d59a3f9024db6336662ac95c8e74';

	//=======��֤��·�����á�=====================================
	/**
	 * TODO�������̻�֤��·��
	 * ֤��·��,ע��Ӧ����д����·�������˿��������ʱ��Ҫ���ɵ�¼�̻�ƽ̨���أ�
	 * API֤�����ص�ַ��https://pay.weixin.qq.com/index.php/account/api_cert������֮ǰ��Ҫ��װ�̻�����֤�飩
	 * @var path
	 */
	static $SSLCERT_PATH = '../cert/apiclient_cert.pem';
	static $SSLKEY_PATH = '../cert/apiclient_key.pem';

	//=======��curl�������á�===================================
	/**
	 * TODO���������ô��������ֻ����Ҫ�����ʱ������ã�����Ҫ����������Ϊ0.0.0.0��0
	 * ������ͨ��curlʹ��HTTP POST�������˴����޸Ĵ����������
	 * Ĭ��CURL_PROXY_HOST=0.0.0.0��CURL_PROXY_PORT=0����ʱ����������������Ҫ�����ã�
	 * @var unknown_type
	 */
	static $CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
	static $CURL_PROXY_PORT = 0;//8080;

	//=======���ϱ���Ϣ���á�===================================
	/**
	 * TODO���ӿڵ����ϱ��ȼ���Ĭ�Ͻ������ϱ���ע�⣺�ϱ���ʱ��Ϊ��1s�����ϱ����۳ɰܡ������׳��쳣����
	 * ����Ӱ��ӿڵ������̣��������ϱ�֮�󣬷���΢�ż��������õ���������������
	 * ���������ϱ���
	 * �ϱ��ȼ���0.�ر��ϱ�; 1.����������ϱ�; 2.ȫ���ϱ�
	 * @var int
	 */
	static $REPORT_LEVENL = 1;

	static $NOTIFY_URL = '';


	static public function set_appid( $val ){
		self::$APPID = $val;
	}
	static public function set_mchid( $val ){
		self::$MCHID= $val;
	}
	static public function set_key( $val ){
		self::$KEY = $val;
	}

	static public function set_appsecret( $val ){
		self::$APPSECRET = $val;
	}
	static public function set_sslcert_path( $val ){
		self::$SSLCERT_PATH = $val;
	}
	static public function set_sslkey_path( $val ){
		self::$SSLKEY_PATH = $val;
	}

}
