<?php

namespace OpenXPort\DataAccess;

class SquirrelMailTasksDataAccessMock extends SquirrelMailTasksDataAccess
{
    protected function init()
    {
        require_once(__DIR__ . '/mock_functions.php');
    }
}
