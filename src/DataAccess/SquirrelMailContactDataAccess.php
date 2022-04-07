<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;
use OpenXPort\Util\AdapterUtil;

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

    public function get($ids, $accountId = null)
    {
        throw new BadMethodCallException("Get via Contact/get not implemented");
    }

    public function create($contactsToCreate, $accountId = null)
    {
        throw new BadMethodCallException("Create via Contact/set not implemented");
    }

    public function destroy($ids, $accountId = null)
    {
        throw new BadMethodCallException("Destroy via Contact/set not implemented");
    }

    // TODO support multiple filter conditions like in the standard
    public function query($accountId, $filter = null)
    {
        throw new BadMethodCallException("Query via Contact/set not implemented");
    }
}
