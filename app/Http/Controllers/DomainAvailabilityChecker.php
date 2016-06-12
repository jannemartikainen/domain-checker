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

        $ret = preg_split ('/$\R?^/m', $request->input('domains'));

        $template = [
            'domains' => DomainAvailability::checkAvailability($ret)
        ];

        return view( 'result', $template );
    }
}
