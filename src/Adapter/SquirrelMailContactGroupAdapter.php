<?php

namespace OpenXPort\Adapter;

use OpenXPort\Util\AdapterUtil;

class SquirrelMailContactGroupAdapter extends AbstractAdapter
{
    private $contactGroup;
    private $logger;

    public function __construct()
    {
        $this->logger = \OpenXPort\Util\Logger::getInstance();
    }

    public function getContactGroup()
    {
        return $this->contactGroup;
    }

    public function setContactGroup($contactGroup)
    {
        $this->contactGroup = $contactGroup;
    }

    public function getId()
    {
        if (!array_key_exists("id", $this->contactGroup)) {
            $this->logger->error("Contact group has no \"ID\" property");
            return null;
        }

        $contactGroupId = $this->contactGroup['id'];

        if (!isset($contactGroupId) || is_null($contactGroupId) || empty($contactGroupId)) {
            $this->logger->error("\"ID\" property of contact group was unset, null or empty");
            return null;
        }

        return $contactGroupId;
    }

    public function getAddressBookId()
    {
        // TODO: Implement me
        // There is no address book ID in SQMail or ProMail and that's why
        // here we simply return null
        return null;
    }

    public function getName()
    {
        if (!array_key_exists("name", $this->contactGroup)) {
            $this->logger->error("Contact group has no \"name\" property");
            return null;
        }

        $contactGroupName = $this->contactGroup['name'];

        if (!isset($contactGroupName) || is_null($contactGroupName) || empty($contactGroupName)) {
            $this->logger->warning("\"name\" property of contact group was unset, null or empty");
            return null;
        }

        return AdapterUtil::decodeHtml($contactGroupName);
    }

    public function getContactIds()
    {
        if (!array_key_exists("contacts", $this->contactGroup)) {
            $this->logger->error("Contact group has no \"contacts\" property");
            return null;
        }

        $contactGroupContacts = $this->contactGroup['contacts'];

        if (!isset($contactGroupContacts) || is_null($contactGroupContacts) || empty($contactGroupContacts)) {
            $this->logger->warning("\"contacts\" property of contact group was unset, null or empty");
            return null;
        }

        return $contactGroupContacts;
    }
}
