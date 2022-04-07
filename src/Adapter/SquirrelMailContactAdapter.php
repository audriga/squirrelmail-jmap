<?php

use OpenXPort\Adapter\AbstractAdapter;
use OpenXPort\Util\AdapterUtil;
use OpenXPort\Jmap\Contact\ContactInformation;
use OpenXPort\Jmap\Contact\Address;

class SquirrelMailContactAdapter extends AbstractAdapter {
    
    private $contact;

    public function getContact() {
        return $this->contact;
    }

    /** 
     * Use this function in order to avoid using a constructor which accepts args,
     * since we need an empty constructor for initialization of this class in the $dataAdapters array (in jmap.php)
     */ 
    public function setContact($contact) {
        $this->contact = $contact;
    }

    public function getId() {
        $nickname = $this->contact['nickname'];
        if (is_null($nickname) || !isset($nickname) || empty($nickname)) {
            throw new \InvalidArgumentException('Nickname should not be empty.');
        }
        return hash("sha256", $nickname);
    }


    public function getPrefix() {
        $prefix = $this->contact['title'];
        if (is_null($prefix) || !isset($prefix) || empty($prefix)) {
            return null;
        }
        return AdapterUtil::decodeHtml($prefix);
    }

    public function getFirstName() {
        $firstName = $this->contact['firstname'];
        if (is_null($firstName) || !isset($firstName) || empty($firstName)) {
            return null;
        }
        return AdapterUtil::decodeHtml($firstName);
    }

    public function getLastName() {
        $lastName = $this->contact['lastname'];
        if (is_null($lastName) || !isset($lastName) || empty($lastName)) {
            return null;
        }
        return AdapterUtil::decodeHtml($lastName);
    }

    public function getNickname() {
        $nickname = $this->contact['nickname'];
        if (is_null($nickname) || !isset($nickname) || empty($nickname)) {
            return null;
        }
        return AdapterUtil::decodeHtml($nickname);
    }

    public function getDisplayname() {
        $displayname = $this->contact['displayname'];
        if (is_null($displayname) || !isset($displayname) || empty($displayname)) {
            return null;
        }   
        return AdapterUtil::decodeHtml($displayname);
    }

    public function getBirthday() {
        $birthday = $this->contact['birthday'];
        if (is_null($birthday) || !isset($birthday) || empty($birthday)) {
            return null;
        }
        return $birthday;
    }

    public function getAnniversary() {
        $anniversary = $this->contact['anniversary'];
        if (is_null($anniversary) || !isset($anniversary) || empty($anniversary)) {
            return null;
        }
        return $anniversary;
    }

    public function getCompany() {
        $company = $this->contact['company'];
        if (is_null($company) || !isset($company) || empty($company)) {
            return null;
        }
        return AdapterUtil::decodeHtml($company);
    }

    public function getDepartment() {
        $department = $this->contact['division'];
        if (is_null($department) || !isset($department) || empty($department)) {
            return null;
        }
        return AdapterUtil::decodeHtml($department);
    }

    public function getNotes() {
        $notes = $this->contact['label'];
        if (is_null($notes) || !isset($notes) || empty($notes)) {
            return null;
        }
        return AdapterUtil::decodeHtml($notes);
    }

    public function getEmails() {
        $jmapEmails = [];

        $primaryEmail = $this->contact['email'];
        $secondaryEmail = $this->contact['secondaryemail'];

        if (!is_null($primaryEmail) && isset($primaryEmail) && !empty($primaryEmail)) {
            $jmapPrimaryEmail = new ContactInformation();
            $jmapPrimaryEmail->setType('personal');
            $jmapPrimaryEmail->setValue($primaryEmail);
            $jmapPrimaryEmail->setLabel(null);
            $jmapPrimaryEmail->setIsDefault(true);

            array_push($jmapEmails, $jmapPrimaryEmail);
        }

        if (!is_null($secondaryEmail) && isset($secondaryEmail) && !empty($secondaryEmail)) {
            $jmapSecondaryEmail = new ContactInformation();
            $jmapSecondaryEmail->setType('personal');
            $jmapSecondaryEmail->setValue($secondaryEmail);
            $jmapSecondaryEmail->setLabel(null);
            $jmapSecondaryEmail->setIsDefault(false);

            array_push($jmapEmails, $jmapSecondaryEmail);
        }

        if (count($jmapEmails) === 0) {
            return null;
        }

        return $jmapEmails;
    }

    public function getPhones() {
        $jmapPhones = [];

        $workPhone = $this->contact['workphone'];
        $workPhone2 = $this->contact['workphone2'];
        $homePhone = $this->contact['homephone'];
        $homePhone2 = $this->contact['homephone2'];
        $mobilePhone = $this->contact['mobilephone'];
        $mobilePhone2 = $this->contact['mobilephone2'];
        $workFax = $this->contact['workfax'];
        $homeFax = $this->contact['homefax'];

        if (!is_null($workPhone) && isset($workPhone) && !empty($workPhone)) {
            $jmapWorkPhone = new ContactInformation();
            $jmapWorkPhone->setType('work');
            $jmapWorkPhone->setValue($workPhone);
            $jmapWorkPhone->setLabel(null);
            $jmapWorkPhone->setIsDefault(false);

            array_push($jmapPhones, $jmapWorkPhone);
        }

        if (!is_null($workPhone2) && isset($workPhone2) && !empty($workPhone2)) {
            $jmapWorkPhone2 = new ContactInformation();
            $jmapWorkPhone2->setType('work');
            $jmapWorkPhone2->setValue($workPhone2);
            $jmapWorkPhone2->setLabel(null);
            $jmapWorkPhone2->setIsDefault(false);

            array_push($jmapPhones, $jmapWorkPhone2);
        }

        if (!is_null($homePhone) && isset($homePhone) && !empty($homePhone)) {
            $jmapHomePhone = new ContactInformation();
            $jmapHomePhone->setType('home');
            $jmapHomePhone->setValue($homePhone);
            $jmapHomePhone->setLabel(null);
            $jmapHomePhone->setIsDefault(false);

            array_push($jmapPhones, $jmapHomePhone);
        }

        if (!is_null($homePhone2) && isset($homePhone2) && !empty($homePhone2)) {
            $jmapHomePhone2 = new ContactInformation();
            $jmapHomePhone2->setType('home');
            $jmapHomePhone2->setValue($homePhone2);
            $jmapHomePhone2->setLabel(null);
            $jmapHomePhone2->setIsDefault(false);

            array_push($jmapPhones, $jmapHomePhone2);
        }

        if (!is_null($mobilePhone) && isset($mobilePhone) && !empty($mobilePhone)) {
            $jmapMobilePhone = new ContactInformation();
            $jmapMobilePhone->setType('mobile');
            $jmapMobilePhone->setValue($mobilePhone);
            $jmapMobilePhone->setLabel(null);
            $jmapMobilePhone->setIsDefault(false);

            array_push($jmapPhones, $jmapMobilePhone);
        }

        if (!is_null($mobilePhone2) && isset($mobilePhone2) && !empty($mobilePhone2)) {
            $jmapMobilePhone2 = new ContactInformation();
            $jmapMobilePhone2->setType('mobile');
            $jmapMobilePhone2->setValue($mobilePhone2);
            $jmapMobilePhone2->setLabel(null);
            $jmapMobilePhone2->setIsDefault(false);

            array_push($jmapPhones, $jmapMobilePhone2);
        }

        if (!is_null($workFax) && isset($workFax) && !empty($workFax)) {
            $jmapWorkFax = new ContactInformation();
            $jmapWorkFax->setType('fax');
            $jmapWorkFax->setValue($workFax);
            $jmapWorkFax->setLabel(null);
            $jmapWorkFax->setIsDefault(false);

            array_push($jmapPhones, $jmapWorkFax);
        }

        if (!is_null($homeFax) && isset($homeFax) && !empty($homeFax)) {
            $jmapHomeFax = new ContactInformation();
            $jmapHomeFax->setType('fax');
            $jmapHomeFax->setValue($homeFax);
            $jmapHomeFax->setLabel(null);
            $jmapHomeFax->setIsDefault(false);

            array_push($jmapPhones, $jmapHomeFax);
        }

        if (count($jmapPhones) === 0) {
            return null;
        }

        return $jmapPhones;
    }

    public function getOnline() {
        $jmapOnline = [];

        $workIm = $this->contact['workim'];
        $homeIm = $this->contact['homeim'];
        $website = $this->contact['website'];

        if (!is_null($workIm) && isset($workIm) && !empty($workIm)) {
            $jmapWorkIm = new ContactInformation();
            $jmapWorkIm->setType('username');
            $jmapWorkIm->setValue($workIm);
            $jmapWorkIm->setLabel(null);
            $jmapWorkIm->setIsDefault(false);

            array_push($jmapOnline, $jmapWorkIm);
        }

        if (!is_null($homeIm) && isset($homeIm) && !empty($homeIm)) {
            $jmapHomeIm = new ContactInformation();
            $jmapHomeIm->setType('username');
            $jmapHomeIm->setValue($homeIm);
            $jmapHomeIm->setLabel(null);
            $jmapHomeIm->setIsDefault(false);

            array_push($jmapOnline, $jmapHomeIm);
        }

        if (!is_null($website) && isset($website) && !empty($website)) {
            $jmapWebsite = new ContactInformation();
            $jmapWebsite->setType('uri');
            $jmapWebsite->setValue($website);
            $jmapWebsite->setLabel(null);
            $jmapWebsite->setIsDefault(false);

            array_push($jmapOnline, $jmapWebsite);
        }

        if (count($jmapOnline) === 0) {
            return null;
        }
        
        return $jmapOnline;
    }

    public function getAddresses() {
        $jmapAddresses = [];

        $workAddress = $this->contact['workaddress'];
        $homeAddress = $this->contact['homeaddress'];

        if (!is_null($workAddress) && isset($workAddress) && !empty($workAddress)) {
            $jmapWorkAddress = new Address();
            $jmapWorkAddress->setType('work');
            $jmapWorkAddress->setLabel(null);

            // TODO: Find out how address data is structured and fill in the JMAP Address data accordingly (country, postcode, etc.)
            // Currently we put all the data into the 'street' property
            $jmapWorkAddress->setStreet(AdapterUtil::decodeHtml($workAddress));

            $jmapWorkAddress->setIsDefault(false);

            array_push($jmapAddresses, $jmapWorkAddress);
        }

        if (!is_null($homeAddress) && isset($homeAddress) && !empty($homeAddress)) {
            $jmapHomeAddress = new Address();
            $jmapHomeAddress->setType('home');
            $jmapHomeAddress->setLabel(null);

            // TODO: Find out how address data is structured and fill in the JMAP Address data accordingly (country, postcode, etc.)
            // Currently we put all the data into the 'street' property
            $jmapHomeAddress->setStreet(AdapterUtil::decodeHtml($homeAddress));

            $jmapHomeAddress->setIsDefault(false);

            array_push($jmapAddresses, $jmapHomeAddress);
        }

        if (count($jmapAddresses) === 0) {
            return null;
        }

        return $jmapAddresses;
    }

    public function getGender() {
        $gender = $this->contact['gender'];
        if (is_null($gender) || !isset($gender) || empty($gender)) {
            return null;
        }
        return $gender;
    }

    public function getRelatedTo() {
        $jmapRelatedTo = [];

        $spouse = $this->contact['spouse'];

        if (isset($spouse) && !is_null($spouse) && !empty($spouse)) {
            $jmapRelatedTo["$spouse"] = array("relation" => array("spouse" => true));
        }

        if (count($jmapRelatedTo) === 0) {
            return null;
        }

        return $jmapRelatedTo;
    }

}
