<?php

use OpenXPort\Mapper\AbstractMapper;
use OpenXPort\Jmap\Calendar\Calendar;

class SquirrelMailCalendarMapper extends AbstractMapper {
    
    public function mapFromJmap($jmapData, $adapter) {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter) {
        $list = [];
        
        foreach ($data as $calendarFolder) {
            $adapter->setCalendar($calendarFolder);

            $jmapCalendarFolder = new Calendar();
            $jmapCalendarFolder->setId($adapter->getId());
            $jmapCalendarFolder->setName($adapter->getName());
            $jmapCalendarFolder->setShareWith($adapter->getShareWith());
            $jmapCalendarFolder->setRole($adapter->getRole());

            array_push($list, $jmapCalendarFolder);
        }

        return $list;
    }
}