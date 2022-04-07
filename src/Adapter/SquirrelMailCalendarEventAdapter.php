<?php

namespace OpenXPort\Adapter;

require(__DIR__ . '/../icalendar/zapcallib.php');

use OpenXPort\Adapter\AbstractAdapter;
use OpenXPort\Util\AdapterUtil;

use OpenXPort\Jmap\Calendar\Location;
use OpenXPort\Jmap\Calendar\RecurrenceRule;

require_once(__DIR__ . '/../Util/SquirrelMailCalendarEventAdapterUtil.php');

class SquirrelMailCalendarEventAdapter extends AbstractAdapter {

    // This is an iCal event component (and not an entire iCal object)
    private $iCalEvent;
    private $logger;

    public function __construct()
    {
        $this->logger = \OpenXPort\Util\Logger::getInstance();
    }

    public function getICalEvent() {
        return $this->iCalEvent;
    }

    public function setICalEvent($iCalEvent) {
        $this->iCalEvent = $iCalEvent;
    }

    public function getCalendarId() {
        $calendarId = $this->iCalEvent->data["X-SQ-EVTPARENTCALENDARS"];

        if (is_null($calendarId) || !isset($calendarId)) {
            return null;
        }

        return $calendarId->getValues();
    }

    public function getDTStart() {
        $dtStart = $this->iCalEvent->data["DTSTART"];

        $jmapStart = null;
        $date = \DateTime::createFromFormat("Ymd\THis\Z", $dtStart->getValues());

        // If there's no 'Z' at the end of the date, try to parse the date without it
        if ($date === false) {
            $date = \DateTime::createFromFormat("Ymd\THis", $dtStart->getValues());
        }

        // If the date still can't be parsed, try parsing it without a time component
        if ($date === false) {
            $date = \DateTime::createFromFormat("Ymd", $dtStart->getValues());
            $jmapStart = \date_format($date, "Y-m-d");

            // Add default values for time in the 'start' JMAP property
            $jmapStart .= "T00:00:00";

            return $jmapStart;
        }

        $jmapStart = date_format($date, "Y-m-d\TH:i:s");
        return $jmapStart;
    }

    public function getDuration() {
        $dtStart = $this->iCalEvent->data["DTSTART"];
        $dtEnd = $this->iCalEvent->data["DTEND"];

        $format = "Ymd\THis";
        $formatWithZ = "Ymd\THis\Z";

        $dateStart = \DateTime::createFromFormat($formatWithZ, $dtStart->getValues());
        $dateEnd = \DateTime::createFromFormat($formatWithZ, $dtEnd->getValues());

        // Analogically to getDTStart(), try different parsing strategy for dates, in case they didn't parse correctly at first
        if ($dateStart === false || $dateEnd === false) {
            $dateStart = \DateTime::createFromFormat($format, $dtStart->getValues());
            $dateEnd = \DateTime::createFromFormat($format, $dtEnd->getValues());
        }

        if ($dateStart === false || $dateEnd === false) {
            $dateStart = \DateTime::createFromFormat("Ymd", $dtStart->getValues());
            $dateEnd = \DateTime::createFromFormat("Ymd", $dtEnd->getValues());
        }

        $interval = $dateEnd->diff($dateStart);
        return $interval->format('P%aDT%hH%iM');
    }

    public function getSummary() {
        return AdapterUtil::decodeHtml($this->iCalEvent->data["SUMMARY"]->getValues());
    }

    public function getDescription() {
        $description = $this->iCalEvent->data["DESCRIPTION"];

        if (is_null($description)) {
            return NULL;
        }

        return AdapterUtil::decodeHtml($description->getValues());
    }

    public function getStatus() {
        $status = $this->iCalEvent->data['STATUS'];

        if (is_null($status)) {
            return NULL;
        }

        switch ($status->getValues()) {
            case 'TENTATIVE':
                return "tentative";
                break;

            case 'CONFIRMED':
                return "confirmed";
                break;

            case 'CANCELLED':
                return "cancelled";
                break;
            
            default:
                return NULL;
                break;
        }
    }

    public function getUid() {
        $uid = $this->iCalEvent->data['UID'];

        if (is_null($uid)) {
            return NULL;
        }

        return $uid->getValues();
    }

    public function getProdId() {
        if (is_null($this->iCalEvent->parentnode)) {
            return null;
        }

        $prodId = $this->iCalEvent->parentnode->data['PRODID'];

        if (is_null($prodId)) {
            return NULL;
        }

        return $prodId->getValues();
    }

    public function getCreated() {
        $created = $this->iCalEvent->data['CREATED'];

        if (is_null($created)) {
            return NULL;
        }

        $iCalFormat = 'Ymd\THis\Z';
        $jmapFormat = 'Y-m-d\TH:i:s\Z';

        $dateCreated = \DateTime::createFromFormat($iCalFormat, $created->getValues());
        $jmapCreated = date_format($dateCreated, $jmapFormat);

        return $jmapCreated;
    }

    public function getLastModified() {
        $lastModified = $this->iCalEvent->data['LAST-MODIFIED'];

        if (is_null($lastModified)) {
            return NULL;
        }

        $iCalFormat = 'Ymd\THis\Z';
        $jmapFormat = 'Y-m-d\TH:i:s\Z';

        $dateLastModified = \DateTime::createFromFormat($iCalFormat, $lastModified->getValues());
        $jmapLastModified = date_format($dateLastModified, $jmapFormat);

        return $jmapLastModified;
    }

    public function getSequence() {
        if (!array_key_exists("SEQUENCE", $this->iCalEvent->data)) {
            return 0;
        }

        $sequence = $this->iCalEvent->data['SEQUENCE'];

        // In case that the SEQUENCE we receive from iCalendar is unset, null or empty, just return 0 as a default value
        if (!isset($sequence) || is_null($sequence) || empty($sequence)) {
            $this->logger->warning(
                "The value for the \"sequence\" property was unset, null or empty. Setting 0 as default value"
            );
            return 0;
        }

        // The JMAP "sequence" property needs to have an int value, that's why we call intval() on the iCalendar
        // value of SEQUENCE. In case that intval() fails to convert $sequenceValue to an int, it returns 0,
        // which is still ok for us, because it's an int and we can return it as a default value for "sequence".
        return intval($sequence->getValues());
    }

    public function getLocation() {
        if (!array_key_exists("LOCATION", $this->iCalEvent->data)) {
            return null;
        }

        $location = $this->iCalEvent->data['LOCATION'];

        if (!isset($location) || is_null($location) || empty($location)) {
            return null;
        }

        $locationValues = explode(", ", $location->getValues());

        if (!isset($locationValues) || is_null($locationValues) || empty($locationValues)) {
            return null;
        }

        $jmapLocations = [];

        foreach ($locationValues as $lv) {
            // If a given location value is unset, null or empty, skip it
            if (!isset($lv) || is_null($lv) || empty($lv)) {
                $this->logger->warning("Location name was unset, null or empty. Skipping this location entry.");
                continue;
            }

            $jmapLocation = new Location();
            $jmapLocation->setType("Location");
            $jmapLocation->setName(AdapterUtil::decodeHtml($lv));

            // Create an ID as a key in the array via base64 (it should just be some random string;
            // I'm picking base64 as a random option)
            $key = base64_encode($lv);
            $jmapLocations["$key"] = $jmapLocation;
        }

        return $jmapLocations;
    }

    public function getCategories() {
        if (!array_key_exists("CATEGORIES", $this->iCalEvent->data)) {
            return null;
        }

        $categories = $this->iCalEvent->data['CATEGORIES'];

        if (is_null($categories)) {
            return NULL;
        }

        $jmapKeywords = [];

        $categoryValues = explode(",", $categories->getValues());

        foreach ($categoryValues as $c) {
            $jmapKeywords[AdapterUtil::decodeHtml($c)] = true;
        }

        return $jmapKeywords;
    }

    public function getRRule() {
        if (!array_key_exists("RRULE", $this->iCalEvent->data)) {
            return null;
        }

        $rRule = $this->iCalEvent->data['RRULE'];

        if (is_null($rRule)) {
            return NULL;
        }

        $rRuleValues = $rRule->getValues();

        // The library treats commas in RRULE as separator for rules and thus we need to fix this by putting the separated RRULE back together as one whole (and not as separate rules)
        if (!empty($rRule->getValues()) && count($rRule->getValues()) > 1) {
            $rRuleValues = implode(",", $rRule->getValues());
        }

        $jmapRecurrenceRule = new RecurrenceRule();
        $jmapRecurrenceRule->setType("RecurrenceRule");

        foreach (explode(";", $rRuleValues) as $r) {
            // Split each rule string by '=' and based upon its key (e.g. FREQ, COUNT, etc.), set the corresponding value to the JMAP RecurrenceRule object
            $splitRule = explode("=", $r);
            $key = $splitRule[0];
            $value = $splitRule[1];

            switch ($key) {
                case 'FREQ':
                    $jmapRecurrenceRule->setFrequency(\SquirrelMailCalendarEventAdapterUtil::convertFromICalFreqToJmapFrequency($value));
                    break;
                
                case 'INTERVAL':
                    $jmapRecurrenceRule->setInterval(\SquirrelMailCalendarEventAdapterUtil::convertFromICalIntervalToJmapInterval($value));
                    break;

                case 'RSCALE':
                    $jmapRecurrenceRule->setRscale(\SquirrelMailCalendarEventAdapterUtil::convertFromICalRScaleToJmapRScale($value));
                    break;
                
                case 'SKIP':
                    $jmapRecurrenceRule->setSkip(\SquirrelMailCalendarEventAdapterUtil::convertFromICalSkipToJmapSkip($value));
                    break;

                case 'WKST':
                    $jmapRecurrenceRule->setFirstDayOfWeek(\SquirrelMailCalendarEventAdapterUtil::convertFromICalWKSTToJmapFirstDayOfWeek($value));
                    break;
                
                case 'BYDAY':
                    $jmapRecurrenceRule->setByDay(\SquirrelMailCalendarEventAdapterUtil::convertFromICalByDayToJmapByDay($value));
                    break;

                case 'BYMONTHDAY':
                    $jmapRecurrenceRule->setByMonthDay(\SquirrelMailCalendarEventAdapterUtil::convertFromICalByMonthDayToJmapByMonthDay($value));
                    break;
                
                case 'BYMONTH':
                    $jmapRecurrenceRule->setByMonth(\SquirrelMailCalendarEventAdapterUtil::convertFromICalByMonthToJmapByMonth($value));
                    break;

                case 'BYYEARDAY':
                    $jmapRecurrenceRule->setByYearDay(\SquirrelMailCalendarEventAdapterUtil::convertFromICalByYearDayToJmapByYearDay($value));
                    break;
                
                case 'BYWEEKNO':
                    $jmapRecurrenceRule->setByWeekNo(\SquirrelMailCalendarEventAdapterUtil::convertFromICalByWeekNoToJmapByWeekNo($value));
                    break;

                case 'BYHOUR':
                    $jmapRecurrenceRule->setByHour(\SquirrelMailCalendarEventAdapterUtil::convertFromICalByHourToJmapByHour($value));
                    break;
                
                case 'BYMINUTE':
                    $jmapRecurrenceRule->setByMinute(\SquirrelMailCalendarEventAdapterUtil::convertFromICalByMinuteToJmapByMinute($value));
                    break;

                case 'BYSECOND':
                    $jmapRecurrenceRule->setBySecond(\SquirrelMailCalendarEventAdapterUtil::convertFromICalBySecondToJmapBySecond($value));
                    break;
                
                case 'BYSETPOS':
                    $jmapRecurrenceRule->setBySetPosition(\SquirrelMailCalendarEventAdapterUtil::convertFromICalBySetPositionToJmapBySetPos($value));
                    break;

                case 'COUNT':
                    $jmapRecurrenceRule->setCount(\SquirrelMailCalendarEventAdapterUtil::convertFromICalCountToJmapCount($value));
                    break;
                
                case 'UNTIL':
                    $jmapRecurrenceRule->setUntil(\SquirrelMailCalendarEventAdapterUtil::convertFromICalUntilToJmapUntil($value));
                    break;

                default:
                    // Maybe log something about an unexpected property/value in the parsed iCal RRULE?
                    break;
            }
        }

        return $jmapRecurrenceRule;
    }

    public function getExDate() {
        if (!array_key_exists("EXDATE", $this->iCalEvent->data)) {
            return null;
        }

        $exDate = $this->iCalEvent->data['EXDATE'];

        if (is_null($exDate)) {
            return NULL;
        }

        $splitExDateValues = explode(",", $exDate->getValues());
        
        $jmapRecurrenceOverrides = [];

        foreach ($splitExDateValues as $v) {
            $iCalFormat = 'Ymd\THis';
            $jmapFormat = 'Y-m-d\TH:i:s';

            $dateExDate = \DateTime::createFromFormat($iCalFormat, $v);
            $jmapExcludedRecurrenceOverride = date_format($dateExDate, $jmapFormat);

            $jmapRecurrenceOverrides[$jmapExcludedRecurrenceOverride] = array("@type" => "jsevent", "excluded" => true);
        }

        return $jmapRecurrenceOverrides;
    }

    public function getPriority() {
        $priority = $this->iCalEvent->data['PRIORITY'];

        if (is_null($priority)) {
            return NULL;
        }

        return $priority->getValues();
    }

    public function getClass() {
        if (!array_key_exists("CLASS", $this->iCalEvent->data)) {
            return null;
        }

        $class = $this->iCalEvent->data['CLASS'];

        if (is_null($class)) {
            return NULL;
        }

        switch ($class->getValues()) {
            case 'PUBLIC':
                return "public";

            case 'PRIVATE':
                return "private";

            case 'CONFIDENTIAL':
                return "secret";

            default:
                return NULL;
        }
    }

    public function getTimeZone() {
        if (is_null($this->iCalEvent->parentnode)) {
            return null;
        }

        $timezoneComponent = $this->iCalEvent->parentNode->tree->child['VTIMEZONE'];
        return $timezoneComponent;
    }

    public function getShowWithoutTime()
    {
        $dtStart = $this->iCalEvent->data["DTSTART"];
        $dtEnd = $this->iCalEvent->data["DTEND"];

        // Full day format for dates, e.g. 20210615, where 'Y' is year (2021), 'm' month (06) and 'd' day (15)
        // See https://www.php.net/manual/en/datetime.createfromformat.php
        $fullDayDateFormat = "Ymd";

        $dateStart = \DateTime::createFromFormat($fullDayDateFormat, $dtStart->getValues());
        $dateEnd = \DateTime::createFromFormat($fullDayDateFormat, $dtEnd->getValues());

        /**
         * If createFromFormat() above does not return false (i.e. parses successfully) for the full day format for 'DTSTART' and 'DTEND',
         * this means that both of these dates do not include time, i.e. are formatted without time.
         * Based on this, we set the JMAP property 'showWithoutTime' to true to indicate a full day event.
         */
        if ($dateStart !== false && $dateEnd !== false) {
            return true;
        }

        return false;
    }
}
