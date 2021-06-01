<?php

use OpenXPort\Mapper\AbstractMapper;
use Jmap\Calendar\CalendarEvent;

class SquirrelMailCalendarEventMapper extends AbstractMapper {

    public function mapFromJmap($jmapData, $adapter) {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter) {
        $list = [];

        foreach ($data as $e) {

            /**
             * Swap newline characters in the DESCRIPTION prop for spaces, since newlines in this prop
             * seem to break the iCal library and force it create new invalid iCal props out of the
             * portions of the value of DESCRIPTION following a newline character
             */
            $e->description->value = trim(preg_replace("/[\r\n]+/", " ", $e->description->value));
            $e->description->rawValue = trim(preg_replace("/[\r\n]+/", " ", $e->description->rawValue));
            
            // Create an iCal object for each event and then feed this object into the adapter
            $icalObj = new ZCiCal($e->getICal(true));
            
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

            array_push($list, $je);
        }

        return $list;
    }

}