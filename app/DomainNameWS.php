<?php
/**
 * Ficoran -rajapintaan tarvittavat luokat
 * Tämä tiedosto sisältää useamman mini-luokan
 */
 
/**
 * Verkkotunnuksen varauspyyntö
 */
class apply_request {
  public $name; // string
  public $valid_applicant_confirmation; // boolean
  public $based_on_person_name; // boolean
  public $person_name_registration_id; // int
  public $person_name_registration_number; // string
  public $domain_name_holder_company_type; // int
  public $domain_name_holder_business_id; // string
  public $domain_name_holder_person_id; // string
  public $electronic_notification_approval; // boolean
  public $data_publishing_approval; // boolean
  public $nameservers; // ArrayOfnameserver
  public $contacts; // ArrayOfcontact
  public $context; // context
  public $domain_validity_period_in_months; // int
}

/**
 * Nimipalvelin
 */
class nameserver {
  public $name; // string
  public $ipaddress; // string
}

/**
 * Kontakti
 */
class contact {
  public $type; // int
  public $company; // string
  public $first_names; // ArrayOfstring
  public $last_name; // string
  public $postal_address; // string
  public $postal_code; // string
  public $postal_office; // string
  public $phone; // string
  public $email; // string
  public $language_code; // string
  public $department; // string
}

/**
 * Viestikonteksi (käytetään rajapintaan autentikoidessa)
 */
class context {
  public $user_name; // string
  public $mac; // string
  public $timestamp; // dateTime
}

/**
 * Verkkotunnusvarauksen vastaus
 */
class apply_response {
  public $code; // boolean
  public $timestamp; // dateTime
  public $webdomain_validation_errors; // ArrayOferror_message
  public $contact_validation_errors; // ArrayOferror_message
  public $nameserver_validation_errors; // ArrayOferror_message
  public $nameserver_technical_errors; // ArrayOferror_message
}

/**
 * Virhesanoma
 */
class error_message {
  public $description; // string
  public $code; // string
}

/**
 * Verkkotunnuksen Nimipalvelun muutospyyntö
 */
class change_nameservers_request {
  public $webdomain_name; // string
  public $authorization_key; // string
  public $nameservers; // ArrayOfnameserver
  public $context; // context
}

/**
 * Verkkotunnuksen Nimipalvelun muutospyynnön vastaus
 */
class change_nameservers_response {
  public $code; // boolean
  public $timestamp; // dateTime
  public $webdomain_validation_errors; // ArrayOferror_message
  public $nameserver_validation_errors; // ArrayOferror_message
  public $nameserver_technical_errors; // ArrayOferror_message
}

/**
 * Verkkotunnuksen poistopyyntö
 */
class remove_nameservers_request {
  public $webdomain_name; // string
  public $context; // context
}

/**
 * Verkkotunnuksen poistopyynnön vastaus
 */
class remove_nameservers_response {
  public $code; // boolean
  public $timestamp; // dateTime
  public $webdomain_validation_errors; // ArrayOferror_message
}

/**
 * Verkkotunnuksen uusimispyyntö
 */
class renew_request {
  public $webdomain_name; // string
  public $authorization_key; // string
  public $context; // context
  public $domain_validity_period_in_months; // int
}

/**
 * Verkkotunnuksen uusimispyynnön vastaus
 */
class renew_response {
  public $code; // boolean
  public $timestamp; // dateTime
  public $webdomain_validation_errors; // ArrayOferror_message
}

/**
 * Verkkotunnuksen varaustapahtuma
 */
class Apply {
  public $webDomainRequest; // apply_request
}

/**
 * Verkkotunnuksen varaustapahtuman vastaus
 */
class ApplyResponse {
  public $ApplyResult; // apply_response
}

/**
 * Dummy -olio
 */
class Dummy2 {
  public $webDomain; // apply_request
  public $nameServer; // nameserver
  public $contract; // contact
  public $errormessage; // error_message
}

/**
 * Dummy -olio, joka ilmentää vastausta
 */
class DummyResponse {
}

/**
 * Verkkotunnuksen nimipalvelujen muutos
 */
class ChangeNameServers {
  public $changeNameServerRequest; // change_nameservers_request
}

/**
 * Verkkotunnuksen nimipalvelu muutoksen vastaus
 */
class ChangeNameServersResponse {
  public $ChangeNameServersResult; // change_nameservers_response
}

/**
 * Verkkotunnuksen nimipalveluiden poisto
 */
class RemoveNameServers {
  public $removeNameServerRequest; // remove_nameservers_request
}

/**
 * Verkkotunnuksen nimipalveluiden poiston vastaus
 */
class RemoveNameServersResponse {
  public $RemoveNameServersResult; // remove_nameservers_response
}

/**
 * Verkkotunnuksen uusiminen
 */
class Renew {
  public $renewRequest; // renew_request
}

/**
 * Verkkotunnuksen uusimispyynnön vastaus
 */
class RenewResponse {
  public $RenewResult; // renew_response
}

/**
 * Ping -luokka, jolla voidaan testata yhteyttä rajapintaan
 */
class Ping {
}

/**
 * Ping -pyynnön vastaus
 */
class PingResponse {
  public $PingResult; // string
}

class char {
}

class duration {
}

class guid {
}


/**
 * DomainNameWS class
 * Ficoran rajapinnan pääluokka
 * 
 * @author    	Suomen Viestintävirasto
 * @modified 	Janne Martikainen
 * @copyright 	Suomen Viestintävirasto, Janne Martikainen
 */
class DomainNameWS extends SoapClient {
	
	private $server = 'http://domainws.ficora.fi';
	//private $server = 'https://domainws.ficora.fi/fidomaintest/DomainNameWS_FicoraDomainNameWS.svc';
	private $wsdl = "https://domainws.ficora.fi/fidomaintest/DomainNameWS_FicoraDomainNameWS.svc?wsdl";
	
	var $setting;
	
	//Tunnukset järjestelmään
	var $user_name;
	var $secret_key;
	
	private static $classmap = array(
		'apply_request' => 'apply_request',
		'nameserver' => 'nameserver',
		'contact' => 'contact',
		'context' => 'context',
		'apply_response' => 'apply_response',
		'error_message' => 'error_message',
		'change_nameservers_request' => 'change_nameservers_request',
		'change_nameservers_response' => 'change_nameservers_response',
		'remove_nameservers_request' => 'remove_nameservers_request',
		'remove_nameservers_response' => 'remove_nameservers_response',
		'renew_request' => 'renew_request',
		'renew_response' => 'renew_response',
		'Apply' => 'Apply',
		'ApplyResponse' => 'ApplyResponse',
		'Dummy2' => 'Dummy2',
		'DummyResponse' => 'DummyResponse',
		'ChangeNameServers' => 'ChangeNameServers',
		'ChangeNameServersResponse' => 'ChangeNameServersResponse',
		'RemoveNameServers' => 'RemoveNameServers',
		'RemoveNameServersResponse' => 'RemoveNameServersResponse',
		'Renew' => 'Renew',
		'RenewResponse' => 'RenewResponse',
		'Ping' => 'Ping',
		'PingResponse' => 'PingResponse',
		'char' => 'char',
		'duration' => 'duration',
		'guid' => 'guid',
	   );
	
	/**
	 *	
	 */ 
	public function DomainNameWS($wsdl = false, $options = array()) {
		
		//Haetaan tunnukset
		$this->setting = new Setting();
		$this->user_name = $this->setting->getVar('domain/ficora/username');
		$this->secret_key = $this->setting->getVar('ficora/secretkey');
		
		foreach(self::$classmap as $key => $value) {
		  if(!isset($options['classmap'][$key])) {
			$options['classmap'][$key] = $value;
		  }
		}
		parent::__construct($this->wsdl, $options);
	}
	
	
	function getUserName() {
		if(strlen($this->user_name) > 0)
			return $this->user_name;
		else
			die("FICORA: Käyttäjätunnus täytyy asettaa!");
	}
	function setUserName($_value) {
		$this->user_name = $_value;
	}
	
	function getSecretKey() {
		if(strlen($this->secret_key) > 0)
			return $this->secret_key;
		else
			die("FICORA: Varmenne täytyy asettaa!");
	}
	function setSecretKey($_value) {
		$this->secret_key = $_value;
	}
	
	/**
	* Suoritetaan verkkotunnuksen varaus
	*
	* @param Apply $parameters
	* @return Apply Response
	*/
	public function Apply(Apply $parameters) {
		return $this->__soapCall('Apply', array($parameters),array(
				'uri' => $this->server,
				'soapaction' => ''
			   )
		);
	}

	public function Dummy2(Dummy2 $parameters) {
		return $this->__soapCall('Dummy2', array($parameters),       array(
				'uri' => $this->server,
				'soapaction' => ''
			   )
		  );
	}

	/**
	* Vaihdetaan nimipalvelimet
	*
	* @param ChangeNameServers $parameters
	* @return ChangeNameServers Response
	*/
	public function ChangeNameServers(ChangeNameServers $parameters) {
	return $this->__soapCall('ChangeNameServers', array($parameters),       array(
			'uri' => $this->server,
			'soapaction' => ''
		   )
	  );
	}

	/**
	* Poistetaan nimipalvelimet verkkotunnukselta
	*
	* @param RemoveNameServers $parameters
	* @return RemoveNameServers Response
	*/
	public function RemoveNameServers(RemoveNameServers $parameters) {
	return $this->__soapCall('RemoveNameServers', array($parameters),       array(
			'uri' => $this->server,
			'soapaction' => ''
		   )
	  );
	}

	/**
	* Uusitaan verkkotunnus
	*
	* @param Renew $parameters
	* @return Renew Response
	*/
	public function Renew(Renew $parameters) {
	return $this->__soapCall('Renew', array($parameters),       array(
			'uri' => $this->server,
			'soapaction' => ''
		   )
	  );
	}

	/**
	* Pingataan ficoran rajapintaan. Tällä metodilla voidaan testata onko yhteys sovelluksen ja rajapinnan välissä kunnossa
	*
	* @param Ping $parameters
	* @return Ping response
	*/
	public function Ping(Ping $parameters) {
	return $this->__soapCall('Ping', array($parameters),       array(
			'uri' => $this->server,
			'soapaction' => ''
		   )
	  );
	}

}

?>
