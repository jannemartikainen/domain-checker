<?php

/**
 * @Created 18.9.2010
 * @author Janne Martikainen
 * @copyright Janne Martikainen
 */
@include_once CLASS_APP_PATH.'jokercom_connect.class.php';

class JokerComDomain extends JokerComConnect
{
	var $type = 'domain';		//Type of class, for connect class
	var $domain_name;
	private $adminHandle;
	private $techHandle;
	
	function __construct() {
		parent::__construct();
		
		$this->adminHandle = array(
			'com' => 'CCOM-43438',
			'name' => 'CNAM-5087',
			'org' => 'CORG-15192',
			'org' => 'CNEU-5149',
			'net' => 'CNET-5346'
		);
		$this->techHandle = $this->adminHandle;
	}
	
	function getDomainAdminHandle($_tld) {
		if($_tld) {
			return $this->adminHandle[strtolower(trim($_tld))];
		}
	}
	
	function getDomainTechHandle($_tld) {
		if($_tld) {
			return $this->adminHandle[strtolower(trim($_tld))];
		}
	}
	
	/**
	 * Listataan kaikki domainit
	 * @return string http-body
	 */
	function listAll() {
		if($this->execute_request('query-domain-list')) {
			return $this->response_body;
		}
	}
	
	/* *
	 *Varataan verkkotunnus
	 *domain   	 Domain name to register
	 *period 	Registration period in months (not in years!)
	 *status 	Set domain status (only "production" is accepted so far)
	 *owner-c 	Owner contact handle
	 *billing-c 	Billing contact handle
	 *admin-c 	Administrative contact handle
	 *tech-c 	Technical contact handle
	 *ns-list 	List of name servers, delimited by colon
	*/
	function register($_fields) {
		
		$fields = array(
        "domain"   => $_fields->domain,
        "period"   => $_fields->period,
		"status"   => 'production',
        "owner-c"   => $_fields->owner,
		"billing-c"   => $_fields->billing,
        "admin-c"   => $_fields->admin,
		"tech-c"   => $_fields->tech,
        "ns-list"   => $_fields->nameservers
        );
		//print_r($fields); die();
		if($this->execute_request('domain-register',$fields)) {
			return true;
		} else
			return false;
		   
	}
	
	/**
	 * Uusitaan verkkotunnus
	 */
	function renew($_fields) {
		
		$fields = array(
        "domain"   => $_fields->domain,
        "period"   => $_fields->period,
		"expyear"   => $_fields->expyear
        );
		
		if($this->execute_request('domain-renew',$fields)) {
			return true;
		} else
			return false;
		   
	}
	
	/**
	 * Muokataan verkkotunnuksen tietoja
	 * Mahdollisia muokattavia tietoja on
	 * - Billing 	-maksaja
	 * - Admin 		-ylläpitäjä
	 * - Tech 		-tekninen yhteystieto
	 * - nameservers -nimipalvelimet
	 * @param string $domain verkkotunnus
	 * @param object $fields muutettavat kentät
	 */
	function modify($_domain, $_fields) {
		
		if($_domain) {
			$fields = array();
			
			$fields['domain'] = $_domain;
			
			if($_fields->billing)
				$fields['billing-c'] = $_fields->billing;
			if($_fields->admin)
				$fields['admin-c'] = $_fields->admin;
			if($_fields->tech)
				$fields['tech-c'] = $_fields->tech;
			if($_fields->nameservers)
				$fields['ns-list'] = $_fields->nameservers;
			
			if($this->execute_request('domain-modify',$fields)) {
				return true;
			} else
				return false;
		  } else
			return false;
	}
	
	/**
	 * Poistetaan verkkotunnukselta nimipalvelimet
	 * @param string domain verkkotunnus
	 * @param string force mikäli verkkotunnus on varattu vähemmän kuin 72 tuntia sitten, voidaan force-määreellä saada vielä rahat takaisin (y|1)
	 */
	function delete($domain, $force = false) {
		if($domain) {
			$fields = array(
			"domain"   => $domain
			);
			
			if($force)
				$fields['force'] = 'Y';
			else
				$fields['force'] = '1';
			
			if($this->execute_request('domain-delete',$fields)) {
				return $this->response_body;
			} else
				return false;
		}
	}
	
	/**
	 * Poistetaan verkkotunnukselta nimipalvelimet
	 * @param string domain verkkotunnus
	 * @param object fields muutettavat kentät
	 */
	function changeOwner($domain, $fields) {
		if($domain) {
			$fields = array(
			"domain"   => $domain,
			"name"   => $fields->firstname." ".$fields->lastname,
			"title"   => $fields->title,
			"individual"   => $fields->individual,
			"organization"   => $fields->organisation,
			"email"   => $fields->email,
			"address-1"   => $fields->street,
			"address-2"   => $fields->street2,
			"address-3"   => $fields->street3,
			"city"   => $fields->city,
			"state"   => $fields->state,
			"postal-code"   => $fields->postalcode,
			"country"   => $fields->country,
			"phone"   => $fields->phone,
			"extension"   => '',
			"fax"   => $fields->fax,
			"lang"   => $fields->country,
			);
			
		
			if($this->execute_request('domain-owner-change',$fields)) {
				return $this->response_body;
			} else
				return false;
		}
	}
	
	/**
	 * Siirretään verkkotunnus toiselle kontaktille
	 * @param object fields tarvittavat kentät
	 */
	function transfer($_fields) {
		
		$fields = array(
        "domain"   => $_fields->domain,
        "period"   => $_fields->period,
		"status"   => 'production',
		"transfer-auth-id"   => $_fields->transfer_auth_id,
		"billing-c"   => $_fields->billing,
		"admin-c"   => $_fields->admin,
		"tech-c"   => $_fields->tech,
		"owner-email"   => $_fields->email
        );
		
		if($this->execute_request('domain-transfer-in-reseller',$fields)) {
			return $this->response_body;
		} else
			return false;
		   
	}
	
	/**
	 * Luo siirtoavaimen verkkotunnukselle
	 */
	function getTransferAuthId($_fields) {
		
		$fields = array(
        "domain"   => $_fields->domain,
        );
		
		if($this->execute_request('domain-transfer-get-auth-id',$fields)) {
			return $this->response_body;
		} else
			return false;
		   
	}
	
	/**
	 * Hakee verkkotunnuksen jonkin parametrin arvon
	 * @param string domain verkkotunnus
	 * @param string property jokin verkkotunnuksen parametri
	 */
	function getProperty($domain, $property) {
	
		if($domain && $property) {
			$fields = array(
			"domain"   => $domain,
			"pname"   => $property
			);
			
			if($this->execute_request('domain-get-property',$fields)) {
				return $this->response_body;
			} else
				return false;
		}
	}
	
	/**
	 * Asettaa verkkotunnuksen jonkin parametrin
	 * @param string domain verkkotunnus
	 * @param string property jokin verkkotunnuksen parametri
	 */
	function setProperty($domain, $property, $value) {
	
		if($domain && $property) {
			$fields = array(
			"domain"   => $domain,
			"pname"   => $property,
			"pvalue"   => $value
			);
			
			if($this->execute_request('domain-set-property',$fields)) {
				return $this->response_body;
			} else
				return false;
		}
	}
	
	/**
	 * Lukitsee verkkotunnuksen
	 * @param string domain verkkotunnus
	 */
	function lock($domain) {
		if($domain) {
			
			$fields = array(
			"domain"   => $domain,
			);
			
			if($this->execute_request('domain-lock',$fields)) {
				return $this->response_body;
			} else
				return false;
		}
	}
	
	/**
	 * Poistaa verkkotunnuksen lukituksen
	 * @param string domain verkkotunnus
	 */
	function unLock($domain) {
		if($domain) {
			
			$fields = array(
			"domain"   => $domain,
			);
			
			if($this->execute_request('domain-unlock',$fields)) {
				return $this->response_body;
			} else
				return false;
		}
	}
	
}

?>