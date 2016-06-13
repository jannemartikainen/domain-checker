<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class DomainAvailability extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domains'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    protected static $whois_servers = [
        'fi' => [ 
            'server'        => 'whois.ficora.fi',
            'response'      => 'Domain not'
        ],
        'com' => [ 
            'server'        => 'whois.crsnic.net',
            'response'      => 'No match for'
        ],
        'net' => [ 
            'server'        => 'whois.crsnic.net',
            'response'      => 'No match for'
        ],
        'org' => [ 
            'server'        => 'whois.publicinterestregistry.net',
            'response'      => 'NOT FOUND'
        ]
    ];

    /**
     * Check availability of list of domains
     * @param array $domains
     */
    public static function checkAvailability(array $domains) {
        
        //First, let's clean the return array
        if(is_array($domains)) {
            foreach($domains as $i => $domain) {
                
                $tld = mb_strtolower(trim($domain));

                $parts = explode(".", $domain);
                if(count($parts) == 1) {
                    $domain_name = trim($parts[0]);
                    $tld = 'fi';
                }
                elseif(count($parts) == 2) {
                    $domain_name = trim($parts[0]);
                    $tld = trim($parts[1]);
                }
                elseif(count($parts) == 3) {
                    $domain_name = trim($parts[1]);
                    $tld = trim($parts[1]);
                }  

                $domains[$i] = DomainAvailability::performCheck($domain_name, $tld);

                usleep(1000000);   //Sleep for a second
            }
        }

        return $domains;
    }

    public static function performCheck($domainName = null, $tld = 'fi') {
        
        $ret = [
            'name'      => $domainName.'.'.$tld,
            'status'    => false,
            'whois'     => ''
        ];

        if($domainName && $tld) {
            $fp = fsockopen(self::$whois_servers[$tld]['server'], 43, $errstr, $errno, 10);
            
            fputs($fp, $domainName.'.'.$tld."\r\n");
            
            $rowCount = 0;  //Dummy failsafe
            $text = '';
            while(!feof($fp) || $rowCount <= 100)
            {
                $text .= fgets($fp, 4096);    
                $rowCount++;
            }

            $ret['whois'] = $text;
            
            //Täydennetään server-arrayta
            if(preg_match("/".self::$whois_servers[$tld]['response']."/",$text, $matches)){
                $ret['status'] = true;
            }
        }

        return $ret;
    }
}
