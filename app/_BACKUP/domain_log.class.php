<?php

/**
 * @Created 18.9.2010
 * @author Janne Martikainen
 * @copyright Janne Martikainen
 */

class DomainLog
{
	var $lLog;
	private $interface_name;
	
	function __construct($_interface = false) {
		$this->lLog = new Rowlist('dv_log');
		if($_interface)
			$this->interface_name = $_interface;
	}
	
	function create($_id="",$_status="unknown", $_error="", $_response="", $_request="") {
		
		$this->lLog->newRow();
		$this->lLog->row->status = $_status;
		$this->lLog->row->tracking_id = $_id;
		$this->lLog->row->interface_name = $this->interface_name;
		$this->lLog->row->error = $_error;
		$this->lLog->row->response = $_response;
		$this->lLog->row->request = $_request;
		$this->lLog->row->created = date('Y-m-d H:i:s');
		
		$this->lLog->saveRow();
	}
	
	/*
	 * Nהytetההn lokia
	 * @param limit integer
	 * @param show varchar (error|success|all|unknown)
	*/
	function show($limit=50,$show='all') {
		
		$aRet = array();
	
		if($show == 'all') {
			$this->lLog->first('id > 0','created DESC',intval($limit));
			return $this->lLog->fetchArray();
		} else {
			$this->lLog->first('error="'.$show.'"','created DESC',intval($limit));
			return $this->lLog->fetchArray();
		}
	}
	
}

?>