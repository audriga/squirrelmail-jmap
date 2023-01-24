<?php

use OpenXPort\Adapter\AbstractAdapter;
use OpenXPort\Util\AdapterUtil;

class SquirrelMailTasksAdapter extends AbstractAdapter {

    private $task;
    private $logger;

    public function __construct()
    {
        $this->logger = \OpenXPort\Util\Logger::getInstance();
    }

    public function getTask() {
        return $this->task;
    }

    public function setTask($task) {
        $this->task = $task;
    }

    public function getDue() {
        $due = $this->task[0];

        if (!isset($due) || is_null($due) || empty($due)) {
            return null;
        }

        // Usually the task due date from SQMail is formatted as "Y-m-d" (e.g. 2021-05-05)
        $longYearFormat = "Y-m-d";

        // Sometimes, however, it could also be formatted as "y-m-d" (e.g. 21-05-05), thus we need
        // to take this format into consideration as well
        $shortYearFormat = "y-m-d";

        // Try to parse the SQMail task due date with the short year format first
        $dueDate = DateTime::createFromFormat($shortYearFormat, $due);

        // If the parsing was unsuccessful (i.e., createFromFormat above returns false),
        // then try parsing with the long year format
        if ($dueDate === false) {            
            $dueDate = DateTime::createFromFormat($longYearFormat, $due);
        }

        // If the second parsing attempt failed as well, log and return null
        if ($dueDate === false) {
            $this->logger->error("Unable to parse due date: " . print_r($due, true));
            return null;
        }

        // Create a JMAP due date, according to the format Y-m-d (e.g. 2021-05-05)
        $jmapFormat = "Y-m-d";
        $jmapDueDate = $dueDate->format($jmapFormat);
        
        // Since we don't receive a time component from SQMail's due date, we have to append 'T00:00:00'
        // to the end of the JMAP due date, since the JMAP format contains a time component as well
        $jmapDueDate .= 'T00:00:00';

        return $jmapDueDate;
    }

    public function getTitle() {
        $title = $this->task[1];

        if (!isset($title) || is_null($title)) {
            return "";
        }

        return AdapterUtil::decodeHtml($title);
    }

    public function getDescription() {
        $description = $this->task[2];

        if (!isset($description) || is_null($description)) {
            return "";
        }

        $descriptionDec = AdapterUtil::decodeHtml($description);

        $this->logger->debug("Decoded \"" . $description . "\" to \"" . $descriptionDec . "\"");

        return $descriptionDec;
    }

}
