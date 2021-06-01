<?php

use OpenXPort\Mapper\AbstractMapper;
use Jmap\Task\Task;

class SquirrelMailTaskMapper extends AbstractMapper {

    public function mapFromJmap($jmapData, $adapter) {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter) {
        $list = [];
        
        foreach ($data as $t) {

            $adapter->setTask($t);

            $jt = new Task();
            $jt->setDue($adapter->getDue());
            $jt->setTitle($adapter->getTitle());
            $jt->setDescription($adapter->getDescription());

            array_push($list, $jt);
        }

        return $list;
    }

}