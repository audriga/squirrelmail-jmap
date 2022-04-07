<?php

namespace Squirrel;

use PHPUnit\Framework\TestCase;

final class SquirrelStorageNodeTest extends TestCase
{
    private const ACCOUNT_ID = 'david@neu.ro';
    private const DIR_ID = '/current archives';
    private const FILE_ID = '/current archives/OneNote Notebooks.txt';
    private const FILE_ID_ABSENT = '/current archives/idontexist.txt';
    private const ROOT = __DIR__ . '/../resources/file_share/data/personal/';

    private const ACCOUNT_WITH_AJXP_META = 'test@user.to';
    private const DIR_WITH_AJXP_META = '/phpupgrade-test';
    private const FILE_WITH_AJXP_META = '/phpupgrade-test/apr.zip';
    private const TRASH_WITH_AJXP_META = '/recycle_bin';

    public function testCanBeCreatedFromFilePath(): void
    {
        // include unit test config
        // TODO move to init function and depend on it
        require_once("config.php");

        $node = new StorageNode(SquirrelStorageNodeTest::ACCOUNT_ID, SquirrelStorageNodeTest::FILE_ID);

        $this->assertInstanceOf(StorageNode::class, $node);
        $this->assertEquals(SquirrelStorageNodeTest::FILE_ID, $node->getPath());
        $this->assertEquals(SquirrelStorageNodeTest::FILE_ID, $node->getBlobId());
    }

    public function testCannnotBeCreatedFromAbsentFilePath(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $node = new StorageNode(SquirrelStorageNodeTest::ACCOUNT_ID, SquirrelStorageNodeTest::FILE_ID_ABSENT);
    }

    public function testPrintContentOfFile(): void
    {
        $node = new StorageNode(SquirrelStorageNodeTest::ACCOUNT_ID, SquirrelStorageNodeTest::FILE_ID);

        $this->assertEquals('Allmynotebooksbelongtous' . PHP_EOL, $node->getContent());
    }

    public function testCannotPrintContentOfDir(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $node = new StorageNode(SquirrelStorageNodeTest::ACCOUNT_ID, SquirrelStorageNodeTest::DIR_ID);

        $node->getContent();
    }

    public function testCanListDir(): void
    {
        $node = new StorageNode(SquirrelStorageNodeTest::ACCOUNT_ID, SquirrelStorageNodeTest::DIR_ID);

        $this->assertEquals(SquirrelStorageNodeTest::DIR_ID . '/OneNote Notebooks.txt', $node->listDir()[0]);
    }

    public function testCannotListFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $node = new StorageNode(SquirrelStorageNodeTest::ACCOUNT_ID, SquirrelStorageNodeTest::FILE_ID);

        $node->listDir();
    }

    public function testCanListFilesOnly(): void
    {
        $node = new StorageNode('test@user.to', '/');

        $this->assertFalse(
            in_array('phpupgrade-test', $node->listDir(true))
        );
    }

    public function testCanListDirsOnly(): void
    {
        $node = new StorageNode('test@user.to', '/');

        $this->assertContains('/phpupgrade-test/', $node->listDir(false));
    }

    public function testCanPrintDirectMetadataFromFile(): void
    {
        // TODO We probably do not care about the following, right?
        // mimestring_id="23"
        // file_group="1" $data["uid"]
        // file_owner="1" $data["gid"]
        $node = new StorageNode(SquirrelStorageNodeTest::ACCOUNT_ID, SquirrelStorageNodeTest::FILE_ID);

        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $node->getCreated());
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $node->getModified());
        $this->assertEquals(
            SquirrelStorageNodeTest::ROOT . SquirrelStorageNodeTest::ACCOUNT_ID . SquirrelStorageNodeTest::FILE_ID,
            $node->getFullPath()
        );
        $this->assertEquals('25', $node->getSize()); // This is "bytesize" from Pydio API
        $this->assertEquals('OneNote Notebooks.txt', $node->getName()); // This is "name" from Pydio API
        $this->assertEquals('text/plain', $node->getType());
    }

    public function testCanPrintDirectMetadataFromFolder(): void
    {
        // TODO We probably do not care about the following, right?
        // file_group="1" $data["uid"]
        // file_owner="1" $data["gid"]
        $node = new StorageNode(SquirrelStorageNodeTest::ACCOUNT_ID, SquirrelStorageNodeTest::DIR_ID);

        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $node->getCreated());
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%dZ', $node->getModified()); // "ajxp_modiftime" from Pydio API

        $this->assertEquals(
            SquirrelStorageNodeTest::ROOT . SquirrelStorageNodeTest::ACCOUNT_ID . SquirrelStorageNodeTest::DIR_ID . "/",
            $node->getFullPath()
        );
        $this->assertEquals('4096', $node->getSize()); // This is "bytesize" from Pydio API
        $this->assertEquals('current archives', $node->getName()); // This is "name" from Pydio API
        $this->assertEquals(SquirrelStorageNodeTest::DIR_ID . '/', $node->getPath());
        $this->assertNull($node->getBlobId());
        $this->assertNull($node->getType());
        $this->assertNull($node->getDescription());
    }

    public function testRootHasNoParentId(): void
    {
        $node = new StorageNode(SquirrelStorageNodeTest::ACCOUNT_ID, "/");

        $this->assertNull($node->getBlobId());
        $this->assertNull($node->getParentId());
    }

    public function testTrashHasNoParentId(): void
    {
        $node = new StorageNode(SquirrelStorageNodeTest::ACCOUNT_ID, "/recycle_bin/");

        $this->assertNull($node->getBlobId());
        $this->assertNull($node->getParentId());
    }

    public function testCanPrintExtraMetadata(): void
    {
        // comment="comment" meta_labels="Comment" is_image="0" mimestring="File"/>
        $node = new StorageNode(
            SquirrelStorageNodeTest::ACCOUNT_WITH_AJXP_META,
            SquirrelStorageNodeTest::FILE_WITH_AJXP_META
        );

        $this->assertEquals('55548', $node->getSize()); // This is "bytesize" from Pydio API
        $this->assertEquals('apr.zip', $node->getName()); // This is "name" from Pydio API
        $this->assertEquals('hola', $node->getDescription());
    }

    public function testCanReadHiddenFile(): void
    {
        $node = new StorageNode($this::ACCOUNT_WITH_AJXP_META, $this::DIR_WITH_AJXP_META);

        $this->assertContains($this::DIR_WITH_AJXP_META . '/apr.zip', $node->listDir());
        $this->assertContains($this::DIR_WITH_AJXP_META . '/.htaccess', $node->listDir());
        $this->assertNotContains($this::DIR_WITH_AJXP_META . '/.ajxp_meta', $node->listDir());
    }

    public function testSkipsAjxpRecycleMetaDuringListDir(): void
    {
        $node = new StorageNode($this::ACCOUNT_WITH_AJXP_META, $this::TRASH_WITH_AJXP_META);

        $this->assertContains($this::TRASH_WITH_AJXP_META . '/argnarr.tmp', $node->listDir());
        $this->assertNotContains($this::TRASH_WITH_AJXP_META . '/.ajxp_recycle_cache.ser', $node->listDir());
    }
}
