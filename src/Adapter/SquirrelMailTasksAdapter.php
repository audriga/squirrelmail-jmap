<?php

use OpenXPort\Adapter\AbstractAdapter;

class SquirrelMailTasksAdapter extends AbstractAdapter {

    private $task;

    public function getTask() {
        return $this->task;
    }

    public function setTask($task) {
        $this->task = $task;
    }

    public function getDue() {
        $due = $this->task[0];

        if (!isset($due) || is_null($due)) {
            return null;
        }

        /**
         * Append "T00:00:00", since the SQMail task only has date without time
         * Date format shouldn't be changed, since it's the same
         *
        */
        return $due . "T00:00:00";
    }

    public function getTitle() {
        $title = $this->task[1];

        if (!isset($title) || is_null($title)) {
            return "";
        }

        return $title;
    }

    public function getDescription() {
        $description = $this->task[2];

        if (!isset($description) || is_null($description)) {
            return "";
        }

        return $description;
    }

}