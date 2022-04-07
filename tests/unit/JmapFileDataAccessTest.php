<?php

namespace OpenXPort\Jmap\File;

use PHPUnit\Framework\TestCase;

final class JmapFileDataAccessTest extends TestCase
{
    private const ACCOUNT_ID = 'test@user.to';
    private const FILE_WITH_AJXP_META = '/phpupgrade-test/apr.zip';

    private function init(): void
    {
        // include unit test config
        require_once("config.php");

        // include mock classes
        require_once("mock_accessor.php");
    }

    public function testCanGetStorageNode(): void
    {
        $this->init();

        $ids = ['/current archives/OneNote Notebooks.txt'];
        $mapper = new \OpenXPort\Jmap\Mapper\SquirrelMailStorageNodeMapper();
        $accessor = new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccessMock();

        $accessor->login("david@neu.ro");
        $files = $accessor->get($ids);
        $list = $mapper->mapToJmap($files, null);

        $this->assertEquals('/current archives/OneNote Notebooks.txt', $list[0]->getId());
        $this->assertEquals('/current archives/', $list[0]->getparentId());
        $this->assertEquals('/current archives/OneNote Notebooks.txt', $list[0]->getBlobId());
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $list[0]->getCreated());
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $list[0]->getModified());
        $this->assertEquals('OneNote Notebooks.txt', $list[0]->getName());
        $this->assertEquals('25', $list[0]->getSize());
    }

    // NOTE Requires actual binary data in order to work
    public function testCanGetStorageNodes(): void
    {
        $this->init();

        $ids = ['/apr.png', '/Old Town Trolley Tours® of Washington DC route map.pdf'];
        $mapper = new \OpenXPort\Jmap\Mapper\SquirrelMailStorageNodeMapper();
        $accessor = new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccessMock();

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->get($ids);
        $list = $mapper->mapToJmap($files, null);

        $this->assertEquals('/apr.png', $list[0]->getBlobId());
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $list[0]->getCreated());
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $list[0]->getModified());
        $this->assertEquals('apr.png', $list[0]->getName());
        $this->assertEquals('62826', $list[0]->getSize());

        $this->assertEquals(
            '/Old Town Trolley Tours® of Washington DC route map.pdf',
            $list[1]->getBlobId()
        );
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $list[1]->getCreated());
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $list[1]->getModified());
        $this->assertEquals('Old Town Trolley Tours® of Washington DC route map.pdf', $list[1]->getName());
        $this->assertEquals('694212', $list[1]->getSize());
    }

    // NOTE Requires actual binary data in order to work
    public function testCanGetStorageNodeWithDescription(): void
    {
        $this->init();

        $ids = [JmapFileDataAccessTest::FILE_WITH_AJXP_META];
        $mapper = new \OpenXPort\Jmap\Mapper\SquirrelMailStorageNodeMapper();
        $accessor = new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);

        $files = $accessor->get($ids);
        $list = $mapper->mapToJmap($files, null);

        $this->assertEquals(JmapFileDataAccessTest::FILE_WITH_AJXP_META, $list[0]->getBlobId());
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $list[0]->getCreated());
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $list[0]->getModified());
        $this->assertEquals('apr.zip', $list[0]->getName());
        $this->assertEquals('55548', $list[0]->getSize());
        $this->assertEquals('hola', $list[0]->getDescription());
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQueryRoot(): void
    {
        $this->init();

        $accessor = new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $filter = new \OpenXPort\Jmap\Files\FilterCondition(null, $ancestorIds = ['root']);

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->query(JmapFileDataAccessTest::ACCOUNT_ID, $filter);

        $this->assertContains('/phpupgrade-test/apr.png', $files);
        $this->assertNotContains('root', $files);
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQueryTrash(): void
    {
        $this->init();

        $accessor = new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $filter = new \OpenXPort\Jmap\Files\FilterCondition(null, $ancestorIds = ['trash']);

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->query(JmapFileDataAccessTest::ACCOUNT_ID, $filter);

        $this->assertContains('/recycle_bin/argnarr.tmp', $files);
        $this->assertNotContains('root', $files);
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQuerySubfolder(): void
    {
        $accessor = new \OpenXport\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $filter = new \OpenXPort\Jmap\Files\FilterCondition(null, $ancestorIds = ['/phpupgrade-test/']);

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->query(JmapFileDataAccessTest::ACCOUNT_ID, $filter);

        $this->assertContains('/phpupgrade-test/apr.png', $files);
        $this->assertNotContains('root', $files);
        $this->assertNotContains('/apr.png', $files);
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQueryRootAndFilesOnly(): void
    {
        $this->init();

        $accessor = new \OpenXport\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $filter = new \OpenXPort\Jmap\Files\FilterCondition(null, ['root'], true);

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->query(JmapFileDataAccessTest::ACCOUNT_ID, $filter);

        $this->assertContains('/phpupgrade-test/apr.png', $files);
        $this->assertNotContains('root', $files);
        $this->assertNotContains('/phpupgrade-test', $files);
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQueryFilesOnly(): void
    {
        $this->init();

        $accessor = new \OpenXport\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $filter = new \OpenXPort\Jmap\Files\FilterCondition(null, null, true);

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->query(JmapFileDataAccessTest::ACCOUNT_ID, $filter);

        $this->assertContains('/phpupgrade-test/apr.png', $files);
        $this->assertNotContains('root', $files);
        $this->assertNotContains('/phpupgrade-test', $files);
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQueryRootAndDirsOnly(): void
    {
        $accessor = new \OpenXport\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $filter = new \OpenXPort\Jmap\Files\FilterCondition(null, ['root'], false);

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->query(JmapFileDataAccessTest::ACCOUNT_ID, $filter);

        $this->assertContains('/phpupgrade-test/', $files);
        $this->assertNotContains('root', $files);
        $this->assertNotContains('/phpupgrade-test/apr.png', $files);
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQueryDirsOnly(): void
    {
        $this->init();

        $accessor = new \OpenXport\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $filter = new \OpenXPort\Jmap\Files\FilterCondition(null, null, false);

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->query(JmapFileDataAccessTest::ACCOUNT_ID, $filter);

        $this->assertContains('root', $files);
        $this->assertContains('/phpupgrade-test/', $files);
        $this->assertNotContains('/phpupgrade-test/apr.png', $files);
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQuerySingleDir(): void
    {
        $accessor = new \OpenXport\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $filter = new \OpenXPort\Jmap\Files\FilterCondition(['root']);

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->query(JmapFileDataAccessTest::ACCOUNT_ID, $filter);

        $this->assertContains('/phpupgrade-test/', $files);
        $this->assertNotContains('root', $files);
        $this->assertNotContains('/phpupgrade-test/apr.png', $files);
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQueryAll(): void
    {
        $this->init();

        $accessor = new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccessMock();

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->query(JmapFileDataAccessTest::ACCOUNT_ID);

        $this->assertContains('root', $files);
        $this->assertContains('/phpupgrade-test/', $files);
        $this->assertContains('/phpupgrade-test/apr.png', $files);
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQuerySubdir(): void
    {
        $this->init();

        $accessor = new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $filter = new \OpenXPort\Jmap\Files\FilterCondition(null, $ancestorIds = ['/phpupgrade-test/']);

        $accessor->login(JmapFileDataAccessTest::ACCOUNT_ID);
        $files = $accessor->query(JmapFileDataAccessTest::ACCOUNT_ID, $filter);

        $this->assertContains('/phpupgrade-test/apr.png', $files);
        $this->assertContains('/phpupgrade-test/.htaccess', $files);
        $this->assertNotContains('root', $files);
        $this->assertNotContains('/apr.png', $files);
    }

    // NOTE Requires actual binary data in order to work
    public function testCanQuerySubdirWithSingleFile(): void
    {
        $this->init();

        $accessor = new \OpenXPort\DataAccess\SquirrelMailStorageNodeDataAccessMock();
        $filter = new \OpenXPort\Jmap\Files\FilterCondition(null, $ancestorIds = ['/current archives']);

        $accessor->login('david@neu.ro');
        $files = $accessor->query('david@neu.ro', $filter);

        $this->assertNotContains('root', $files);
        $this->assertContains('/current archives/OneNote Notebooks.txt', $files);
    }
}
