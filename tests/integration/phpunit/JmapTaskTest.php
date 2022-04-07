<?php

namespace Jmap\Task;

use PHPUnit\Framework\TestCase;

final class JmapTaskTest extends \PHPUnit_Framework_TestCase
{
    // Mapper, data accessor and adapter objects from OpenXPort that we're going to need throughout all tests
    private $mapper;
    private $accessor;
    private $adapter;

    private function init()
    {
        // Include the mock functions (for reading mock data)
        require_once(__DIR__ . '/mock_functions.php');
        require_once(__DIR__ . '/mock_task_accessor.php');

        // Set up the mapper, data accessor and adapter that we need for the tests
        $this->mapper = new \SquirrelMailTaskMapper();
        $this->accessor = new \OpenXPort\DataAccess\SquirrelMailTasksDataAccessMock();
        $this->adapter = new \SquirrelMailTasksAdapter();

        $handler = new \Jmap\Core\ErrorHandler();
        $handler->setHandlers();
    }

    public function testCanSanitizeBadEncoding()
    {
        $this->init();

        $todos = $this->accessor->getAll();
        $list = $this->mapper->mapToJmap($todos, $this->adapter);

        $expFirDesc = "This is my first note...\n\nChecking break lines";
        $expSecDesc = "Checking characters\n\n(｡･･｡)\n\náñ, _*; üê";
        $expThiTitl = "Third test ( ˘▽˘)っ♨";

        $this->assertNotEquals($list[2]->getTitle(), $expThiTitl);

        array_walk($list, array('OpenXPort\Util\AdapterUtil', 'sanitizeJson'));

        $actualTitle = $list[2]->getTitle();

        $this->assertEquals($list[0]->getDescription(), $expFirDesc);
        $this->assertEquals($list[1]->getDescription(), $expSecDesc);
        $this->assertEquals($list[2]->getTitle(), $expThiTitl);

        $json_enc = serializeAsJson($list);
        $json_dec = json_decode($json_enc);

        $this->assertEquals($json_dec[0]->description, $expFirDesc);
        $this->assertEquals($json_dec[1]->description, $expSecDesc);
        $this->assertEquals($json_dec[2]->title, $expThiTitl);
    }
}
