<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

$auth = Zend_Auth::getInstance();

// phpunit trips up here without the cli test:
if( php_sapi_name() !== 'cli' ) {

    // this if equates to: if the user is logged into Zend but not Laravel:
    if( $auth->hasIdentity() && \Auth::guest() ) {
        // log the user is for Laravel5
        // Note that we reload the user from the database as Zend uses a session cache
        // which breaks associations, etc.
        \Auth::login( d2r('User')->find( ($auth->getIdentity()['user'])->getId() ) );
    } else if( !$auth->hasIdentity() ) {
        \Auth::logout();
    }
}

if( Auth::check() && Auth::user()->isSuperUser() ) {
    // get an array of customer id => names
    if( !( $customers = Cache::get( 'admin_home_customers' ) ) ) {
        $customers = d2r( 'Customer' )->getNames( true );
        Cache::put( 'admin_home_customers', $customers, 3600 );
    }

    app()->make('Foil\Engine')->useData(['customers' => $customers]);
}


Route::group( [ 'namespace' => 'PatchPanel' ], function() {
    Route::get( 'verify-loa/{id}/{code}',       'PatchPanelPortController@verifyLoa' );
});

Route::group( [ 'namespace' => 'PatchPanel'], function() {
    Route::get( 'verify-loa/{id}/{code}',       'PatchPanelPortController@verifyLoa' );
});

Route::group( [ 'namespace' => 'PatchPanel', 'prefix' => 'patch-panel-port' ], function() {
    Route::get( 'view/{id}',                    'PatchPanelPortController@view' );
    Route::get( 'loa-pdf/{id}',                 'PatchPanelPortController@loaPDF' );
});

Route::group( [ 'prefix' => 'rs-prefixes', 'middleware' => [ 'rs-prefixes' ] ], function() {
    Route::get(     'list',         'RsPrefixesController@list' )->name( 'rs-prefixes@list'  );
    Route::get(     'view/{cid}',   'RsPrefixesController@view' )->name( 'rs-prefixes@view'  );
});

Route::get( 'weather-map/{id}',                    'WeatherMapController@index' )->name( 'weathermap');


/////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////
///
/// Static content
///
/// See: http://docs.ixpmanager.org/features/static-content/
///
///
Route::get( 'content/{priv}/{page}',     'ContentController@index' )->name( 'content' );
Route::get( 'public-content/{page}',     'ContentController@public' )->name( 'public-content' );

Route::get( 'content/members/{priv}/{page}', 'ContentController@members' )->name( 'content/members' );

/////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////
///
/// Statistics -> a dedicated request object manages authorization
///

Route::group( [ 'prefix' => 'statistics' ], function() {
    Route::get(  'ixp/{category?}',                       'StatisticsController@ixp'               )->name( 'statistics/ixp'            );
    Route::get(  'infrastructure/{graphid?}/{category?}', 'StatisticsController@infrastructure'    )->name( 'statistics/infrastructure' );
    Route::get(  'switch/{switchid?}/{category?}',        'StatisticsController@switch'            )->name( 'statistics/switch'         );
    Route::get(  'trunk/{trunkid?}/{category?}',          'StatisticsController@trunk'             )->name( 'statistics/trunk'          );
});


/////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////
///
/// DEFAULT ROUTE
///

Route::get( '/', function() {

    if( Auth::guest() ) {
        return redirect('auth/login' );
    }

    if( Auth::user()->isSuperUser() ) {
        return redirect( 'admin' );
    } else if( Auth::user()->isCustAdmin() ) {
        return redirect( 'contact/list' );
    } else {
        return redirect( 'dashboard/index' );
    }
})->name( 'default' );

/////////////////////////////////////////////////////////////////////////////////////////////////



