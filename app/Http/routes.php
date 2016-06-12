<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
use App\Models\DomainAvailability;

$app->get('/', function () use ($app) {
    return view('index');
});
$app->post('check', [
    'as' => 'check', 'uses' => 'DomainAvailabilityChecker@check'
]);