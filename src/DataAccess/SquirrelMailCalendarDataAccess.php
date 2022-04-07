<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;
use OpenXPort\Util\AdapterUtil;

// TODO Cross-account access not implemented. We currently ignore accountId from call.
class SquirrelMailCalendarDataAccess extends AbstractDataAccess
{
    protected $accountId;

    /* Initialize Data Accessor with userId*/
    protected function init()
    {
        require_once(__DIR__ . '/../../../../functions/global.php');

        sqGetGlobalVar('username', $this->accountId);
    }

    public function getAll($accountId = null)
    {
        // Import the SQMail code for working with calendars
        require_once(__DIR__ . '/../../../calendar/functions.php');
        require_once(__DIR__ . '/../../../calendar/backend_functions.php');

        $this->init();
        
        // Fetch all calendar folders which the user owns
        $calendars = get_all_owned_calendars($this->accountId);

        return $calendars;
    }

    public function get($ids, $accountId = null)
    {
        throw new BadMethodCallException("Get via Calendar/get not implemented");
    }

    public function create($eventsToCreate, $accountId = null)
    {
        throw new BadMethodCallException("Create via Calendar/set not implemented");
    }

    public function destroy($ids, $accountId = null)
    {
        throw new BadMethodCallException("Destroy via Calendar/set not implemented");
    }

    // TODO support multiple filter conditions like in the standard
    public function query($accountId, $filter = null)
    {
        throw new BadMethodCallException("Query via Calendar/set not implemented");
    }
}
