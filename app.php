<?php
/* Bootstrap */
require_once __DIR__.'/silex.phar';
$app = new Silex\Application();

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
/*************/

// Config
$app['debug'] = true;
$app['bbb.host'] = "";
//$app['bbb.username'] = "bigbluebutton";
$app['bbb.securitySalt'] = "";

// BBB function
require_once __DIR__.'/bbbClient.php';      // TODO : use autoloader
$app['bbb'] = function ($app) {
    return new bbbClient($app['bbb.host'],$app['bbb.securitySalt']);
};

$app->get('/', function() use($app) {
    return 'Home';
});

$app->error(function (\Exception $e, $code) use($app) {
    //return new Response('HERE:We are sorry, but something went terribly wrong.', $code);
    if ($app['debug']) {
        return;
    }
    return new Response('<response>
        <returncode>FAILED</returncode>
        <messageKey>invalidMeetingIdentifier</messageKey>
        <message>
            The meeting ID that you supplied did not match any existing meetings
        </message>
    </response>',200);
     
});
/*
$app->get('/{username}/api/{function}', function (Request $request) use($app) {
    $message = $request->get('message');
    
 */
$app->get('/{username}/api/{function}', function(Request $request, $function, $username) use($app) {
    $response = new Response();

    $req = explode('&',$request->getQueryString());
    $params = array();
    foreach($req as $key => $elem) {
        $elem = explode("=",$elem);
        if(count($elem) > 1)
            $params[$elem[0]] = $elem[1];
    }
    //if(array_key_exists("checksum", $params))

    // is query is valid
    if($app['bbb']->isValidQuery($function, $params)) {
        if(array_key_exists('checksum', $params)) {
            unset($params['checksum']);
        }
        $response->setContent($app['bbb']->query($function,$params));
    } else {
        $response->setContent('<response>
<returncode>FAILED</returncode>
<messageKey>Estheban - checksumError</messageKey>
<message>You did not pass the checksum security check</message>
</response>');
    }

    // remove checksum from params
    if(array_key_exists("checksum", $params)) {
        unset($params['checksum']);
    }
    /*
    foreach($request->attributes as $key=>$att) {
        echo $key." = ".$att."\n";
    }*/
    
    
    //$response->setContent();
    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'text/xml');

    return $response;
});

$app->run();

