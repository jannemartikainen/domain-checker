<?php

/**
 * 
 * @author Janne Martikainen
 * @copyright Janne Martikainen
 */

class JokerComConnect
{
	/* Tärkeitä arvoja rajapintaan yhdistämistä varten */
	private $dmapi_url = "https://dmapi.ote.joker.com";
	private $dmapi_session;
	private $dmapi_username;
	private $dmapi_password;
	private $available_requests = array();
	
	/* CURL -muuttujia */
	private $curlopt_connecttimeout = 5;
	private $curlopt_timeout = 60;
	
	/* Session -muuttujat */
	private $dmapi_auth_sid;
	private $dmapi_uid;
	private $account_balance;
	private $tracking_id;
	
	private $setting;		//Asetus -olio, jolla voidaan hakea tietokantaan asetettuja asetuksia
	
	var $response_header;
	var $response_body;
	
	var $session;
	private $log;
	
    /**
     * @return  void
     */
    function __construct()
    {
		$this->session = new Session;
		$this->setting = new Setting;
		
		$this->log = new DomainLog('joker');
		
		//Haetaan tunnistautumistiedot
		$this->dmapi_username = $this->setting->getVar('domain/joker/username');
		$this->dmapi_password = $this->setting->getVar('joker/password');
		
		//Kirjaudutaan rajapintaan
		if(!$this->getAuthSid()) {
			$this->login($this->dmapi_username, $this->dmapi_password);
		}
		
    }
	
	
	
	function getAuthSid() {
		if($this->dmapi_auth_sid)
			return $this->dmapi_auth_sid;
		elseif($this->session->getVar('jokercom/auth-sid')) {
			$this->setAuthSid($this->session->getVar('jokercom/auth-sid'));
			return $this->session->getVar('jokercom/auth-sid');
		}
	}
	function setAuthSid($_value) {
	
		if(isset($_value)) {
			$this->dmapi_auth_sid = $_value;
			$this->session->setVar('jokercom/auth-sid', $_value);
		}
	}
	
	/**
	 * Palautetaan dataan sessioon liittyen
	 */
	function getSessionVariables() {
		if($this->session->getVar('jokercom/login-response'))
			return $this->session->getVar('jokercom/login-response');
	}
	
	
	/**
	 * Kirjaudutaan sisään rajapintaan
	 * @param username
	 * @param password
	 * @return boolean
	*/
	function login($_username = "", $_password = "") {
		
		if(empty($_username))
			$_username = $this->dmapi_username;
		
		if(empty($_password))
			$_password = $this->dmapi_password;
		
		$params = array(
			'username' => $_username,
			'password' => $_password
		);
		
		//Sisäänkirjautumisen yhteydessä tallennetaan tarvittavat tiedot tulevaa käsittelyä varten
		if($this->execute_request('login',$params)) {
			
			$this->setAuthSid($this->response_header['auth-sid']);
			$this->session->setVar('jokercom/login-response', $this->response);
			return true;
		} else
			return false;
		
	}
	
	function logOut() {
		$this->execute_request('logout');
	}
	
	function getVersion() {
		if($this->execute_request('version'))
			return $this->response_body;
	}
	
	/**
	 * Haetaan jälleenmyyjän käyttäjäprofiili
	 */
	function getProfile() {
		if($this->execute_request('query-profile'))
			return $this->response_body;
	}
	
	/**
	 * Hakee tietoa domainista, kontaktista tai hostista
	 * @param string $_value 
	*/
	function whoIs($_value) {
		if($this->type && $_value) {
			$params = array(
				$this->type => $_value
			);

			if($this->execute_request('query-whois',$params)) {
				return $this->response_body;
			} else
				print_r($this->response_header);
		} else
			return false;
	}
	
	/**
	 * Hakee tietoa joker.comiin tehdyistä kyselyistä
	*/
	function resultList() {
		if($this->execute_request('result-list',$params)) {
				return $this->response_body;
			} else
				print_r($this->response_header);
	}
	
	/**
	 * Haetaan requestin tietoja annetun id-numeron perusteella
	*/
	function retrieveResult($proc_id = false, $svtrid = false) {
		$params = array();
		if($proc_id) {
			$params['Proc-ID'] = $proc_id;
		
		if($svtrid)
			$params['SvTrID'] = $svtrid;
		
		}
		
		if(count($params) == 0)
			$params = false;
		
		if($this->execute_request('result-retrieve',$params)) {
				return $this->response_body;
			} else
				print_r($this->response_header);
	}
	
	/**
     * Muodostaa yhteyden palvelimeen CURL-kirjaston avulla
     *
     * @param   string  $conn_server palvelu, johon yhdistetään
     * @param   string  $request varsinainen request
     * @param   boolean $get_header haetaanko headerit
     * @access  public 
     * @return  string
     * @see     execute_request()
     */
    function query_host($conn_server, $http_query = "", $get_header = false)
    {
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $conn_server.$http_query);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		if($this->curlopt_connecttimeout)
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->curlopt_connecttimeout);
		
		if($this->curlopt_timeout)
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->curlopt_timeout); 
		
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
        if ($get_header)
            curl_setopt($ch, CURLOPT_HEADER, 1);
        else
            curl_setopt($ch, CURLOPT_HEADER, 0);
		
		//Ajetaan request
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            die("Yhteys epäonnistui!");
        } else {
            curl_close($ch);
        }
		
        return $result;
    }
	
	
	/**
	 * Valmistelee DMAPI-requestin, lähettää sen sekä tarkastaa rajapinnasta saadun vastauksen
     * HTTP-vastaus parsitaan ja tallennetaan myöhempää käsittelyä varten
     *
     * @param   string  $request 
     * @param   array   $params pyynnön tarvitsemat parametrit
     * @return  boolean
	 *
     */
    function execute_request($request, $params)
    {
		//Tarkastetaan, että request on sallittu ajaa rajapintaan
        if ($this->is_request_available($request)) {
			//print_r($params); die();
            //Rakennetaan request
            $this->assemble_query($request, $params);
			
            //Muodostetaan yhteys palvelimelle ja lähetetään request
            $raw_response = $this->query_host($this->dmapi_url, $this->http_query, true);
            $temp_array = @explode("\r\n\r\n", $raw_response, 2);
			
			//Jaetaan request head -sekä body osaan
            if (is_array($temp_array) && 2 == count($temp_array)) {
                $response = $this->parse_response($temp_array[1]);
                $response["http_header"] = $temp_array[0];
               
            } else {
                return false;
            }
			
			$this->response_header = $response['response_header'];
			$this->response_body = $response['response_body'];
			
			//print_r($response); die();
			
            //Tarkastetaan rajapinnasta tullut status
            if ($this->http_srv_response($response["http_header"]) && $this->request_status($response)) {
				$this->log->create($response['http_header']['tracking-id'],'success', 'Pyynnön suoritus onnistui!', $raw_response, $this->http_query);
                return true;
            } else {
				
				//Kirjoitetaan lokia epäonnistuneesta pyynnöstä
				$this->log->create($response['response_header']['tracking-id'],'error', implode("|",$response['response_header']['error']), $raw_response, $this->http_query);
				
                $http_code = $this->get_http_code($response["http_header"]);
                //Mikäli http-koodi oli 401, eli ei ole oikeuksia, kirjataan käyttäjä uudestaan sisään (sessioon on voinut jäädä vanhentunut auth-sid)
				if ($http_code == "401" || $http_code == "406") {
					$this->login();
                }
            }
        }
        return false;
    }
	
	
    /**
     * Parsitaan rajapinnasta saatu vastaus (ainoastaan viestin runko-osa, eli body)
     *
     * Onnistuessaan palautetaan assosiatiivinen array viestin palautusdatasta
     * Epäonnistuessa palautetaan tyhjä merkkijono
     *
     * @param   string  $res viestin runko
     * @access  private
     * @return  mixed
     * @see     execute_request()
     */
    function parse_response($res)
    {
        $raw_arr = explode("\n\n", trim($res));
        $arr_elements = count($raw_arr);
        if ($arr_elements > 0) {
            if (is_array($raw_arr) && 1 == count($raw_arr)) {
                $temp["response_header"] = $this->parse_response_header($raw_arr["0"]);

            } elseif (is_array($raw_arr) && 2 == count($raw_arr)) {
                $temp["response_header"] = $this->parse_response_header($raw_arr["0"]);
                $temp["response_body"] = $raw_arr["1"];
            } else {
                $temp["response_header"] = $this->parse_response_header($raw_arr["0"]);
                $felem = array_shift($raw_arr);
                $temp["response_body"] = implode("\n\n",$raw_arr);
            }
        } else {
            $this->log->req_status("e", "function parse_response(): Couldn't split the response into response header and response body\nRaw result:\n$res");
            $temp = "";
        }
        return $temp;
    }

    /**
     * Paritaan rajapinnasta saadun vastauksen head-osa
     *
     * Onnistuessaan palautetaan assosiatiivinen array viestin palautusdatasta
     * Epäonnistuessa palautetaan tyhjä merkkijono
     *
     * @param   string  viestin head-osa
     * @access  private
     * @return  mixed
     * @see     execute_request()
     */
    function parse_response_header($header)
    {
        $raw_arr = explode("\n", trim($header));
        $result = array();
        if (is_array($raw_arr)) {
            foreach ($raw_arr as $key => $value)
            {
                $keyval = array();
                if (preg_match("/^([^\s]+):\s+(.+)\s*$/", $value, $keyval)) {
                    $keyval[1] = strtolower($keyval[1]);
                    if (isset($arr[$keyval[1]])) {
                        if (!is_array($arr[$keyval[1]])) {
                            $prev = $arr[$keyval[1]];
                            $arr[$keyval[1]] = array();
                            $arr[$keyval[1]][] = $prev;
                            $arr[$keyval[1]][] = $keyval[2];
                        } else {
                            $arr[$keyval[1]][] = $keyval[2];
                        }
                    } else {
                        if ($keyval[2] != "") {
                            $arr[$keyval[1]] = $keyval[2];
                        } else {
                            $arr[$keyval[1]] = "";
                        }
                    }
                } else {
                    $this->log->req_status("e", "function parse_response_header(): Header line not parseable - pattern do not match\nRaw header:\n$value");
                    $this->log->debug($header);
                }
            }
        } else {
            $arr = "";
            $this->log->req_status("e", "function parse_response_header(): Unidentified error\nRaw header:\n$header");
        }
        return $arr;
    }

    /**
	 * Tarkastus siitä mitkä komennot sallitaan ajaa rajapinnasta
	 * @return boolean
	 */
    function is_request_available($request)
    {
		if(isset($request)) {
			if ($request == "login" || $request == "query-request-list") {
				return true;
			}
			foreach ($this->available_requests as $item) {
				if ($request == $item) {
					return true;
				}
			}
			return true;
			//return false;
		} else
			return false;
    }
    
    

    /**
     * Poimitaan head-osasta HTTP-pyynnön koodi
     *
     * @param   string  $http_header
     * @access  public
     * @return  string
     */
    function get_http_code($http_header)
    {
        $regex = "/^HTTP\/1.[0-1]\b ([0-9]{3}) /i";
        preg_match($regex, $http_header, $matches);
        if (is_array($matches) && $matches[1]) {
            return $matches[1];
        } else {
            return false;
        }
    }

    /**
     * Tarkastetaan oliko HTTP-kutsu onnistunut 
     *
     * @param   string  $http_header
     * @access  public
     * @return  boolean
     * @see     execute_request()
     */
    function http_srv_response($http_header)
    {
        $success = false;
        $http_code = $this->get_http_code($http_header);
        switch (substr($http_code,0,1))
        {
            case "2":
                $success = true;
                break;
            default:
                break;
        }
        return $success;
    }

    /**
     * Tarkastetaan oliko varsinainen pyyntö rajapintaan onnistunut
     *
     * @param   string  $http_header
     * @access  public
     * @return  boolean
     * @see     execute_request()
     */
    function request_status($sessdata)
    {
        if (!isset($sessdata["response_header"]["status-code"]) || $sessdata["response_header"]["status-code"] != "0") {
            return false;
        }
        return true;
    }
	

    /**
     * Valmistellaan rajapintaan ajettava request
     *
     * @param   string $request DMAPI -request
     * @param   array $params parametrit ja arvot
     */
    function assemble_query($request, $params)
    {
		$http_query_temp = "";
		if($request) {
			$http_query = "/request/".$request;
			if(is_array($params)) {
				
				foreach ($params as $key => $value) {
					if(isset($key) && isset($value)) {
						$http_query_temp .= strtolower(trim($key)).'='.strtolower(trim($value)).'&';
					}
				}
				
				//Mikäli auth-sid on asetettu, liitetään se parametrien perään. Muussa tapauksessa otetaan viimeinen & pois
				if($this->getAuthSid()) {
					$http_query_temp .= "&auth-sid=".$this->getAuthSid();
				} else
					$http_query_temp = substr($http_query_temp,0,strlen($http_query_temp)-1);
				
				$http_query = $http_query."?".$http_query_temp;
			
			//If there is no parameters and auth-sid is set, let's get auth-sid in request
			} else {
				if($this->getAuthSid()) {
					//$http_query .= "?dmapi-auth=".$this->getAuthSid();
					$http_query .= "?auth-sid=".$this->getAuthSid();
				}
			}
			
			$this->http_query = $http_query;
			
			return $http_query;
		} else
			return false;
    }
	
}

?>