<?php

namespace OpenXPort\DataAccess;

use abook_group_database;
use OpenXPort\Util\AdapterUtil;

class SquirrelMailContactGroupDataAccess extends AbstractDataAccess
{
    public function getAll($accountId = null)
    {
        require_once(__DIR__ . '/../../../../functions/addressbook.php');
        require_once(__DIR__ . '/../../../abook_group/abook_group_database.php');

        global $username;

        $abookGroupDatabaseParams = array();
        $abookGroupDatabaseParams['dsn'] = '';
        $abookGroupDatabaseParams['table'] = '';
        $abookGroupDatabaseParams['owner'] = $username;

        $abookGroupDatabase = new abook_group_database($abookGroupDatabaseParams);
        $contactGroups = array();

        $groupsToFetch = $abookGroupDatabase->list_group();

        // In case there are no groups, return an empty list
        if (is_null($groupsToFetch) || !isset($groupsToFetch) || empty($groupsToFetch)) {
            return [];
        }

        foreach ($groupsToFetch as $group) {
            // Create an array to hold the information that we need for each group
            $contactGroup = array();
            $groupName = $group['addressgroup'];

            $contactGroup['name'] = $groupName;
            $contactGroup['id'] = hash("sha256", $groupName);

            // If the group's name is unset, null or the empty string, then skip this group
            if (is_null($groupName) || !isset($groupName) || empty($groupName)) {
                continue;
            }

            $groupContacts = $abookGroupDatabase->list_groupMembers($groupName);
            if (!is_null($groupContacts) && isset($groupContacts) && !empty($groupContacts)) {
                foreach ($groupContacts as $contact) {
                    // Base ID on  each contact's nickname, since contact nicknames
                    // in SQMail are unique for each contact
                    $contactGroup['contacts'][] = hash("sha256", $contact['nickname']);
                }
            }

            $contactGroups[] = $contactGroup;
        }

        return $contactGroups;
    }

    public function get($ids, $accountId = null)
    {
        throw new BadMethodCallException("Get via ContactGroup/get not implemented");
    }

    public function create($contactGroupsToCreate, $accountId = null)
    {
        throw new BadMethodCallException("Create via ContactGroup/set not implemented");
    }

    public function destroy($ids, $accountId = null)
    {
        throw new BadMethodCallException("Destroy via ContactGroup/set not implemented");
    }

    // TODO support multiple filter conditions like in the standard
    public function query($accountId, $filter = null)
    {
        throw new BadMethodCallException("Query via ContactGroup/set not implemented");
    }
}
