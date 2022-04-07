<?php

use OpenXPort\Adapter\AbstractAdapter;
use OpenXPort\Jmap\Calendar\CalendarRights;

class SquirrelMailCalendarAdapter extends AbstractAdapter {
    
    // This is the calendar folder from SquirrelMail that we're transforming to a JMAP calendar
    private $calendar;

    public function getCalendar() {
        return $this->calendar;
    }

    public function setCalendar($calendar) {
        $this->calendar = $calendar;
    }

    public function getId() {
        return $this->calendar->id->value;
    }

    public function getName() {
        return AdapterUtil::decodeHtml($this->calendar->name->value);
    }

    public function getShareWith() {
        $calendarReadableUsers = $this->calendar->readable_users->value;
        $calendarWriteableUsers = $this->calendar->writeable_users->value;

        $jmapShareWith = [];

        if (!empty($calendarReadableUsers)) {
            $calendarReadableUserRights = new CalendarRights();
            $calendarReadableUserRights->setMayReadItems(true);
            
            if (is_array($calendarReadableUsers)) {
                foreach ($calendarReadableUsers as $calendarReadableUser) {
                    $jmapShareWith[$calendarReadableUser] = $calendarReadableUserRights;
                }
            } else {
                $jmapShareWith[$calendarReadableUsers] = $calendarReadableUserRights;
            }
        }

        if (!empty($calendarWriteableUsers)) {
            $calendarWriteableUserRights = new CalendarRights();
            $calendarWriteableUserRights->setMayAddItems(true);
            $calendarWriteableUserRights->setMayUpdateAll(true);
            $calendarWriteableUserRights->setMayRemoveAll(true);
            $calendarReadableUserRights->setMayReadItems(true);

            if (is_array($calendarWriteableUsers)) {
                foreach ($calendarWriteableUsers as $calendarWriteableUser) {
                    $jmapShareWith[$calendarWriteableUser] = $calendarWriteableUserRights;
                }
            } else {
                $jmapShareWith[$calendarWriteableUsers] = $calendarWriteableUserRights;
            }
        }

        if (count($jmapShareWith) === 0) {
            return null;
        }

        return $jmapShareWith;
    }

    public function getRole() {
        // Obtain the calendar folder's id, since this is what we use to differentiate whether it has the 'inbox' role or not
        $calendarFolderId = $this->calendar->id->value;

        // The calendar folder id has the format 'sm_cal_<xxx>' where <xxx> is either a date (in case of non-default,
        // i.e. non-inbox folder) or the calendar folder's owner's username (with '@' and '.' replaced by '_') (in case of inbox folder)
        // Thus, we check if the folder is NOT of the former format (i.e., the one containing date)
        // If it does NOT contain date, then it is inbox and we return 'inbox' as role
        // Otherwise, we return null as role

        // As per the comments above, extract the second part of the calendar folder id
        $calendarFolderIdSecondPart = substr($calendarFolderId, 7);

        // This is the format in which the second part would be formatted if it is a date
        $format = 'Ymd\THis\Z';

        // Check the second part of the id -> if it is NOT a correctly parseable date (i.e. createFromFormat returns false),
        // then we have an inbox folder and we return the role of 'inbox'
        // Otherwise, it's not an inbox one and we return null
        if (DateTime::createFromFormat($format, $calendarFolderIdSecondPart) === false) {
            return "inbox";
        }
        return null;
    }
}
