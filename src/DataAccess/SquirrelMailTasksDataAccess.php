<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class SquirrelMailTasksDataAccess extends AbstractDataAccess
{
    public function getAll($accountId = null)
    {
        // Import the SQMail code for working with tasks
        require_once(__DIR__ . '/../../../todo/functions.php');

        $todos = '';
        todo_init($todos);

        // Split all todos into individual todos
        $todos = explode(PHP_EOL, trim($todos));

        $res = [];

        foreach ($todos as $t) {
            // Transform individual todos from string to array ('\t' is the property divider in the string)
            $t = explode("\t", $t);
            array_push($res, $t);
        }

        return $res;
    }

    public function write()
    {
        // TODO: Implement me
    }

    // TODO support multiple filter conditions like in the standard
    public function query($accountId, $filter = null)
    {
        // TODO: Implement me in case you want to be fancy
    }
}
