<?php
/**
 *
 * ΢��֧��API�쳣��
 * @author widyhu
 *
 */
class WxPayException extends Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
