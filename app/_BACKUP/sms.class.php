<?php

/* Lähetetään SMS-viesti käyttäen Auronicin tarjoamaa SMS-Gatewayta
 * 
 * Esimerkki URL, jolla viesti lähetetään
 * GET = http://mon.auronic.fi:1981/sms?user=username&pwd=password&msg=x&snd=X&num=X
 * POST = http://mon.auronic.fi:2080/sms
*/

class sms {

	var $_user;			//Palvelun käyttäjätunnus
	var $_pwd;		//Palvelun salasana
	var $_msg;					//Viesti
	var $_snd;					//Lähettäjän puhelinnumero
	var $_num;					//Vastaanottajan puhelinnumero
	
	/**
	 * Konstruktori. Asetellaan tunnarit
	 */
	function sms() {
		
		$setting = new Setting();
		
		$this->_user = $setting->getVar('sms/user');
		$this->_pwd = $setting->getVar('sms/password');
	}
	
	function setSender($value) {
		
		$value = strip_tags(trim($value));
		$value = str_replace(" ","",$value);
		$value = str_replace("-","",$value);
		$value = str_replace("(", "", $value);
		$value = str_replace(")", "", $value);
		$value = str_replace("+358","0",$value);
		$this->_snd = $value;
	}
	
	function setNumber($value) {
		
		$value = strip_tags(trim($value));
		$value = str_replace(" ","",$value);
		$value = str_replace("-","",$value);
		$value = str_replace("(", "", $value);
		$value = str_replace(")", "", $value);
		$value = str_replace("+358","0",$value);
		$this->_num = $value;
	}
	
	function setMessage($value) {
		
		$value = strip_tags(trim($value));
		
		$this->_msg = $value;
	}
	
	/* Lähetetään sms-viesti */
	function sendMessage($msg, $sender, $receiver) {
		$this->setMessage($msg);
		$this->setSender($sender);
		$this->setNumber($receiver);
		
		$request['user'] = $this->_user;
		$request['pwd'] = $this->_pwd;		
		$request['snd'] = $this->_snd;
		$request['msg'] = $this->_msg;
		$request['num'] = $this->_num;
		
		
		//$url = 'http://mon.auronic.fi:1981/sms?user='.$this->_user.'&pwd='.$this->_pwd.'&msg='.$this->_msg.'&snd='.$this->_snd.'&num='.$this->_num;
		$url = 'http://mon.auronic.fi:2080/sms';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		$response = curl_exec($ch);
		curl_close($ch);
		
		/* URL: */
		//trigger_error('http://mon.auronic.fi:1981/sms?user='.$this->_user.'&pwd='.$this->_pwd.'&msg='.$this->_msg.'&snd='.$this->_snd.'&num='.$this->_num);
	
	}
	
}

?>