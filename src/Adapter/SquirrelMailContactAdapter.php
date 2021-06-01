<?php

use OpenXPort\Adapter\AbstractAdapter;
use Jmap\Contact\ContactInformation;
use Jmap\Contact\Address;

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

    public function getPrefix() {
        $prefix = $this->contact['title'];
        if (is_null($prefix)) {
            return null;
        }
        return $prefix;
    }

    public function getFirstName() {
        $firstName = $this->contact['firstname'];
        if (is_null($firstName)) {
            return null;
        }
        return $firstName;
    }

    public function getLastName() {
        $lastName = $this->contact['lastname'];
        if (is_null($lastName)) {
            return null;
        }
        return $lastName;
    }

    public function getNickname() {
        $nickname = $this->contact['nickname'];
        if (is_null($nickname)) {
            return null;
        }
        return $nickname;
    }

    public function getBirthday() {
        $birthday = $this->contact['birhday'];
        if (is_null($birthday)) {
            return null;
        }
        return $birthday;
    }

    public function getAnniversary() {
        $anniversary = $this->contact['anniversary'];
        if (is_null($anniversary)) {
            return null;
        }
        return $anniversary;
    }

    public function getCompany() {
        $company = $this->contact['company'];
        if (is_null($company)) {
            return null;
        }
        return $company;
    }

    public function getDepartment() {
        $department = $this->contact['division'];
        if (is_null($department)) {
            return null;
        }
        return $department;
    }

    public function getNotes() {
        $notes = $this->contact['label'];
        if (is_null($notes)) {
            return null;
        }
        return $notes;
    }

    public function getEmails() {
        $jmapEmails = [];

        $primaryEmail = $this->contact['email'];
        $secondaryEmail = $this->contact['secondaryemail'];

        if (!is_null($primaryEmail)) {
            $jmapPrimaryEmail = new ContactInformation();
            $jmapPrimaryEmail->setType('personal');
            $jmapPrimaryEmail->setValue($primaryEmail);
            $jmapPrimaryEmail->setLabel(null);
            $jmapPrimaryEmail->setIsDefault(true);

            array_push($jmapEmails, $jmapPrimaryEmail);
        }

        if (!is_null($secondaryEmail)) {
            $jmapSecondaryEmail = new ContactInformation();
            $jmapSecondaryEmail->setType('personal');
            $jmapSecondaryEmail->setValue($secondaryEmail);
            $jmapSecondaryEmail->setLabel(null);
            $jmapSecondaryEmail->setIsDefault(false);

            array_push($jmapEmails, $jmapSecondaryEmail);
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

        if (!is_null($workPhone)) {
            $jmapWorkPhone = new ContactInformation();
            $jmapWorkPhone->setType('work');
            $jmapWorkPhone->setValue($workPhone);
            $jmapWorkPhone->setLabel(null);
            $jmapWorkPhone->setIsDefault(false);

            array_push($jmapPhones, $jmapWorkPhone);
        }

        if (!is_null($workPhone2)) {
            $jmapWorkPhone2 = new ContactInformation();
            $jmapWorkPhone2->setType('work');
            $jmapWorkPhone2->setValue($workPhone2);
            $jmapWorkPhone2->setLabel(null);
            $jmapWorkPhone2->setIsDefault(false);

            array_push($jmapPhones, $jmapWorkPhone2);
        }

        if (!is_null($homePhone)) {
            $jmapHomePhone = new ContactInformation();
            $jmapHomePhone->setType('home');
            $jmapHomePhone->setValue($homePhone);
            $jmapHomePhone->setLabel(null);
            $jmapHomePhone->setIsDefault(false);

            array_push($jmapPhones, $jmapHomePhone);
        }

        if (!is_null($homePhone2)) {
            $jmapHomePhone2 = new ContactInformation();
            $jmapHomePhone2->setType('home');
            $jmapHomePhone2->setValue($homePhone2);
            $jmapHomePhone2->setLabel(null);
            $jmapHomePhone2->setIsDefault(false);

            array_push($jmapPhones, $jmapHomePhone2);
        }

        if (!is_null($mobilePhone)) {
            $jmapMobilePhone = new ContactInformation();
            $jmapMobilePhone->setType('mobile');
            $jmapMobilePhone->setValue($mobilePhone);
            $jmapMobilePhone->setLabel(null);
            $jmapMobilePhone->setIsDefault(false);

            array_push($jmapPhones, $jmapMobilePhone);
        }

        if (!is_null($mobilePhone2)) {
            $jmapMobilePhone2 = new ContactInformation();
            $jmapMobilePhone2->setType('mobile');
            $jmapMobilePhone2->setValue($mobilePhone2);
            $jmapMobilePhone2->setLabel(null);
            $jmapMobilePhone2->setIsDefault(false);

            array_push($jmapPhones, $jmapMobilePhone2);
        }

        if (!is_null($workFax)) {
            $jmapWorkFax = new ContactInformation();
            $jmapWorkFax->setType('fax');
            $jmapWorkFax->setValue($workFax);
            $jmapWorkFax->setLabel(null);
            $jmapWorkFax->setIsDefault(false);

            array_push($jmapPhones, $jmapWorkFax);
        }

        if (!is_null($homeFax)) {
            $jmapHomeFax = new ContactInformation();
            $jmapHomeFax->setType('fax');
            $jmapHomeFax->setValue($homeFax);
            $jmapHomeFax->setLabel(null);
            $jmapHomeFax->setIsDefault(false);

            array_push($jmapPhones, $jmapHomeFax);
        }

        return $jmapPhones;
    }

    public function getOnline() {
        $jmapOnline = [];

        $workIm = $this->contact['workim'];
        $homeIm = $this->contact['homeim'];
        $website = $this->contact['website'];

        if (!is_null($workIm)) {
            $jmapWorkIm = new ContactInformation();
            $jmapWorkIm->setType('username');
            $jmapWorkIm->setValue($workIm);
            $jmapWorkIm->setLabel(null);
            $jmapWorkIm->setIsDefault(false);

            array_push($jmapOnline, $jmapWorkIm);
        }

        if (!is_null($homeIm)) {
            $jmapHomeIm = new ContactInformation();
            $jmapHomeIm->setType('username');
            $jmapHomeIm->setValue($homeIm);
            $jmapHomeIm->setLabel(null);
            $jmapHomeIm->setIsDefault(false);

            array_push($jmapOnline, $jmapHomeIm);
        }

        if (!is_null($website)) {
            $jmapWebsite = new ContactInformation();
            $jmapWebsite->setType('uri');
            $jmapWebsite->setValue($website);
            $jmapWebsite->setLabel(null);
            $jmapWebsite->setIsDefault(false);

            array_push($jmapOnline, $jmapWebsite);
        }

        return $jmapOnline;
    }

    public function getAddresses() {
        $jmapAddresses = [];

        $workAddress = $this->contact['workaddress'];
        $homeAddress = $this->contact['homeaddress'];

        if (!is_null($workAddress)) {
            $jmapWorkAddress = new Address();
            $jmapWorkAddress->setType('work');
            $jmapWorkAddress->setLabel(null);

            // TODO: Find out how address data is structured and fill in the JMAP Address data accordingly (country, postcode, etc.)
            // Currently we put all the data into the 'street' property
            $jmapWorkAddress->setStreet($workAddress);

            $jmapWorkAddress->setIsDefault(false);

            array_push($jmapAddresses, $jmapWorkAddress);
        }

        if (!is_null($homeAddress)) {
            $jmapHomeAddress = new Address();
            $jmapHomeAddress->setType('home');
            $jmapHomeAddress->setLabel(null);

            // TODO: Find out how address data is structured and fill in the JMAP Address data accordingly (country, postcode, etc.)
            // Currently we put all the data into the 'street' property
            $jmapHomeAddress->setStreet($homeAddress);

            $jmapHomeAddress->setIsDefault(false);

            array_push($jmapAddresses, $jmapHomeAddress);
        }

        return $jmapAddresses;
    }

}