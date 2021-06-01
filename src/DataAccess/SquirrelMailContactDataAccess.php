<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class SquirrelMailContactDataAccess extends AbstractDataAccess
{

    public function getAll($accountId = null)
    {
        // Import the SQMail code for addressbook functionality and get all addressbooks for the user, set in the
        // global session var 'username' (the one set via admin auth with the value of 'accountId')
        require_once(__DIR__ . '/../../../../functions/addressbook.php');

        $abook = addressbook_init(false, false);
        $contacts = $abook->list_addr();

        return $contacts;
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
