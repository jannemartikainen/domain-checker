<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DomainAvailability;

class DomainAvailabilityChecker extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     *
     */
    public function check(Request $request) {

        $original = preg_split ('/$\R?^/m', $request->input('domains'));
        $domains_fi = array();
        $domains_other = array();

        //erotellaan fi ja kansainväliset domaint toisistaan
        foreach($original as $i => $d) {
            if(strstr($d, '.fi'))
                $domains_fi[] = $d;
            elseif( strstr($d, '.com') || strstr($d, '.net') || strstr($d, '.org') )
                $domains_other[] = $d;
        }

        $template = [
            'domains_fi' => DomainAvailability::checkAvailability($domains_fi),
            'domains_other' => DomainAvailability::checkAvailability($domains_other)
        ];

        return view( 'result', $template );
    }
}
