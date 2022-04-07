<?php

namespace OpenXPort\Mapper;

use OpenXPort\Mapper\AbstractMapper;
use OpenXPort\Jmap\Calendar\CalendarEvent;
use OpenXPort\Adapter\SquirrelMailCalendarEventMetaDataAdapter;

class SquirrelMailCalendarEventMapper extends AbstractMapper {

    public function mapFromJmap($jmapData, $adapter) {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter) {
        $list = [];
        
        foreach ($data as $eventsWithMeta) {

            // Since we can read calendar events together with meta data of theirs,
            // we have arrays with 2 entries as entries of $data
            // The first entry of each such array is the event itself, accessible  under the key 'event'.
            // The second entry is the event meta data, accessible under the key 'eventMetaData'.
            // Thus, here we first obtain each event via the key 'event'
            $e = $eventsWithMeta['event'];

            /**
             * Swap newline characters in the DESCRIPTION prop for spaces, since newlines in this prop
             * seem to break the iCal library and force it create new invalid iCal props out of the
             * portions of the value of DESCRIPTION following a newline character
             */
            $e->description->value = trim(preg_replace("/[\r\n]+/", " ", $e->description->value));
            $e->description->rawValue = trim(preg_replace("/[\r\n]+/", " ", $e->description->rawValue));
            
            // Create an iCal object for each event and then feed this object into the adapter
            $icalObj = new \ZCiCal($e->getICal(true));
            
            $adapter->setICalEvent($icalObj->tree);
        
            $je = new CalendarEvent();

            $je->setCalendarId($adapter->getCalendarId());        
            $je->setStart($adapter->getDTStart());
            $je->setDuration($adapter->getDuration());
            $je->setStatus($adapter->getStatus());
            $je->setType("jsevent");
            $je->setUid($adapter->getUid());
            $je->setProdId($adapter->getProdId());
            $je->setCreated($adapter->getCreated());
            $je->setUpdated($adapter->getLastModified());
            $je->setSequence($adapter->getSequence());
            $je->setTitle($adapter->getSummary());
            $je->setDescription($adapter->getDescription());
            $je->setLocations($adapter->getLocation());
            $je->setKeywords($adapter->getCategories());
            $je->setRecurrenceRule($adapter->getRRule());
            $je->setRecurrenceOverrides($adapter->getExDate());
            $je->setPriority($adapter->getPriority());
            $je->setPrivacy($adapter->getClass());
            $je->setTimeZone($adapter->getTimeZone());
            $je->setShowWithoutTime($adapter->getShowWithoutTime());

            // Check if the function 'readEventMeta' is available and only then
            // try to access an event's meta data (if we have any), containing reminders and attendees
            // This function is usually only present in ProMail.
            // Currently, it is present in SQMail only as a mock function, used for testing,
            // which delivers mock meta data
            if (function_exists('readEventMeta')
                && isset($eventsWithMeta['eventMetaData'])
                && !empty($eventsWithMeta['eventMetaData'])) {
                // Obtain each event's meta data from the 2-entry array,
                // as described at the beginning of this function
                $eventMetaData = $eventsWithMeta['eventMetaData'];

                // Create an adapter for the meta data which returns JMAP Alerts and JMAP Participants
                // that can be set on $je. Feed the adapter with the meta data, read from ProMail
                $metaDataAdapter = new SquirrelMailCalendarEventMetaDataAdapter();
                $metaDataAdapter->setMetaData($eventMetaData);
                
                // Obtain the JMAP Alerts and Participants from the adapter
                $jmapAlerts = $metaDataAdapter->getAlerts();
                $jmapParticipants = $metaDataAdapter->getParticipants();

                // Set $je's alerts and participants properties with the obtained
                // JMAP Alerts and Participants from the adapter
                $je->setAlerts($jmapAlerts);
                $je->setParticipants($jmapParticipants);
            }

            array_push($list, $je);
        }

        return $list;
    }

}