<?php

/**
 * @Created 25.10.2010
 * @author Janne Martikainen
 * @copyright Janne Martikainen
 * 
 * Luodaan sähköpostiosoitteita mail.sitefactory.fi -palvelimelle.
 */

class EmailCreator
{
	private $_server = 'http://mail.sitefactory.fi/postfixadmin/admin/autocreate-mailbox.php';		//Server address
	var $emailList;			//Array containing email addresses
	private $username;
	private $password;
	private $setting;
	
	function __construct($emails = false) {
		
		if($emails) {
			$this->setEmails($emails);
		}
		
		//Haetaan tunnistautumistiedot
		$this->setting = new Setting;
		$this->username = $this->setting->getVar('domain/email/username');
		$this->password = $this->setting->getVar('domain/email/password');
	}
	
	/**
	 * Asetetaan sähköpostiosoitteet
	 */
	function setEmails($_data) {
		if(is_array($_data)) {
			foreach($_data as $email) {
				if($this->_validateEmailAddress($email))
					$this->emailList[] = $email;
			}
		}
	}
	
	/**
	 * Otetaan yhteys sähköpostipalvelimelle ja luodaan sähköpostiosoitteet
	 * @return boolean
	 */
	function create() {
		
		//Loopataan sähköpostiosoitteet läpi ja generoidaan automaattisesti salasana
		if(is_array($this->emailList)) {
			
			$aEmails = array();
			$postedEmails = '';
			
			foreach($this->emailList as $email) {
				$temp = array();
				$temp['email'] = $email;
				$temp['password'] = $this->_generatePassword($email);
				$aEmails[] = $temp;
			}
			
			//Luodaan postattava data
			foreach ($aEmails as $key => $email) {
				$postedEmails .= $email['email'].';'.$email['password'].';';
			}
			
			$postedEmails = substr($postedEmails,0,strlen($postedEmails)-1);
			
			//echo $postedEmails; die();
			
			$postParams = array(
			'luo' => '1',
			'emails' => $postedEmails
			);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->_server);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_USERPWD, $this->username.":".$this->password);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
			
			$data = curl_exec($ch);
			curl_close($ch);
			
			print_r($data); die();
		} else
			return false;
	}
	
	/*
	 * Tarkastetaan sähköpostiosoitteen muoto 
	*/
	private function _validateEmailAddress($email) {
		$parts = explode('@',$email);
		$lastPart = explode('.',$parts[1]);

		if ($parts[0] && $parts[1] && count($lastPart) > 1)	{
			if (strlen($parts[0]) >= 1 && strlen($parts[1]) >= 4)
				return true;
			else
				return false;
		}
		else
			return false;
	}
	
	/**
	 * Generoidaan salasana
	 * @param string $_string merkkijono, jota käytetään salasanan generointiin
	 * @param int $_lenght salasanan merkkien määrä
	 * @return string $password
	 */
	function _generatePassword($_string, $_lenght=6) {
		return strtolower(substr(md5($_string."_".rand(100,1000)),0,$_lenght));
	}
	
}

?>