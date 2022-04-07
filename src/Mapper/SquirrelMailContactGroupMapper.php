<?php

namespace OpenXPort\Mapper;

use Jmap\Contact\ContactGroup;

class SquirrelMailContactGroupMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter)
    {
        $list = [];

        foreach ($data as $contactGroup) {
            $adapter->setContactGroup($contactGroup);

            // Currently we don't set addressBookId to anything, since I couldn't see any
            // counterpart for it in SQMail
            $jmapContactGroup = new ContactGroup();
            $jmapContactGroup->setId($adapter->getId());
            $jmapContactGroup->setName($adapter->getName());
            $jmapContactGroup->setContactIds($adapter->getContactIds());

            array_push($list, $jmapContactGroup);
        }

        return $list;
    }
}
