<?php

/**
 * Verkkotunnusjärjestelmän pääluokka. Sisältää domainvarausjärjestelmän päätoiminnot.
 * 
 * Author: Janne Martikainen
 * Created on 4.6.2009
 * 
 */
class DomainInterface {
	
	//Domain-tiedot
	var $domain;
	var $domain_valid_applicant_confirmation;
	var $domain_based_on_person_name;
	var $domain_person_registration_id;
	var $domain_person_registration_number;
	var $domain_holder_person_id;
	var $domain_holder_company_type;
	var $domain_holder_business_id;
	var $domain_electronic_notification_approval; 
	var $domain_domain_data_publishing_approval; 
	var $domain_validity_period; //Miten pitkään domain on voimassa (kuukausissa), default 12kk
	var $tld;
	
	//Nimipalvelimet
	private	$nameserver_1;
	private	$nameserver_2;

	//Yhteystiedot
	var $contact_type;
	var $contact_company;
	var $contact_title;
	var $contact_department;
	var $contact_firstname;
	var $contact_lastname;
	var $contact_postal_address;
	var $contact_postal_address2;
	var $contact_postal_address3;
	var $contact_postal_code;
	var $contact_postal_office;
	var $contact_postal_state;
	var $contact_country;
	var $contact_phone;
	var $contact_phone_extension;
	var $contact_fax;
	var $contact_email;
	var $contact_language_code;

	
	//Array, eri www-osoitteiden päätteistä ja palvelu, josta niiden voimassaolo voidaan tarkastaa (korvataan myöhemmin toisella ratkaisulla)
	var $suffixArray;
	var $ficoraClient;		//Ficoran rajapinnan toiminnot	
	var $JokerComContact;	//Joker.com Contact
	var $jokerComDomain;	//Joker.com Domain
	
	
	private $useInterface;	//Valinta mitä rajapintaa käytetään (ficora/joker)
	
	//Ficoran rajapinnan tarvitsemat attribuutit (siirretään ficora -luokkaan)
	var $error_message;
	
	
    function __construct() {
	
		$this->ficoraClient = new DomainNameWS();
		$this->JokerComContact = new JokerComContact();
		$this->JokerComDomain = new JokerComDomain();
		
		$this->setInterface('ficora');					//Oletuksena päälle ficoran rajapinta
		$this->domain_validity_period = 12;				//Oletus 12kk
		$this->ficoraClient_authorization_key = '';			//Ficoran valtuutusavain
		$this->contact_language_code = "fi-FI";
		
		//Let's set the nameservers
		$this->nameserver_1 = "ns1.test.fi";
		$this->nameserver_2 = "ns2.test.fi";
		
		
		//Listataan ylätason verkkotunnukset ja haetaan kunkin whois -palvelu
    	$index = 0;
    	$this->serverList[$index]['top']      = 'com';
		$this->serverList[$index]['server']   = 'whois.crsnic.net';
		$this->serverList[$index]['response'] = 'No match for';
		$this->serverList[$index]['check']    = false;
		$this->serverList[$index]['default']    = false;
		$index++;
		
		$this->serverList[$index]['top']      = 'net';
		$this->serverList[$index]['server']   = 'whois.crsnic.net';
		$this->serverList[$index]['response'] = 'No match for';
		$this->serverList[$index]['check']    = false;
		$this->serverList[$index]['default']    = false;
		$index++;
	
		$this->serverList[$index]['top']      = 'org';
		$this->serverList[$index]['server']   = 'whois.publicinterestregistry.net';
		$this->serverList[$index]['response'] = 'NOT FOUND';
		$this->serverList[$index]['check']    = false;
		$this->serverList[$index]['default']    = false;
		$index++;
		
		$this->serverList[$index]['top']      = 'info';
		$this->serverList[$index]['server']   = 'whois.afilias.net';
		$this->serverList[$index]['response'] = 'NOT FOUND';
		$this->serverList[$index]['check']    = false;
		$this->serverList[$index]['default']    = false;
		$index++;
		
		$this->serverList[$index]['top']      = 'name';
		$this->serverList[$index]['server']   = 'whois.nic.name';
		$this->serverList[$index]['response'] = 'No match';
		$this->serverList[$index]['check']    = false;
		$this->serverList[$index]['default']    = false;
		$index++;
	
		$this->serverList[$index]['top']      = 'biz';
		$this->serverList[$index]['server']   = 'whois.nic.biz';
		$this->serverList[$index]['response'] = 'Not found';
		$this->serverList[$index]['check']    = false;
		$this->serverList[$index]['default']    = false;
		$index++;
	
		$this->serverList[$index]['top']      = 'tv';
		$this->serverList[$index]['server']   = 'whois.internic.net';
		$this->serverList[$index]['response'] = 'No match for';
		$this->serverList[$index]['check']    = false;
		$this->serverList[$index]['default']    = false;
		$index++;
		
		$this->serverList[$index]['server']   = 'whois.ficora.fi';
		$this->serverList[$index]['top']      = 'fi';
		$this->serverList[$index]['response'] = 'Domain not';
		$this->serverList[$index]['status']    = false;
		$this->serverList[$index]['default']    = false;
		$index++;
    }
    
	
	/** Asetetaan ja haetaan käytettävä domain-rajapinta
	 * - Käytettävä rajapinta on pakko asettaa ennen kuin luokkaa voidaan käyttää. Oletuksena konstruktorissa asetetaan käyttöön ficora.
	 *
	 */
	function setInterface($_value) {
		$this->useInterface = $_value;
	}
	function getInterface() {
		return $this->useInterface;
	}
	
	
	/** Asetetaan ja haetaan verkkotunnus
	 * - Tämä täytyy olla asetettuna, ennen kuin eri toimintoja voidaan tehdä
	 * @param string $value domain
	 */
	function setDomain($_value) {
		
		//Asetetaan automaattisesti domainin mukaan käytettävä rajapinta
		$parts = explode('.',$_value);
		
		//Verkkotunnus pitää antaa oikeassa muodossa esim. esimerkki.fi tai www.esimerkki.fi
		if(count($parts) == 2) {
			if($parts[1] == 'fi') {
				$this->setInterface('ficora');
				$this->domain_validity_period = "36";	//Fi -domain on oletuksena 36kk voimassa
			} else
				$this->setInterface('joker');
			
			$this->tld = $parts[1];
			$this->domain = $_value;
			
		} elseif(count($parts) == 3 && $parts[0] == 'www') {
			if($parts[2] == 'fi')
				$this->setInterface('ficora');
			else
				$this->setInterface('joker');
			
			$this->domain = $_value;
		}
		
		
	}
	/**
	 * Haetaan asetettu domain
	 */
	function getDomain() {
		return $this->domain;
	}
	
	/**
	 * Nimipalvelimien 1 & 2 aksessorimetodit
	 */
	function setNameServer1($_name, $_ip) {
		$this->nameserver_1->name = $_name;
		$this->nameserver_1->ipaddress = $_ip;
	}
	function getNameServer1() {
		return $this->_nameserver_1;
	}
	function setNameServer2($_name, $_ip) {
		$this->nameserver_2->name = $_name;
		$this->nameserver_2->ipaddress = $_ip;
	}
	function getNameServer2() {
		return $this->nameserver_2;
    
	}
	
	/**
	 * haetaan serverilista
	 */
	function getServerList() {
		return $this->serverList;
	}
	
    /** Tarkastaa domainin varattavuuden
	 * @param string $_domain verkkotunnus
	 * @param boolean $_show_alternatives näytetäänkö varaustilanne muiden ylätasonverkkotunnuksien osalta
	 * @return array $serverlist jos haetaan useampi domain kerrallaan, niin palautetaan koko ylätasonverkkotunnus lista arvoineen
	 * @return boolean Jos haetaan yhden domainin varattavuus, niin palautetaan vain flagi
	 */
    function checkAvailability($_domain, $_show_alternatives = true) {
 		
		$domain_extension = '';
		$domain = $_domain;
		
		//Jos syötettiin päätteellinen domain, pilkotaan se osiin
		$domain_arr = explode(".",$_domain);
		
		if(count($domain_arr) > 1) {
			$domain = $domain_arr[0];
			$domain_extension = $domain_arr[1];
		}
		
		//Otetaan mukaan rinnakkaisverkkotunnukset
		if($_show_alternatives) {
		
			//Käydään domainpäätteet läpi ja tsekataan kunkin päätteen kohdalla varattavuus
			if(is_array($this->serverList)) {
				foreach($this->serverList as $index => $server) {
					
					$current_domain = $domain.".".$server['top'];
					
					//Testataan saadaanko yhteyttä palvelimeen
					$fp = fsockopen($server['server'], 43, &$errstr, &$errno, 10);
						
					fputs($fp, $current_domain."\r\n");
						
					$rowCount = 0;
					while(!feof($fp) || $rowCount <= 100)
					{
						$text .= fgets($fp, 4096);    
						$rowCount++;
					}
					
					//Täydennetään server-arrayta
					if(preg_match("/".$server['response']."/",$text, $matches))
					{
						$this->serverList[$index]['status'] = true;   
					} else {
						$this->serverList[$index]['status'] = false;  
					}
					
					$this->serverList[$index]['domain'] = $current_domain;
					
					if($domain_extension == $this->serverList[$index]['top'])
						$this->serverList[$index]['default'] = 1;
					
				}
				
			}
		
			return $this->serverList;
		
		//Haetaan vain yksi verkkotunnus
		} else {
			
			if(is_array($this->serverList)) {
				foreach($this->serverList as $index => $server) {
					
					if($server['top'] == $domain_extension) {
						$current_domain = $domain.".".$server['top'];
						$fp = fsockopen($server['server'], 43, &$errstr, &$errno, 10);
						
						fputs($fp, $current_domain."\r\n");
						
						$rowCount = 0;
						while(!feof($fp) || $rowCount <= 100)
						{
							$text .= fgets($fp, 4096);    
							$rowCount++;
						}
						
						//Täydennetään server-arrayta
						if(preg_match("/".$server['response']."/",$text, $matches)){
							return true;
						} else {
							return false;
						}
					}
				}
			} else
				return false;
		}
    }

	/* Testataan yhteys ficoran rajapintaan
	function Ping(){
	
		$client = new DomainNameWS();
		$ping = new Ping();

		// Performs the Ping call
		$res = $client->Ping($ping);
		return $res->PingResult;
	}*/
	
	
    /* 
     * Varataan verkkotunnus
     * @param void
	 * @return boolean
     */
	 function registerDomain() {
		
		switch ($this->getInterface())
		{
		/* FICORA */
		case 'ficora':
			
			$apply = new Apply();
			$apply->webDomainRequest = new apply_request();

			// domain-tiedot
			$apply->webDomainRequest->name = $this->domain;
			$apply->webDomainRequest->valid_applicant_confirmation = $this->domain_valid_applicant_confirmation;
			$apply->webDomainRequest->based_on_person_name =  $this->domain_based_on_person_name;
			$apply->webDomainRequest->person_name_registration_id = $this->domain_person_registration_id;
			//$apply->webDomainRequest->person_name_registration_number = $this->domain_person_registration_number;
			$apply->webDomainRequest->domain_name_holder_person_id = $this->domain_holder_person_id;
			$apply->webDomainRequest->domain_name_holder_company_type = $this->domain_holder_company_type;
			$apply->webDomainRequest->domain_name_holder_business_id = $this->domain_holder_business_id;
			$apply->webDomainRequest->electronic_notification_approval = $this->domain_electronic_notification_approval; 
			$apply->webDomainRequest->data_publishing_approval = $this->domain_data_publishing_approval; 
			
			//Nimipalvelimet
			$apply->webDomainRequest->nameservers[0] = new nameserver();
			$apply->webDomainRequest->nameservers[0]->name = $this->nameserver_1;
			$apply->webDomainRequest->nameservers[0]->ipaddress = "";	//Ei pakollinen tieto
			$apply->webDomainRequest->nameservers[1] = new nameserver();
			$apply->webDomainRequest->nameservers[1]->name = $this->nameserver_2;
			$apply->webDomainRequest->nameservers[1]->ipaddress = "";	//Ei pakollinen tieto
			
			//Yhteystiedot
			$apply->webDomainRequest->contacts[0]->type = $this->contact_type;
			$apply->webDomainRequest->contacts[0]->company = $this->contact_company;
			$apply->webDomainRequest->contacts[0]->department = $this->contact_department;
			$apply->webDomainRequest->contacts[0]->first_names[0] = $this->contact_firstname;
			$apply->webDomainRequest->contacts[0]->last_name = $this->contact_lastname;
			$apply->webDomainRequest->contacts[0]->postal_address = $this->contact_postal_address;
			$apply->webDomainRequest->contacts[0]->postal_code = $this->contact_postal_code;
			$apply->webDomainRequest->contacts[0]->postal_office = $this->contact_postal_office;
			$apply->webDomainRequest->contacts[0]->phone = $this->contact_phone;
			$apply->webDomainRequest->contacts[0]->email = $this->contact_email;
			$apply->webDomainRequest->contacts[0]->language_code = $this->contact_language_code;
			
			//Verkkotunnuksen uusijan/maksajan yhteystiedot
			$apply->webDomainRequest->contacts[1]->type = 1;
			$apply->webDomainRequest->contacts[1]->company = "Sitefactory Oy";
			$apply->webDomainRequest->contacts[1]->department = "Verkkotunnus varaukset";
			$apply->webDomainRequest->contacts[1]->first_names[0] = "Paavo";
			$apply->webDomainRequest->contacts[1]->last_name = "Haapalainen";
			$apply->webDomainRequest->contacts[1]->postal_address = "Torikatu 14";
			$apply->webDomainRequest->contacts[1]->postal_code = "80100";
			$apply->webDomainRequest->contacts[1]->postal_office = "Joensuu";
			$apply->webDomainRequest->contacts[1]->phone = "0207431920";
			$apply->webDomainRequest->contacts[1]->email = "domains@sitefactory.fi";
			$apply->webDomainRequest->contacts[1]->language_code = "fi-FI";
			
			//Verkkotunnuksen tekninen vastaava
			$apply->webDomainRequest->contacts[2]->type = 2;
			$apply->webDomainRequest->contacts[2]->company = "Sitefactory Oy";
			$apply->webDomainRequest->contacts[2]->department = "Verkkotunnus varaukset";
			$apply->webDomainRequest->contacts[2]->first_names[0] = "Paavo";
			$apply->webDomainRequest->contacts[2]->last_name = "Haapalainen";
			$apply->webDomainRequest->contacts[2]->postal_address = "Torikatu 14";
			$apply->webDomainRequest->contacts[2]->postal_code = "80100";
			$apply->webDomainRequest->contacts[2]->postal_office = "Joensuu";
			$apply->webDomainRequest->contacts[2]->phone = "0207431920";
			$apply->webDomainRequest->contacts[2]->email = "domains@sitefactory.fi";
			$apply->webDomainRequest->contacts[2]->language_code = "fi-FI";

			//Viesti-konteksti
			$apply->webDomainRequest->context = new context();
			$apply->webDomainRequest->context->user_name = $this->ficoraClient->getUserName();
			$apply->webDomainRequest->context->timestamp = $this->getTimeStamp();
			$apply->webDomainRequest->domain_validity_period_in_months = $this->domain_validity_period;
			
			
			//mac
			$macstring=
			$apply->webDomainRequest->name .
			$this->boolean2string($apply->webDomainRequest->valid_applicant_confirmation).
			$this->boolean2string($apply->webDomainRequest->based_on_person_name) .
			$apply->webDomainRequest->person_name_registration_id  .
			$apply->webDomainRequest->person_name_registration_number .
			$apply->webDomainRequest->domain_name_holder_company_type .
			$apply->webDomainRequest->domain_name_holder_business_id .
			$apply->webDomainRequest->domain_name_holder_person_id .
			$this->boolean2string($apply->webDomainRequest->electronic_notification_approval) .
			$this->boolean2string($apply->webDomainRequest->data_publishing_approval) .
			$apply->webDomainRequest->nameservers[0]->name .
			$apply->webDomainRequest->nameservers[0]->ipaddress .
			$apply->webDomainRequest->nameservers[1]->name .
			$apply->webDomainRequest->nameservers[1]->ipaddress .
			$apply->webDomainRequest->contacts[0]->type .
			$apply->webDomainRequest->contacts[0]->company .
			$apply->webDomainRequest->contacts[0]->department .
			$apply->webDomainRequest->contacts[0]->first_names[0] .
			$apply->webDomainRequest->contacts[0]->first_names[1] .
			$apply->webDomainRequest->contacts[0]->last_name .
			$apply->webDomainRequest->contacts[0]->postal_address .
			$apply->webDomainRequest->contacts[0]->postal_code .
			$apply->webDomainRequest->contacts[0]->postal_office .
			$apply->webDomainRequest->contacts[0]->phone .
			$apply->webDomainRequest->contacts[0]->email .
			$apply->webDomainRequest->contacts[0]->language_code .
			$apply->webDomainRequest->contacts[1]->type .
			$apply->webDomainRequest->contacts[1]->company .
			$apply->webDomainRequest->contacts[1]->department .
			$apply->webDomainRequest->contacts[1]->first_names[0] .
			$apply->webDomainRequest->contacts[1]->first_names[1] .
			$apply->webDomainRequest->contacts[1]->last_name .
			$apply->webDomainRequest->contacts[1]->postal_address .
			$apply->webDomainRequest->contacts[1]->postal_code .
			$apply->webDomainRequest->contacts[1]->postal_office .
			$apply->webDomainRequest->contacts[1]->phone .
			$apply->webDomainRequest->contacts[1]->email .
			$apply->webDomainRequest->contacts[1]->language_code .
			$apply->webDomainRequest->contacts[2]->type .
			$apply->webDomainRequest->contacts[2]->company .
			$apply->webDomainRequest->contacts[2]->department .
			$apply->webDomainRequest->contacts[2]->first_names[0] .
			$apply->webDomainRequest->contacts[2]->first_names[1] .
			$apply->webDomainRequest->contacts[2]->last_name .
			$apply->webDomainRequest->contacts[2]->postal_address .
			$apply->webDomainRequest->contacts[2]->postal_code .
			$apply->webDomainRequest->contacts[2]->postal_office .
			$apply->webDomainRequest->contacts[2]->phone .
			$apply->webDomainRequest->contacts[2]->email .
			$apply->webDomainRequest->contacts[2]->language_code .
			$apply->webDomainRequest->context->user_name .
			$apply->webDomainRequest->context->timestamp .
			$apply->webDomainRequest->domain_validity_period_in_months .
			$this->ficoraClient->getSecretKey();
			
			$hash = hash('sha1', $macstring);
			$apply->webDomainRequest->context->mac = $hash;
			
			//print_r($apply); die();
			//Suoritetaan varaus
			try
			{
				$res = $this->ficoraClient->Apply($apply);
			}
				catch (SoapFault $exception)
			{
				return false;
			}
			
			//print_r($res); die();
			
			//Haetaan palautusdata
			$log = new DomainLog('ficora');
			$result = '';
			//Pyyntö epäonnistui
			if($res->ApplyResult->code === false){
				$result .= "Webdomain validation error: ";
				$result .= $res->ApplyResult->webdomain_validation_errors->error_message->code;
				$result .= " ";
				$result .= $res->ApplyResult->webdomain_validation_errors->error_message->description;

				$n = sizeof($res->ApplyResult->nameserver_validation_errors->error_message);
				if ($n >0) {
						$result .= "Name server validation error: ";
						$result .= $res->ApplyResult->nameserver_validation_errors->error_message->code;
						$result .= " ";
						$result .= $res->ApplyResult->nameserver_validation_errors->error_message->description;
					}

				$n = sizeof($res->ApplyResult->contact_validation_errors->error_message);
				if ($k >0) {
					for ($i = 0; $i < $n; $i++){
						$result .= "Contact validation error: ";
						$result .= $res->ApplyResult->contact_validation_errors->error_message[$i]->code;
						$result .= " ";
						$result .= $res->ApplyResult->contact_validation_errors->error_message[$i]->description;
						}
					}
				
				$log->create('','error', $result, serialize($res), 'Apply');
				$this->error_message = $result;
				return false;
			
			//Pyyntö onnistui
			} else {
				$n = sizeof($res->ApplyResult->nameserver_technical_errors->error_message);
				if ($n == 0)
				$result .= "OK";
				if ($n >0) {
					for ($i = 0; $i < $n; $i++){
						$result .= "Technical error: ";
						$result .= $res->ApplyResult->nameserver_technical_errors->error_message[$i]->code;
						$result .= " ";
						$result .= $res->ApplyResult->nameserver_technical_errors->error_message[$i]->description;
						$result .= "\n";
						}
					}
				
				$log->create('','successful', $result, serialize($res), 'Apply');
				$this->error_message = $result;
				return true;
			}
			
		
		/* JOKER.COM */
		case 'joker':
			
			//Tarkastetaan onko kontakti olemassa jo
			$ret = $this->JokerComContact->view($this->contact_firstname.' '.$this->contact_lastname, $this->tld);
			if(!$ret) {
				
				//Luodaan ensin kontakti
				$dContact = new dummy;

				$dContact->tld = $this->tld;
				$dContact->firstname = $this->contact_firstname;
				$dContact->lastname = $this->contact_lastname;
				$dContact->title = $this->contact_title;
				
				if($this->domain_based_on_person_name)
					$dContact->individual = 'Y';
				else {
					$dContact->individual = 'N';
					$dContact->organisation = 'Sitefactory Oy';
				}
				
				$dContact->email = $this->contact_email;
				$dContact->street = $this->contact_postal_address;
				$dContact->street2 = $this->contact_postal_address2;
				$dContact->street3 = $this->contact_postal_address2;
				$dContact->city = $this->contact_postal_office;
				$dContact->state = $this->contact_postal_state;
				$dContact->postalcode = $this->contact_postal_code;
				$dContact->country = $this->JokerComContact->getLanguageCode($this->contact_country);
				
				$dContact->phone = $this->contact_phone;
				$dContact->extension = $this->contact_phone_extension;
				$dContact->fax = $this->contact_fax;
				
				$this->JokerComContact->create($dContact);
				
				if($this->JokerComContact->view($this->contact_firstname.' '.$this->contact_lastname, $this->tld)) {
					$this->jokerComDomain = new JokerComDomain();
					
					$dDomain = new Dummy;
					$dDomain->domain = $this->domain;
					$dDomain->period = $this->domain_validity_period;
					$dDomain->owner = $ret[0];
					$dDomain->billing = $this->jokerComDomain->getDomainAdminHandle($this->tld);
					$dDomain->admin = $this->jokerComDomain->getDomainAdminHandle($this->tld);
					$dDomain->tech = $this->jokerComDomain->getDomainTechHandle($this->tld);
					$dDomain->nameservers = $this->nameserver_1.":".$this->nameserver_2;
					
					if($this->jokerComDomain->register($dDomain))
						return true;
					else
						return false;
				} else
					return false;
				
			} else {
			
				if(is_array($ret)) {
					$exploded_data = explode("\n",$ret[0]);
					
					$this->jokerComDomain = new JokerComDomain();
					
					$dDomain = new Dummy;
					$dDomain->domain = $this->domain;
					$dDomain->period = $this->domain_validity_period;
					$dDomain->owner = $exploded_data[0];
					$dDomain->billing = $this->jokerComDomain->getDomainAdminHandle($this->tld);
					$dDomain->admin = $this->jokerComDomain->getDomainAdminHandle($this->tld);
					$dDomain->tech = $this->jokerComDomain->getDomainTechHandle($this->tld);
					$dDomain->nameservers = $this->nameserver_1.":".$this->nameserver_2;
					
					//print_r($dDomain); die();
					
					if($this->jokerComDomain->register($dDomain))
						return true;
					else
						return false;
								
				}
			
			}
			
		}
	}
	 
	 
	 
	 /** 
	  * Muutetaan verkkotunnuksen nimipalvelutiedot
	  * @param string $_nameserver1
	  * @param string $_nameserver2
	  * @param object $_jokerComOptionalFields kontaktiparametrit, jos vaihdetaan kontakteja muokkauksen yhteydessä 
	  */
	function changeNameServers($_nameServer1, $_nameServer2, $_jokerComOptionalFields = false) {
		
		switch ($this->getInterface())
		{
			case 'ficora':
		
				$change = new ChangeNameServers();
				$change->changeNameServerRequest = new change_nameservers_request();

				// domain information
				$change->changeNameServerRequest->webdomain_name = $this->domain;
				$change->changeNameServerRequest->ficoraClient_authorization_key = $this->ficoraClient_authorization_key;

				// nameservers
				$change->changeNameServerRequest->nameservers[0] = new nameserver();
				$change->changeNameServerRequest->nameservers[0]->name = $_nameServer1;
				$change->changeNameServerRequest->nameservers[0]->ipaddress = "";
				$change->changeNameServerRequest->nameservers[1] = new nameserver();
				$change->changeNameServerRequest->nameservers[1]->name = $_nameServer2;
				$change->changeNameServerRequest->nameservers[1]->ipaddress = "";

				// context
				$change->changeNameServerRequest->context = new context();
				$change->changeNameServerRequest->context->user_name = $this->ficoraClient->getUserName();
				$change->changeNameServerRequest->context->timestamp = getTimeStamp();
				

				// mac
				$macstring=

				$change->changeNameServerRequest->webdomain_name .
				$change->changeNameServerRequest->ficoraClient_authorization_key .
				$change->changeNameServerRequest->nameservers[0]->name .
				$change->changeNameServerRequest->nameservers[0]->ipaddress .
				$change->changeNameServerRequest->nameservers[1]->name .
				$change->changeNameServerRequest->nameservers[1]->ipaddress .

				$change->changeNameServerRequest->context->user_name .
				$change->changeNameServerRequest->context->timestamp .
				$secret_key;
				$macstring = utf8_encode($macstring);
				$hash = hash('sha1', $macstring);
				$change->changeNameServerRequest->context->mac = $hash;


				// performs the ChangeNameServers request
				try
					{
					$res = $this->ficoraClient->ChangeNameServers($change);
					}
					catch (SoapFault $exception)
					{
					return $exception;
				} 

				// get the results
				if($res->ChangeNameServersResult->code === false){
				$result .= "Webdomain validation error: ";
				$result .= $res->ChangeNameServersResult->webdomain_validation_errors->error_message->code;
				$result .= " ";
				$result .= $res->ChangeNameServersResult->webdomain_validation_errors->error_message->description;

				$n = sizeof($res->ApplyResult->nameserver_validation_errors->error_message);
				if ($n >0) {
						$result .= "Name server validation error: ";
						$result .= $res->ChangeNameServersResult->nameserver_validation_errors->error_message->code;
						$result .= " ";
						$result .= $res->ChangeNameServersResult->nameserver_validation_errors->error_message->description;
					}

				$n = sizeof($res->ChangeNameServersResult->contact_validation_errors->error_message);
				if ($k >0) {
					for ($i = 0; $i < $n; $i++){
						$result .= "Contact validation error: ";
						$result .= $res->ChangeNameServersResult->contact_validation_errors->error_message[$i]->code;
						$result .= " ";
						$result .= $res->ChangeNameServersResult->contact_validation_errors->error_message[$i]->description;
						}
					}


				}
				else {
				
				$n = sizeof($res->ChangeNameServersResult->nameserver_technical_errors->error_message);
				if ($n == 0)
				$result .= "OK";
				if ($n >0) {
					for ($i = 0; $i < $n; $i++){
						$result .= "Technical error: ";
						$result .= $res->ChangeNameServersResult->nameserver_technical_errors->error_message[$i]->code;
						$result .= " ";
						$result .= $res->ChangeNameServersResult->nameserver_technical_errors->error_message[$i]->description;
						$result .= "\n";
						}
					}
				}
			
				return $result;
			
			case 'joker':
				
				
				$this->jokerComDomain = new JokerComDomain();
				
				$fields = new Dummy;
				$fields->nameservers = $_nameServer1.':'.$_nameServer2;
				
				if($_jokerComOptionalFields) {
					$fields->admin = $_jokerComOptionalFields->admin;
					$fields->billing = $_jokerComOptionalFields->billing;
					$fields->tech =  $_jokerComOptionalFields->tech;
				}
				
				//print_r($fields); die();
				
				$this->jokerComDomain->modify($this->domain, $fields);
		
		}

	}
	
	/**
	 * Uusitaan verkkotunnus
	 * - Osin keskeneräinen
	 */
	function renewDomain() {
		
		switch ($this->getInterface())
		{
			case 'ficora':
		
				$renew = new Renew();
				$renew->renewRequest = new renew_request();

				// domain information
				$renew->renewRequest->webdomain_name = $this->domain;

				// context
				$renew->renewRequest->context = new context();
				$renew->renewRequest->context->user_name = $this->ficoraClient->getUserName();
				$renew->renewRequest->context->timestamp = getTimeStamp();
				$renew->renewRequest->domain_domain_validity_period_in_months = $this->domain_validity_period;
				// mac
				$macstring=
				$renew->renewRequest->webdomain_name .
				$renew->renewRequest->context->user_name .
				$renew->renewRequest->context->timestamp .
				$renew->renewRequest->domain_domain_validity_period_in_months .
				$secret_key;

				$hash = hash('sha1', $macstring);
				$renew->renewRequest->context->mac = $hash;


				// performs the Renew request
				try
					{
					$res = $client->Renew($renew);
					}
					catch (SoapFault $exception)
					{
					echo $exception;
				} 

				// get the results
				if($res->RenewResult->code === false){
					$result .= "Webdomain validation error: ";
					$result .= $res->RenewResult->webdomain_validation_errors->error_message->code;
					$result .= " ";
					$result .= $res->RenewResult->webdomain_validation_errors->error_message->description;
				}
				else{
					$result .= "OK";
				}
				return $result;
				
				
			case 'joker':	
			
				$this->jokerComDomain = new JokerComDomain();
				
				$dRenew = new Dummy;
				$dRenew->domain = $this->domain;
				$dRenew->validity_period = $this->domain_validity_period;
				$dRenew->expyear = intval(date("Y") + ($this->domain_validity_period/12));
				
				if($this->jokerComDomain->renew($dRenew))
					return true;
				else
					return false;
		}
	}
	
	
	/** 
	 * Poistetaan nimipalvelut verkkotunnukselta
	 * - Osin keskeneräinen, joker.com toimii
	 */
	function deleteNameServers()  {
		
		switch ($this->getInterface())
		{
			case 'ficora':
		
				$client = new DomainNameWS();
				$remove = new RemoveNameServers();
				$remove->removeNameServerRequest = new remove_nameservers_request();


				// domain information
				$remove->removeNameServerRequest->webdomain_name = $domain_name;

				// context
				$remove->removeNameServerRequest->context = new context();
				$remove->removeNameServerRequest->context->user_name = $user_name;
				$remove->removeNameServerRequest->context->timestamp = getTimeStamp();
				
				// mac
				$macstring=
				$remove->removeNameServerRequest->webdomain_name .
				$remove->removeNameServerRequest->context->user_name .
				$remove->removeNameServerRequest->context->timestamp .
				$secret_key;

				$hash = hash('sha1', $macstring);
				$remove->removeNameServerRequest->context->mac = $hash;

				// performs the RemoveNameServers request
				try
					{
					$res = $client->RemoveNameServers($remove);
					}
					catch (SoapFault $exception)
					{
					echo $exception;
				} 


				// get the results	
				if($res->RemoveNameServersResult->code === false){
				$result .= "Webdomain validation error: ";
				$result .= $res->RemoveNameServersResult->webdomain_validation_errors->error_message->code;
				$result .= " ";
				$result .= $res->RemoveNameServersResult->webdomain_validation_errors->error_message->description;
				}
				else{
				$result .= "OK";
				}
				return $result;
			
			
			case 'joker':
				
				$this->jokerComDomain = new JokerComDomain();
				
				if($request->force)
					$this->jokerComDomain->delete($this->domain, 'Y');
				else
					$this->jokerComDomain->delete($this->domain);
		}
	}



	
	/** 
	 * Muutetaan boolean -arvo merkkijonoksi (true|false)
	 */
	function boolean2string($bool){
		if ($bool === true)
			return 'true';
		else
			return 'false';
	}

	/** 
	 * Haetaan aikaleima ficoran pään ymmärtämässä muodossa
	 */
	function getTimeStamp() {
		// Time must be UTC
		date_default_timezone_set("UTC");
		list($fraction, $full) = explode(' ', microtime());
		$fraction = substr($fraction, 2, 7); 
		$timestamp =  date("Y-m-d")."T".date("H:i:s.").$fraction."Z";
		return $timestamp;
	}
}
?>
