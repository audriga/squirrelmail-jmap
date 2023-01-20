<?php

/* START OF OPENXPORT Code only */
// Use our composer autoload

require_once('vendor/autoload.php');

// Print debug output via API on error
// NOTE: Do not use on public-facing setups
$handler = new \OpenXPort\Jmap\Core\ErrorHandler();
$handler->setHandlers();

// TODO We should move to some other form of configuration since
// manually requiring a php file will not work when using in classes
// and using composer with our current config.php breaks easily
// require_once __DIR__ . '/config/config.php';

use Squirrel\Config;

// Initialize Graylog if installed

$logger = null;

if (Config::$allowGraylog && class_exists('Gelf\Logger')) {
    try {
        // Try sending a log line first and raise exception in case of error
        if (Config::$graylogUseTls) {
            $sslOptions = new Gelf\Transport\SslOptions();

            $sslOptions->setAllowSelfSigned(Config::$graylogAllowSelfSigned);
        }

        $transport = new Gelf\Transport\TcpTransport(Config::$graylogEndpoint, Config::$graylogPort, $sslOptions);
        $logger = new Gelf\Logger($transport);

        $logger->info("We should log some version information here");

        // Ignore all future logging errors and init Logger
        $transport = new Gelf\Transport\IgnoreErrorTransportWrapper($transport);
        $logger = new Gelf\Logger($transport);

        $logger->info("Logger has been successfully initialized");
        OpenXPort\Util\Logger::init($logger);
    } catch (exception $e) {
        $graylogException = $e;
    }
}

// Initialize file-based logger when Graylog is not included or exception has been raised
if (Config::$allowFileLog && (!$logger || $graylogException)) {
    $logger = new OpenXPort\Util\FileLogger(Config::$logPath, Config::$logLevel);
    OpenXPort\Util\Logger::init($logger);
    $logger->info("We should log some version information here");
    if ($graylogException) {
        // Log to file just like in Jmap\Core\ErrorHandler
        $logger->error(
            "Exception raised when initializing Graylog. " .
            "EXCEPTION " . $graylogException->getCode() . ":" .
            " - Message " . $graylogException->getMessage() .
            " - File " . $graylogException->getFile() .
            " - Line " . $graylogException->getLine()
        );
    }
}

// Reuse auth from webmailer
require_once __DIR__ . '/bridge.php';

/**
 * Array to hold data access classes for different types of data
 * "null" means that no data access class is present/available for the given data type
*/
$accessors = array(
    "Contacts" => new \OpenXPort\DataAccess\SquirrelMailContactDataAccess(),
    "CalendarEvents" => new \OpenXPort\DataAccess\SquirrelMailCalendarEventDataAccess(),
    "Tasks" => new \OpenXPort\DataAccess\SquirrelMailTasksDataAccess(),
    "Notes" => null,
    "Identities" => null,
    "Filters" => null,
    "StorageNodes" => new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccess(),
    "Calendars" => new \OpenXPort\DataAccess\SquirrelMailCalendarDataAccess(),
    "ContactGroups" => new \OpenXPort\DataAccess\SquirrelMailContactGroupDataAccess()
);

/**
 * Array to hold adapter classes for different types of data
 * "null" means that no adapter class is present/available for the given data type
*/
$adapters = array(
    "Contacts" => new SquirrelMailContactAdapter(),
    "CalendarEvents" => new \OpenXPort\Adapter\SquirrelMailCalendarEventAdapter(),
    "Tasks" => new SquirrelMailTasksAdapter(),
    "Notes" => null,
    "Identities" => null,
    "Filters" => null,
    "StorageNodes" => null,
    "Calendars" => new SquirrelMailCalendarAdapter(),
    "ContactGroups" => new \OpenXPort\Adapter\SquirrelMailContactGroupAdapter()
);

/**
 * Array to hold mapper classes for different types of data
 * "null" means that no mapper class is present/available for the given data type
*/
$mappers = array(
    "Contacts" => new SquirrelMailContactMapper(),
    "CalendarEvents" => new \OpenXPort\Mapper\SquirrelMailCalendarEventMapper(),
    "Tasks" => new SquirrelMailTaskMapper(),
    "Notes" => null,
    "Identities" => null,
    "Filters" => null,
    "StorageNodes" => new \OpenXPort\Jmap\Mapper\SquirrelMailStorageNodeMapper(),
    "Calendars" => new SquirrelMailCalendarMapper(),
    "ContactGroups" => new \OpenXPort\Mapper\SquirrelMailContactGroupMapper()
);

OpenXPort\Util\AdapterUtil::setEncodingCallback(array('\SquirrelMail\Util\SquirrelMailUtil', 'getMyCharset'));

$server = new Jmap\Core\Server($accessors, $adapters, $mappers);
$server->listen();
