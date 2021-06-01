<?php

namespace Jmap\Mapper;

use OpenXPort\Mapper\AbstractMapper;

class SquirrelMailStorageNodeMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter)
    {
        $list = [];
        //$notFound = [];

        foreach ($data as $node) {
            // No need for an adapter since SQFile is the adapter
            $jmapFile = new \Jmap\Files\StorageNode($node->getId());

            $jmapFile->setBlobId($node->getBlobId());
            $jmapFile->setParentId($node->getParentId());
            $jmapFile->setCreated($node->getCreated());
            $jmapFile->setModified($node->getModified());
            $jmapFile->setName($node->getName());
            $jmapFile->setType($node->getType());
            $jmapFile->setSize($node->getSize());
            $jmapFile->setDescription($node->getDescription());

            array_push($list, $jmapFile);
        }

        return $list;
    }
}
