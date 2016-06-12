<?php

/**
 * @Created 18.9.2010
 * @author Janne Martikainen
 * @copyright Janne Martikainen
 */
@include_once CLASS_APP_PATH.'jokercom_connect.class.php';
class JokerComNameserver extends JokerComConnect
{
	var $type = 'host';		//Luokan tyyppi
	
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Listataan kaikki nimipalvelimet
	 * @param void
	 * @return string
	 */
	function listAll() {
		if($this->execute_request('query-ns-list')) {
			return $this->response_body;
		}
	}
	
	/**
	 * Luodaan uusi nimipalvelin.
	 * @param void
	 * @return string
	 */
	function create($nameserver_name, $ipv4, $ipv6) {
	
		$fields = array(        
            "host"  => $this->format_fqdn($nameserver_name, "ascii"),
            "ip"    => $ipv4,
            "ipv6"    => $ipv6,
		);
		
		if($this->execute_request('ns-create',$fields)) {
			return $this->response_body;
		}
	}
}

?>