<?php

namespace SquirrelMail\Util;

class SquirrelMailUtil
{
    /*
     * Modified version of SQMail's functions/i18n.php set_my_charset() that does not alter `$default_charset` and returns it instead.
     * Called only upon error
     *
     * @return string Charset
     */
    public static function getMyCharset()
    {
        global $data_dir, $username, $languages, $squirrelmail_language;

        $my_language = getPref($data_dir, $username, 'language');
        if (!$my_language) {
            $my_language = $squirrelmail_language ;
        }
        // Catch removed translation
        if (!isset($languages[$my_language])) {
            $my_language="en_US";
        }
        while (isset($languages[$my_language]['ALIAS'])) {
            $my_language = $languages[$my_language]['ALIAS'];
        }
        $encoding = $languages[$my_language]['CHARSET'];

        $logger = \OpenXPort\Util\Logger::getInstance();

        $logger->info("Encoding set to " . print_r($encoding, true) . " due to Language " . $my_language . ".");

        return array( "encoding" => $encoding);
    }
}
