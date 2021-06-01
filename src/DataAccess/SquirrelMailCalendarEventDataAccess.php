<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class SquirrelMailCalendarEventDataAccess extends AbstractDataAccess
{
    public function getAll($accountId = null)
    {
        // Import the SQMail code for working with calendars and calendar events
        require_once(__DIR__ . '/../../../calendar/functions.php');
        require_once(__DIR__ . '/../../../calendar/backend_functions.php');

        $calendarIds = [];
        $calendars = get_all_owned_calendars($accountId);

        // Take the IDs of all calendars of the user, since we need them for reading all of the user's events
        foreach ($calendars as $c) {
            if (strcmp($c->owners->value, $accountId) === 0) {
                array_push($calendarIds, $c->id->value);
            }
        }

        $events = [];

        // Get all events for the given user from all their calendars
        foreach ($calendarIds as $cId) {
            $events = array_merge($events, get_all_events($cId, $accountId));
        }

        return $events;
    }

    public function write()
    {
        // TODO: Implement me
    }

    // TODO support multiple filter conditions like in the standard
    public function query($accountId, $filter = null)
    {
        // TODO: Implement me in case you want to be fancy
    }
}
