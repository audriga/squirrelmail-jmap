<?php

namespace OpenXPort\DataAccess;

class SquirrelMailCalendarEventDataAccessMock extends SquirrelMailCalendarEventDataAccess
{
    protected function init()
    {
        require_once(__DIR__ . '/mock_functions.php');
    }

    public function login($accountId)
    {
        $this->accountId = $accountId;
    }

    public function getAll($accountId = null)
    {
        $this->init();

        $calendarIds = [];
        // Take mock calendars in order to be able to read mock eventsWithMetaData below
        $calendars = get_all_owned_calendars_mock($this->accountId);

        // Take the IDs of all calendars of the user, since we need them for reading all of the user's eventsWithMetaData
        foreach ($calendars as $c) {
            if (strcmp($c->owners->value, $this->accountId) === 0) {
                array_push($calendarIds, $c->id->value);
            }
        }

        $eventsWithMetaData = [];

        // Get all mock eventsWithMetaData
        foreach ($calendarIds as $cId) {
            $mockEvents = get_all_events_mock($cId, $this->accountId);
            foreach ($mockEvents as $eventId => $event) {
                $mockEventMetaData = readEventMeta($cId, $eventId);
                $eventsWithMetaData[] = array("event" => $event, "eventMetaData" => $mockEventMetaData);
            }
        }

        return $eventsWithMetaData;
    }
}
