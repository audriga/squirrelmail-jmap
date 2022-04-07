<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;
use OpenXPort\Util\AdapterUtil;

// TODO Sharing not implemented. We currently ignore accountId from call.
class SquirrelMailCalendarEventDataAccess extends AbstractDataAccess
{
    protected $accountId;

    /* Initialize Data Accessor with userId*/
    protected function init()
    {
        require_once(__DIR__ . '/../../../../functions/global.php');

        sqGetGlobalVar('username', $this->accountId);

        // Import the SQMail code for working with calendars and calendar events
        require_once(__DIR__ . '/../../../calendar/functions.php');
        require_once(__DIR__ . '/../../../calendar/backend_functions.php');
    }

    public function getAll($accountId = null)
    {
        $this->init();

        $calendarIds = [];
        $calendars = get_all_owned_calendars($this->accountId);

        // Take the IDs of all calendars of the user, since we need them for reading all of the user's events
        foreach ($calendars as $c) {
            if (strcmp($c->owners->value, $this->accountId) === 0) {
                array_push($calendarIds, $c->id->value);
            }
        }

        $events = [];

        // Get all events for the given user from all their calendars
        // and if we're able to read calendar events meta data => then also
        // read the corresponding meta data for each event (containing reminders and attendees for the given event)
        // and return an array, containing both the events and their corresponding meta data
        foreach ($calendarIds as $cId) {
            $eventsWithoutMetaData = get_all_events($cId, $this->accountId);

            foreach ($eventsWithoutMetaData as $eventId => $event) {
                // Check if the function 'readEventMeta' for reading calendar event meta data from ProMail exists
                // and if yes, then return events alongside with their corresponding meta data
                if (function_exists('readEventMeta')) {
                    $eventMetaData = readEventMeta($cId, $eventId);
                    $events[] = array("event" => $event, "eventMetaData" => $eventMetaData);    
                } else { // Otherwise, return only events and no meta data
                    $events[] = array("event" => $event, "eventMetaData" => []);
                }
            }
        }

        return $events;
    }

    public function get($ids, $accountId = null)
    {
        throw new BadMethodCallException("Get via CalendarEvent/get not implemented");
    }

    public function create($eventsToCreate, $accountId = null)
    {
        throw new BadMethodCallException("Create via CalendarEvent/set not implemented");
    }

    public function destroy($ids, $accountId = null)
    {
        throw new BadMethodCallException("Destroy via CalendarEvent/set not implemented");
    }

    // TODO support multiple filter conditions like in the standard
    public function query($accountId, $filter = null)
    {
        throw new BadMethodCallException("Query via CalendarEvent/set not implemented");
    }
}
