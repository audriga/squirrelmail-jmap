<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class SquirrelMailStorageNodeDataAccess extends AbstractDataAccess
{
    private function collectAncestorPaths($accountId, $ancestorIds, $hasBlobId = null)
    {
        $relativePaths = array();

        foreach ($ancestorIds as $parentId) {
            if ($parentId == "root") {
                $parentId = "/";
            } elseif ($parentId == "trash") {
                $parentId = "/recycle_bin/";
            }

            $node = new \Squirrel\StorageNode($accountId, $parentId);

            $relativePaths = array_merge(
                $relativePaths,
                $this->recurseDirs($accountId, $node, $hasBlobId)
            );
        }

        return $relativePaths;
    }

    private function collectParentPaths($accountId, $parentIds, $hasBlobId)
    {
        $relativePaths = array();

        foreach ($parentIds as $parentId) {
            if ($parentId == "root") {
                $parentId = "/";
            } elseif ($parentId == "trash") {
                $parentId = "/recycle_bin/";
            }

            $node = new \Squirrel\StorageNode($accountId, $parentId);

            $relativePaths = array_merge($relativePaths, $node->listDir($hasBlobId));
        }

        return $relativePaths;
    }

    private function recurseDirs($accountId, $node, $blobFilter = null)
    {
        // List folder only and recurse
        $dirPaths = $node->listDir(false);
        $relativePaths = array();

        foreach ($dirPaths as $path) {
            $new_node = new \Squirrel\StorageNode($accountId, $path);
            foreach ($this->recurseDirs($accountId, $new_node, $blobFilter) as $recursedPaths) {
                array_push($relativePaths, $recursedPaths);
            }
        }

        // Then list files as well depending on blobFilter

        if (!is_null($blobFilter)) {
            // Only include files
            if ($blobFilter) {
                $relativePaths = array_merge($relativePaths, $node->listDir(true));
            // Only include folders
            } else {
                $relativePaths = array_merge($relativePaths, $dirPaths);
            }
        } else {
            // Include both
            $relativePaths = array_merge($relativePaths, $dirPaths);
            $relativePaths = array_merge($relativePaths, $node->listDir(true));
        }

        return $relativePaths;
    }

    public function getAll($accountId = null)
    {
        // TODO
    }

    /** Get Storage Nodes for certain paths
        * id is relative path **/
    public function get($ids, $accountId = null, $includeParentsLimit = 0)
    {
        $nodes = [];

        foreach ($ids as $id) {
            if ($id == "root") {
                $id = "/";
            } elseif ($id == "trash") {
                $id = "/recycle_bin/";
            }
            array_push($nodes, new \Squirrel\StorageNode($accountId, $id));
        }

        return $nodes;
    }

    /** Get a list of Storage Nodes
        * NOTE only filtering for parentIds is implemented **/
    // TODO support multiple filter conditions like in the standard
    public function query($accountId, $filter = null)
    {
        $resultArray = array();

        if ($filter === null) {
            $tmpArray = $this->collectAncestorPaths($accountId, ["root"]);
            array_push($tmpArray, "root");

            return $tmpArray;
        }
        if (!is_null($filter->getAncestorIds())) {
            array_push(
                $resultArray,
                $this->collectAncestorPaths($accountId, $filter->getAncestorIds(), $filter->getHasBlobId())
            );
        }
        if (!is_null($filter->getParentIds())) {
            array_push(
                $resultArray,
                $this->collectParentPaths($accountId, $filter->getParentIds(), $filter->getHasBlobId())
            );
        }
        if (
            is_null($filter->getAncestorIds()) &&
            is_null($filter->getParentIds()) &&
            !is_null($filter->getHasBlobId())
        ) {
            $tmpArray = $this->collectAncestorPaths($accountId, ["root"], $filter->getHasBlobId());

            // Include root in case of dir only
            if (!$filter->getHasBlobId()) {
                array_push($tmpArray, "root");
            }

            return $tmpArray;
        }
        if (sizeof($resultArray) > 1) {
            $resultArray = call_user_func_array('array_intersect', $resultArray);
        } else {
            $resultArray = $resultArray[0];
        }

        return $resultArray;
    }

    public function write()
    {
        // TODO: Implement me
    }

    public function download($accountId, $name, $path, $accept)
    {
        // Inspiration was https://stackoverflow.com/a/32885706
        // Has more features like MIME type and ob_end_clean
        $file = new \Squirrel\StorageNode($accountId, $path);
        $mime_type = $accept;
        $size = $file->getSize();

        // Specified in JMAP Core
        header('Cache-Control: private, immutable, max-age=31536000');
        header('Content-Disposition: attachment; filename="' . $name . '"');

        // TODO raise error on incorrect MIME Type
        header('Content-Type: ' . $mime_type);

        // Own
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');

        if (isset($_SERVER['HTTP_RANGE'])) {
            list($a, $range) = explode("=", $_SERVER['HTTP_RANGE'], 2);
            list($range) = explode(",", $range, 2);
            list($range, $range_end) = explode("-", $range);
            $range = intval($range);

            if (!$range_end) {
                $range_end = $size - 1;
            } else {
                $range_end = intval($range_end);
            }

            $request_length = $range_end - $range + 1;

            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $request_length");
            header("Content-Range: bytes $range-$range_end/$size");
        } else {
            $request_length = $size;
            header("Content-Length: " . $size);
        }

        $chunk_size = 1 * (1024 * 1024);
        $bytes_sent = 0;

        // TODO use squirrelstoragenode for reading content?
        if ($path = fopen($file->getFullPath(), 'r')) {
            if (isset($_SERVER['HTTP_RANGE'])) {
                fseek($path, $range);
            }

            while (!feof($path) && (!connection_aborted()) && ($bytes_sent < $request_length)) {
                $buffer = fread($path, $chunk_size);
                echo($buffer);
                flush();
                $bytes_sent += strlen($buffer);
            }
            fclose($path);
        } else {
            die('Error - can not open file.');
        }
        die();
    }
}
