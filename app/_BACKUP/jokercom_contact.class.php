<?php

/**
 * @Created 18.9.2010
 * @author Janne Martikainen
 * @copyright Janne Martikainen
 */
@include_once CLASS_APP_PATH.'jokercom_connect.class.php';
class JokerComContact extends JokerComConnect
{
	var $type = 'contact';		//Type of class, for connect class
	var $domain_name;
	
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Haetaan kontaktitietoja
	 * @param string pattern hakusana
	 * @param string @tld ylätason verkkotunnuksen nimi
	 */
	function view($pattern = false, $tld = false) {
		$fields = array();
		
		if($pattern)
			$fields['pattern'] = $pattern;
		
		if($tld)
			$fields['tld'] = $tld;
		
		if(count($fields) == 0)
			$fields = false;
		
		if($this->execute_request('query-contact-list',$fields)) {
			
			if(strlen($this->response_body) > 1)
				return explode('\n', $this->response_body);
			else
				return false;

		} else
			return false;
	}
	
	/**
	 * Luodaan kontakti
	 *	Field array:
	 *	tld   	 Target TLD where this contact is intended to be used.
	 *	name 	Full name (if empty, fname + lname will be used)
	 *	fname 	First name
	 *	lname 	Last name
	 *	title 	(opt)
	 *	individual 	(opt) Y, Yes, N, No
	 *	organization 	(opt if individual)
	 *	email 	mailaddress of the contact
	 *	address-1 	street address
	 *	address-2 	(opt)
	 *	address-3 	(opt)
	 *	city 	
	 *	state 	(opt)
	 *	postal-code 	
	 *	country 	ISO country code (2 letters)
	 *	phone 	
	 *	extension 	(opt)
	 *	fax 	(opt)
	 */
	function create($fields) {
		
		$fields = array(
        "tld"   => $fields->tld,
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
		
		if($this->execute_request('contact-create',$fields)) {
			return $this->response_body;
		} else
			return false;
	}
	
	/**
	 * Muokataan kontaktin tietoja
	 * @param string $handle kohtankti-kahva (eli tavallaan kontaktin id)
	 * @param array fields kontaktin eri yhteystiedot
	 */
	function modify($handle, $fields) {
		
		if($handle) {
			$fields = array(
			"handle"   => $handle,
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
			
		
			if($this->execute_request('contact-modify',$fields)) {
				return $this->response_body;
			} else
				return false;
		}
	}
	
	/**
	 * Poistetaan kontakti
	 * @param string $handle kohtankti-kahva (eli tavallaan kontaktin id)
	 */
	function delete($handle) {
		if($handle) {
			$fields = array(
			"handle"   => $handle
			);
		
			if($this->execute_request('contact-delete',$fields)) {
				return $this->response_body;
			} else
				return false;
		}
	}
	
	/**
	 * Palautetaan JokerCom -rajapinnan mukainen maatunnus
	 * Eli muutetaan vaan maan nimi, maakoodiin
	 */
	 function getLanguageCode($country) {
		
		if($country == 'Finland')
			return 'FI';
		if($country == 'England')
			return 'EN';
		if($country == 'Sweden')
			return 'SE';
		if($country == 'Russian')
			return 'RU';
		if($country == 'Germany')
			return 'DE';
		else
			return 'FI';
		
	 }
}

?>