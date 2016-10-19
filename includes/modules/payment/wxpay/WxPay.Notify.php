<?php
/**
 *
 * �ص�������
 * @author widyhu
 *
 */
class WxPayNotify extends WxPayNotifyReply
{
	/**
	 *
	 * �ص����
	 * @param bool $needSign  �Ƿ���Ҫǩ�����
	 */
	final public function Handle($needSign = true)
	{
		$msg = "OK";
		//������false��ʱ�򣬱�ʾnotify�е���NotifyCallBack�ص�ʧ�ܻ�ȡǩ��У��ʧ�ܣ���ʱֱ�ӻظ�ʧ��
		$result = WxpayApi::notify(array($this, 'NotifyCallBack'), $msg);
		if($result == false){
			$this->SetReturn_code("FAIL");
			$this->SetReturn_msg($msg);
			$this->ReplyNotify(false);
			return;
		} else {
			//�÷�֧�ڳɹ��ص���NotifyCallBack�������������֮������
			$this->SetReturn_code("SUCCESS");
			$this->SetReturn_msg("OK");
		}
		$this->ReplyNotify($needSign);
	}

	/**
	 *
	 * �ص�������ڣ��������д�÷���
	 * ע�⣺
	 * 1��΢�Żص���ʱʱ��Ϊ2s�������û�ʹ���첽�������̣�ȷ�ϳɹ�֮�����̻ظ�΢�ŷ�����
	 * 2��΢�ŷ������ڵ���ʧ�ܻ��߽ӵ��ذ�Ϊ��ȷ�ϰ���ʱ�򣬻ᷢ�����ԣ���ȷ����Ļص��ǿ�������
	 * @param array $data �ص����ͳ��Ĳ���
	 * @param string $msg ����ص�����ʧ�ܣ����Խ�������Ϣ������÷���
	 * @return true�ص�������ɲ���Ҫ�����ص���false�ص�����δ�����Ҫ�����ص�
	 */
	public function NotifyProcess($data, &$msg)
	{
		//TODO �û���������֮����Ҫ��д�÷������ɹ���ʱ�򷵻�true��ʧ�ܷ���false
		return true;
	}

	/**
	 *
	 * notify�ص��������÷�������Ҫ��ֵ��Ҫ����Ĳ���,������д
	 * @param array $data
	 * @return true�ص�������ɲ���Ҫ�����ص���false�ص�����δ�����Ҫ�����ص�
	 */
	final public function NotifyCallBack($data)
	{
		$msg = "OK";
		$result = $this->NotifyProcess($data, $msg);

		if($result == true){
			$this->SetReturn_code("SUCCESS");
			$this->SetReturn_msg("OK");
		} else {
			$this->SetReturn_code("FAIL");
			$this->SetReturn_msg($msg);
		}
		return $result;
	}

	/**
	 *
	 * �ظ�֪ͨ
	 * @param bool $needSign �Ƿ���Ҫǩ�����
	 */
	final private function ReplyNotify($needSign = true)
	{
		//�����Ҫǩ��
		if($needSign == true &&
			$this->GetReturn_code($return_code) == "SUCCESS")
		{
			$this->SetSign();
		}
		WxpayApi::replyNotify($this->ToXml());
	}
}