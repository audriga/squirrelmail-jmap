<?php

use OpenXPort\Mapper\AbstractMapper;
use OpenXPort\Jmap\Task\Task;

class SquirrelMailTaskMapper extends AbstractMapper
{

    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter)
    {
        $list = [];

        if (!isset($data) || is_null($data) || empty($data)) {
            return $list;
        }

        foreach ($data as $t) {
            $adapter->setTask($t);

            $jt = new Task();
            $jt->setType('jstask');
            $jt->setDue($adapter->getDue());
            $jt->setTitle($adapter->getTitle());
            $jt->setDescription($adapter->getDescription());

            array_push($list, $jt);
        }

        return $list;
    }
}
