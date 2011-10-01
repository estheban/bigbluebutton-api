<?php
/* Bootstrap */
require_once __DIR__.'/silex.phar';
$app = new Silex\Application();

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
/*************/

// Config
$app['debug'] = true;
$app['config'] = parse_ini_file("config.ini", true);

// BBB function
// TODO : use autoloader
require_once __DIR__.'/lib/BbbQuery.class.php';
require_once __DIR__.'/lib/BbbServer.class.php';

$app['bbb'] = function ($app) {
    return new BbbServer($app['config']['server']['securitySalt'], $app['config']['server']['url']);
};

function getChecksum(BbbQuery $query, BbbServer $bbb) {
    return sha1($query->getFunction() . (string) $query . $bbb->getSecuritySalt());
};

function query(BbbServer $server, BbbQuery $query) {
    $queryStr = $query->getFunction()."?".(string) $query . "&checksum=".getChecksum($query, $server);
    try {
        $result =  @file_get_contents($server->getUrl().$queryStr);
    } catch(Exception $e) { }
    if(!$result) {
        $result = '<response>
        <returncode>FAILED</returncode>
        <messageKey></messageKey>
        <message></message>
    </response>';
    }

    return $result;
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

    $bbb = new BbbServer($app['config']['server']['securitySalt']);
    $query = new BbbQuery($function, $request->server->get('QUERY_STRING'));
    // can't use $request->getQueryString() because symfony normalise the queryString

    $checksum = getChecksum($query, $bbb);

    // Validate Query
    if($checksum == $query->getParameter("checksum")) {
        // valid
        switch($function) {
            case "join":
                // Quick fix, redirect to the original BigBlueButton API
                $queryStr = $query->getFunction()."?".(string) $query . "&checksum=".getChecksum($query, $app['bbb']);
                return $app->redirect($app['bbb']->getUrl().$queryStr);
                break;
            default:
                $result = query($app['bbb'], $query);
                break;
        }
        $response->setContent($result);
    } else {
        $response->setContent('<response>
<returncode>FAILED</returncode>
<messageKey>checksumError</messageKey>
<message>You did not pass the checksum security check</message>
</response>');
    }

    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'text/xml');
    return $response;
});

$app->run();

