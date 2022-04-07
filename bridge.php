<?php
/** START OF COPY OF src/redirect.php (Squirrelmail 1.4.22) **/

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/strings.php');
require_once(SM_PATH . 'functions/prefs.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/constants.php');

// OpenXPort: We require this on IMAP login error
require_once(SM_PATH . 'functions/i18n.php');

// OpenXPort: Set user and password. Here 'secretkey' denotes password
if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    list($login_username, $secretkey) = explode(':', base64_decode(substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)));
} else {
    $login_username = $_SERVER['PHP_AUTH_USER'];
    $secretkey = $_SERVER['PHP_AUTH_PW'];
}
$users = null;

// OpenXPort register shutdown fuction because SQMail tends to just exit on 401
function shutdown()
{
    if (!sqsession_is_registered('username')) {
        http_response_code(401);
        die('401 Unauthorized');
    }
}
register_shutdown_function('shutdown');

// OpenXPort: Handle admin auth if any
// If user contains * explode it
if (mb_strpos($login_username, '*')) {
    $users = explode("*", $login_username);

    // Use fist part for IMAP login
    $login_username = $users[0];
}

sqsession_is_active();

sqsession_unregister ('user_is_logged_in');
sqsession_register ($base_uri, 'base_uri');

sqsession_register($login_username, 'login_username');
sqsession_register($secretkey, 'secretkey');

/* get globals we might need */
sqGetGlobalVar('login_username', $login_username);
sqGetGlobalVar('secretkey', $secretkey);
// OpenXPort: removed some code
/* end of get globals */

// OpenXPort: removed some code

if (!sqsession_is_registered('user_is_logged_in')) {
    do_hook ('login_before');

    /**
     * Regenerate session id to make sure that authenticated session uses
     * different ID than one used before user authenticated.  This is a
     * countermeasure against session fixation attacks.
     * NB: session_regenerate_id() was added in PHP 4.3.2 (and new session
     *     cookie is only sent out in this call as of PHP 4.3.3), but PHP 4
     *     is not vulnerable to session fixation problems in SquirrelMail
     *     because it prioritizes $base_uri subdirectory cookies differently
     *     than PHP 5, which is otherwise vulnerable.  If we really want to,
     *     we could define our own session_regenerate_id() when one does not
     *     exist, but there seems to be no reason to do so.
     */
    if (function_exists('session_regenerate_id')) {
        session_regenerate_id();

        // re-send session cookie so we get the right parameters on it
        // (such as HTTPOnly, if necessary - PHP doesn't do this itself
        sqsetcookie(session_name(),session_id(),false,$base_uri);
    }

    $onetimepad = OneTimePadCreate(strlen($secretkey));
    $key = OneTimePadEncrypt($secretkey, $onetimepad);
    sqsession_register($onetimepad, 'onetimepad');

    /* remove redundant spaces */
    $login_username = trim($login_username);

    /* Verify that username and password are correct. */
    if ($force_username_lowercase) {
        $login_username = strtolower($login_username);
    }

    $imapConnection = sqimap_login($login_username, $key, $imapServerAddress, $imapPort, 1); // OpenXPort: Use $hide to disable error messages

    $sqimap_capabilities = sqimap_capability($imapConnection);
    sqsession_register($sqimap_capabilities, 'sqimap_capabilities');
    $delimiter = sqimap_get_delimiter ($imapConnection);

    sqimap_logout($imapConnection);
    sqsession_register($delimiter, 'delimiter');

    $username = $login_username;
    sqsession_register ($username, 'username');
    sqsetcookie('key', $key, 0, $base_uri);
}

// OpenXPort: removed some code

/* Set the login variables. */
$user_is_logged_in = true;
$just_logged_in = true;

/* And register with them with the session. */
sqsession_register ($user_is_logged_in, 'user_is_logged_in');
sqsession_register ($just_logged_in, 'just_logged_in');

/* Write session data and send them off to the appropriate page. */
session_write_close();

// OpenXPort: If admin authed successfully, use second part for SQMail
if ($users) {
    if (!in_array($users[0], \Squirrel\Config::$adminUsers)) {
        // TODO put into library
        http_response_code(403);
        die('403 Forbidden');
    }
    $username = $users[1];
    sqsession_register($username, 'username');
}
