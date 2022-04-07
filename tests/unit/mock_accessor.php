<?php

namespace OpenXPort\DataAccess;

class SquirrelMailStorageNodeDataAccessMock extends SquirrelMailStorageNodeDataAccess
{
    protected $accountId;

    /* Initialize Data Accessor with userId*/
    protected function init()
    {
    }

    public function login($accountId)
    {
        $this->accountId = $accountId;
    }
}
