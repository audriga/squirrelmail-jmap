<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;
use OpenXPort\Util\AdapterUtil;

class SquirrelMailTasksDataAccess extends AbstractDataAccess
{
    protected function init()
    {
        // Import the SQMail code for working with tasks
        require_once(__DIR__ . '/../../../todo/functions.php');
    }

    public function getAll($accountId = null)
    {
        $this->init();
        $todos = '';
        todo_init($todos);

        // Split all todos into individual todos
        $todos = explode(PHP_EOL, trim($todos));

        $res = [];

        foreach ($todos as $t) {
            if (isset($t) && !is_null($t) && !empty($t)) {
                // Transform individual todos from string to array ('\t' is the property divider in the string)
                $t = explode("\t", $t);
                array_push($res, $t);
            }
        }

        return $res;
    }

    public function get($ids, $accountId = null)
    {
        throw new BadMethodCallException("Get via Task/get not implemented");
    }

    public function create($contactsToCreate, $accountId = null)
    {
        throw new BadMethodCallException("Create via Task/set not implemented");
    }

    public function destroy($ids, $accountId = null)
    {
        throw new BadMethodCallException("Destroy via Task/set not implemented");
    }

    // TODO support multiple filter conditions like in the standard
    public function query($accountId, $filter = null)
    {
        throw new BadMethodCallException("Query via Task/set not implemented");
    }
}
