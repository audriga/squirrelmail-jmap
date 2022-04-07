<?php

use OpenXPort\Mapper\AbstractMapper;
use OpenXPort\Jmap\Contact\Contact;

class SquirrelMailContactMapper extends AbstractMapper {

    public function mapFromJmap($jmapData, $adapter) {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter) {
        $list = [];

        foreach ($data as $c) {
            $adapter->setContact($c);

            $jc = new Contact();
            $jc->setId($adapter->getId());
            $jc->setPrefix($adapter->getPrefix());
            $jc->setFirstName($adapter->getFirstName());
            $jc->setLastName($adapter->getLastName());
            $jc->setNickname($adapter->getNickname());
            $jc->setBirthday($adapter->getBirthday());
            $jc->setAnniversary($adapter->getAnniversary());
            $jc->setCompany($adapter->getCompany());
            $jc->setDepartment($adapter->getDepartment());
            $jc->setNotes($adapter->getNotes());
            $jc->setPhones($adapter->getPhones());
            $jc->setOnline($adapter->getOnline());
            $jc->setAddresses($adapter->getAddresses());
            $jc->setEmails($adapter->getEmails());
            $jc->setGender($adapter->getGender());
            $jc->setRelatedTo($adapter->getRelatedTo());
            $jc->setDisplayname($adapter->getDisplayname());

            array_push($list, $jc);
        }

        return $list;
    }

}
