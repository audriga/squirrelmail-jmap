<?php

namespace OpenXPort\Jmap\Calendar;

use PHPUnit\Framework\TestCase;

final class JmapCalendarEventMetaDataTest extends \PHPUnit_Framework_TestCase
{

    // Mapper, data accessor and adapter objects from OpenXPort that we're going to need throughout all tests
    private $mapper;
    private $accessor;
    private $adapter;

    private function init()
    {
        if (!defined('SM_PATH')) {
            define('SM_PATH', '../../');
        }

        // Include the mock functions (for reading mock data)
        require_once(__DIR__ . '/mock_functions.php');
        require_once(__DIR__ . '/mock_calendar_event_accessor.php');
        require_once(__DIR__ . '/../../../../calendar/constants.php');
        require_once(__DIR__ . '/../../../../calendar/backend_functions.php');
        require_once(__DIR__ . '/../../../../calendar/classes/calendar.php');
        require_once(__DIR__ . '/../../../../calendar/classes/event.php');
        require_once(__DIR__ . '/../../../../calendar/classes/property.php');

        // Set up the mapper, data accessor and adapter that we need for the tests
        $this->mapper = new \OpenXPort\Mapper\SquirrelMailCalendarEventMapper();
        $this->accessor = new \OpenXPort\DataAccess\SquirrelMailCalendarEventDataAccessMock();
        $this->adapter = new \OpenXPort\Adapter\SquirrelMailCalendarEventAdapter();

        $this->accessor->login("stanimir.bozhilov.1998@gmail.com");
    }

    public function testCanParseCalendarEventParticipantIds()
    {
        $this->init();

        $calendarEvents = $this->accessor->getAll();
        $list = $this->mapper->mapToJmap($calendarEvents, $this->adapter);

        // Since JMAP participants are a map of participant ID to a participant object, here we take
        // the participant IDs by using the PHP function array_keys()
        $participantIds = array_keys($list[0]->getParticipants());

        // Create an array of the participant IDs that we expect to have
        $expectedIds = array("51F1", "8RIv");

        // Use our helper function arraysAreEqual() to check if the expected and the actual participant IDs match
        $this->assertTrue($this->arraysAreEqual($participantIds, $expectedIds));
    }

    public function testCanParseCalendarEventParticipantEmail()
    {
        $this->init();

        $calendarEvents = $this->accessor->getAll();
        $list = $this->mapper->mapToJmap($calendarEvents, $this->adapter);

        $this->assertEquals("primaryemail@user1.com", $list[0]->getParticipants()['51F1']->getEmail());
        $this->assertEquals("user2@user2.com", $list[0]->getParticipants()['8RIv']->getEmail());
    }

    public function testCanParseCalendarEventParticipantStatus()
    {
        $this->init();

        $calendarEvents = $this->accessor->getAll();
        $list = $this->mapper->mapToJmap($calendarEvents, $this->adapter);

        // $list contains 2 events
        // The first event ($list[0]) has two participants -> one with participation status "needs-action" and the other
        // with participation status "accepted"
        // That's why here we check these 2 values of participation status from $list[0] for correctness
        $this->assertEquals("needs-action", $list[0]->getParticipants()['51F1']->getParticipationStatus());
        $this->assertEquals("accepted", $list[0]->getParticipants()['8RIv']->getParticipationStatus());

        // The second event ($list[1]) has two participants -> one with participation status "declined" and the other
        // with participation status "tentative"
        // That's why here we check these 2 values of participation status from $list[1] for correctness
        $this->assertEquals("declined", $list[1]->getParticipants()['51F1']->getParticipationStatus());
        $this->assertEquals("tentative", $list[1]->getParticipants()['8RIv']->getParticipationStatus());
    }

    public function testCanParseCalendarEventAlarm()
    {
        $this->init();

        $calendarEvents = $this->accessor->getAll();
        $list = $this->mapper->mapToJmap($calendarEvents, $this->adapter);

        $this->assertEquals("OffsetTrigger", reset($list[0]->getAlerts())->getTrigger()->getType());
        $this->assertEquals("start", reset($list[0]->getAlerts())->getTrigger()->getRelativeTo());
        $this->assertEquals("-PT15M", reset($list[0]->getAlerts())->getTrigger()->getOffset());
    }

    public function testCanParseCalendarEventAlarmWithANonFullHour()
    {
        $this->init();

        $calendarEvents = $this->accessor->getAll();
        $list = $this->mapper->mapToJmap($calendarEvents, $this->adapter);

        $this->assertEquals("OffsetTrigger", reset($list[1]->getAlerts())->getTrigger()->getType());
        $this->assertEquals("start", reset($list[1]->getAlerts())->getTrigger()->getRelativeTo());
        $this->assertEquals("-PT5H30M", reset($list[1]->getAlerts())->getTrigger()->getOffset());
    }

    private function arraysAreEqual($arr1, $arr2)
    {
        // If the array indexes don't match, return immediately
        if (count(array_diff_assoc($arr1, $arr2))) {
            return false;
        }

        // After checking the indexes, check whether all of the array values match
        foreach ($arr1 as $key => $value) {
            if ($value !== $arr2[$key]) {
                return false;
            }
        }

        // All indexes are identical and all values are equal
        return true;
    }
}
