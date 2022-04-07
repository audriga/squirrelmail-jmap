<?php

namespace OpenXPort\Adapter;

use OpenXPort\Adapter\AbstractAdapter;
use OpenXPort\Jmap\Calendar\Participant;
use OpenXPort\Jmap\Calendar\Alert;
use OpenXPort\Jmap\Calendar\OffsetTrigger;

class SquirrelMailCalendarEventMetaDataAdapter extends AbstractAdapter
{
    // This is the meta data from ProMail that we're transforming to JMAP attendees, resp. reminders
    private $metaData;

    public function getMetaData()
    {
        return $this->metaData;
    }

    public function setMetaData($metaData)
    {
        $this->metaData = $metaData;
    }

    /**
     * Takes attendees from the passed meta data from SQMail (if any)
     * and delivers a map of JMAP Participants
     * (as per https://datatracker.ietf.org/doc/html/draft-ietf-calext-jscalendar-32#section-4.4.5)
     */
    public function getParticipants()
    {
        $jmapParticipants = [];

        if (isset($this->metaData['attendees'])
            && !is_null($this->metaData['attendees'])
            && !empty($this->metaData['attendees'])) {
            
            $attendees = $this->metaData['attendees'];

            foreach ($attendees as $attendeeId => $attendee) {
                $jmapParticipant = new Participant();
                $jmapParticipant->setType("Participant");
                
                if (isset($attendee['email'])
                    && !is_null($attendee['email'])
                    && !empty($attendee['email'])) {
                        $jmapParticipant->setEmail($attendee['email']);
                }

                if (isset($attendee['status'])
                    && !is_null($attendee['status'])
                    && !empty($attendee['status'])) {
                        $jmapParticipant->setParticipationStatus(\SquirrelMailCalendarEventAdapterUtil::convertFromSquirrelMailAttendeeStatusToJmapParticipationStatus($attendee['status']));
                }

                $jmapParticipants[$attendeeId] = $jmapParticipant;
            }
        }

        if (count($jmapParticipants) === 0) {
            return null;
        }

        return $jmapParticipants;
    }

    public function getAlerts()
    {
        $jmapAlerts = [];

        if (isset($this->metaData['enableReminder'])
            && !is_null($this->metaData['enableReminder'])
            && $this->metaData['enableReminder'] !== 0) {

            
            if (isset($this->metaData['reminderTime']) && !is_null($this->metaData['reminderTime'])) {
    
                $reminderTime = $this->metaData['reminderTime'];

                $jmapAlert = new Alert();
                $jmapAlert->setType("Alert");

                // After inspecting the ProMail UI, it was observed that reminders can have an offset (expressed as duration)
                // before the start of an event and this is the only option to specify when a reminder of a given event
                // is actually going to be fired.
                // Thus, it'd be suitable to use an OffsetTrigger for the JMAP alert and not an AbsoluteTrigger
                // (since AbsoluteTrigger requires an exact date and time and not a duration/offset).
                // So, since the reminder can only be fired via an offset before an event's start, we create an OffsetTrigger
                // which indicates the value of $this->metaData['reminderTime'] as the duration in minutes (as a negative ISO 8601 Duration)
                // before the event's start (i.e. with the OffsetTrigger's 'relativeTo' set to 'start')
                // when the alarm/reminder needs to be triggered.
                // Furthermore, 'reminderTime' always contains its value as minutes which can range from 0 to 720 (12 hours)
                // Thus, up to the value of 60 we can parse 'reminderTime' as minutes. Between the values of 60 and 720, we can parse it as hours
                $jmapTrigger = new OffsetTrigger();
                $jmapTrigger->setType("OffsetTrigger");

                // Check the value of 'reminderTime' and parse it as either minutes or hours, depending on the value
                if ($reminderTime < 60) {
                    // Set the JMAP trigger's offset to be formatted as minutes
                    $jmapTrigger->setOffset("-PT" . $reminderTime . "M");
                } elseif ($reminderTime >= 60 && $reminderTime <= 720) {
                    // When working with hours, we can also have values from ProMail that indicate hours with halves
                    // (e.g., 5.5 or 3.5 hours). In this case we check if modulo of $reminderTime and 60 is 30.
                    // If yes => we parse the hour and we round it down (e.g. 5.5 gets rounded down to 5) and we
                    // add the remainder with a value of 30 as minutes (so that 5.5 hours parses to 5 hours and 30 minutes)
                    if ($reminderTime % 60 !== 0) {
                        // Round the hours after division by 60 to the next lowest value (e.g. 5.5 rounds down to 5)
                        $hours = round($reminderTime / 60, 0, PHP_ROUND_HALF_DOWN);

                        // Take the half part of the hour as 30 minutes (this is the modulo of the hour value divided by 60)
                        $minutes = $reminderTime % 60;

                        // Set the JMAP trigger's offset to be formatted as hours and minutes
                        $jmapTrigger->setOffset("-PT" . $hours . "H" . $minutes . "M");
                    } else {
                        // Here we're dealing with hours without a half part
                        // Divide $reminderTime by 60 in order to get the value as hours
                        $reminderTime = $reminderTime / 60;

                        // Set the JMAP trigger's offset to be formatted as hours
                        $jmapTrigger->setOffset("-PT" . $reminderTime . "H");
                    }
                }
                
                $jmapTrigger->setRelativeTo("start");

                // Set the created trigger from above as the JMAP Alert's trigger
                $jmapAlert->setTrigger($jmapTrigger);

                // Since JMAP Alerts need to be a map of IDs to JMAP Alert objects, we create a random ID
                // to add as the map's key via md5, uniqid and rand
                $jmapAlertId = md5(uniqid(rand(), true));

                $jmapAlerts[$jmapAlertId] = $jmapAlert;
            }
        }

        if (count($jmapAlerts) === 0) {
            return null;
        }
        
        return $jmapAlerts;
    }
}
