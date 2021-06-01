<?php

/* START OF OPENXPORT Code only */

// Reuse auth from webmailer
require_once __DIR__ . '/mailer.php';

require_once __DIR__ . '/config/config.php';

/**
 * Array to hold data access classes for different types of data
 * "null" means that no data access class is present/available for the given data type
*/
$accessors = array(
    "Contacts" => new \OpenXPort\DataAccess\SquirrelMailContactDataAccess(),
    "Calendars" => new \OpenXPort\DataAccess\SquirrelMailCalendarEventDataAccess(),
    "Tasks" => new \OpenXPort\DataAccess\SquirrelMailTasksDataAccess(),
    "Notes" => null,
    "Settings" => null,
    "Filters" => null,
    "Files" => new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccess()
);

/**
 * Array to hold adapter classes for different types of data
 * "null" means that no adapter class is present/available for the given data type
*/
$adapters = array(
    "Contacts" => new SquirrelMailContactAdapter(),
    "Calendars" => new SquirrelMailCalendarEventAdapter(),
    "Tasks" => new SquirrelMailTasksAdapter(),
    "Notes" => null,
    "Settings" => null,
    "Filters" => null,
    "Files" => null
);

/**
 * Array to hold mapper classes for different types of data
 * "null" means that no mapper class is present/available for the given data type
*/
$mappers = array(
    "Contacts" => new SquirrelMailContactMapper(),
    "Calendars" => new SquirrelMailCalendarEventMapper(),
    "Tasks" => new SquirrelMailTaskMapper(),
    "Notes" => null,
    "Settings" => null,
    "Filters" => null,
    "Files" => new \Jmap\Mapper\SquirrelMailStorageNodeMapper()
);

$server = new Server($accessors, $adapters, $mappers);
$server->listen();
