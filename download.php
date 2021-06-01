<?php

// TODO To be more JMAP compliant we should
// * return "problem details" object on HTTP error
// * display URL in session object

// Parse URL vars
$url_vars = array();
parse_str($_SERVER['QUERY_STRING'], $url_vars);

// Reuse auth from webmailer
require_once __DIR__ . '/mailer.php';

// TODO split squirrelmail code from OpenXPort and reuse auth from squirrelmail
/*function to set your files*/
set_time_limit(0); //TODO might be a good idea to choose a very large value instead

/*output must be folder/yourfile*/
$accessor = new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccess();

$accessor->download($url_vars['accountId'], $url_vars['name'], $url_vars['blobId'], $url_vars['accept']);

/*back to jmap.php while downloading*/
header('Location:jmap.php');
