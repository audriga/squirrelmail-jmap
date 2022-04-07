<?php

# taken from plugins/calendar/functions.php
function foldICalStreamByRef(&$text, $maxLineLength = 75, $foldDelimiter = '')
{
    if (empty($foldDelimiter)) {
        $foldDelimiter = ICAL_LINE_DELIM . ' ';
    }
    $newText = '';
    while (strlen($text) > 0) {
        $EOL = strpos($text, ICAL_LINE_DELIM);

        if ($EOL <= $maxLineLength) {
            $cutoff = $EOL + strlen(ICAL_LINE_DELIM);
        } else {
            $cutoff = $maxLineLength;
        }

        $newText .= substr($text, 0, $cutoff);
        $text = substr($text, $cutoff);

        if ($EOL > $maxLineLength) {
            $newText .= $foldDelimiter;
        }
    }
    $text = $newText;
}

# Redeclare Nutmail's readEventMeta from require_once(__DIR__ . '/../../../../calendar/functions.php');
function readEventMeta($calId, $eventId)
{
    $path = __DIR__ . '/../../resources/calendars/mock-meta-data-' . $eventId . '.meta';
    if (file_exists($path)) {
        $body = file_get_contents($path);
        return unserialize($body);
    }
    return array();
}

function get_all_owned_calendars_mock($userId)
{
    $path = __DIR__ . '/../../resources/calendars/mock-calendar.meta';
    if (file_exists($path)) {
        $body = file_get_contents($path);
        return unserialize($body);
    }
    return array();
}

function get_all_events_mock($calendarId, $userId)
{
    $path = __DIR__ . '/../../resources/calendars/mock-calendar-event.meta';
    if (file_exists($path)) {
        $body = file_get_contents($path);
        return unserialize($body);
    }
    return array();
}

# Taken from plugins/todo/functions.php
# Hard coded the sole todo file
function todo_init(&$todos)
{
    $path = __DIR__ . '/../../resources/tasks/protest9@oxpromailtesting.com.todo';
    $todo_filesize = @filesize($path);
    $file = @fopen($path,'r');
    $todos = @fread($file ,$todo_filesize);
    @fclose($file);
}

# Taken from OpenXPort Server.php because its a private function:
function serializeAsJson($content)
{
    $json_response = json_encode($content, JSON_UNESCAPED_SLASHES);

    if ($json_response) {
        return $json_response;
    }

    $error = json_last_error();

    switch ($error) {
        case JSON_ERROR_NONE:
            break;
        case JSON_ERROR_DEPTH:
            $msg = 'Error during JSON encoding - Maximum stack depth exceeded';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $msg = 'Error during JSON encoding - Underflow or the modes mismatch';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $msg = 'Error during JSON encoding - Unexpected control character found';
            break;
        case JSON_ERROR_SYNTAX:
            $msg = 'Error during JSON encoding - Syntax error, malformed JSON';
            break;
        case JSON_ERROR_UTF8:
            $msg = 'Error during JSON encoding - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
        default:
            $msg = 'Error during JSON encoding - Unknown error';
            break;
    }

    $this->logger->warning($msg);

    \OpenXPort\Util\AdapterUtil::executeEncodingCallback();
    array_walk($content['methodResponses'][0][1]["list"], array('\OpenXPort\Util\AdapterUtil', 'sanitizeJson'));

    return json_encode($content, JSON_UNESCAPED_SLASHES);
}
