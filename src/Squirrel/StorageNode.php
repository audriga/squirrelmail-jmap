<?php

// NOTE: This is custom tailored for web.com project
namespace Squirrel;

class StorageNode
{
    /** This is currently the path or special folder string root/trash
     * @var string **/
    private $id;

    /** This is "bytesize" from Pydio API
     * @var int **/
    private $modified;

    /** This is "name" from Pydio API
     * @var string **/
    private $name;

    /** @var string **/
    private $basePath;

    /** @var string **/
    private $fullPath;

    /** @var string **/
    private $relativePath;

    /** This is "ajxp_modiftime" from Pydio API
        * @var int **/
    private $size;

    /** @var string **/
    private $parentId;

    /** MIME type
     * @var string **/
    private $type;

    /** Comment added by user
     * @var string **/
    private $description;

    public function __construct($accountId, $relativePath)
    {
        $this->accountId = $accountId;

        $fullPath = Config::$filesRoot . $accountId . $relativePath;
        if (!file_exists($fullPath)) {
            throw new \UnexpectedValueException('The following file was not found on disk: ' . $relativePath);
        }

        // Make sure folder path ends with /
        if (is_dir($fullPath) and !$this->endsWith($fullPath, "/")) {
            $this->fullPath = $fullPath . "/";
            $relativePath = $relativePath . "/";
        } else {
            $this->fullPath = $fullPath;
        }

        $this->relativePath = $relativePath;
        $this->name = pathinfo($relativePath)["basename"];

        $data = stat($this->fullPath);

        $parentFolder = null;
        // Derive parentId
        if ($relativePath != "/" and $relativePath != "/recycle_bin/") {
            $parentFolder = dirname($relativePath);

            if ($parentFolder == "/") {
                $this->parentId = "root";
            } elseif ($parentFolder == "/recycle_bin") {
                $this->parentId = "trash";
            } else {
                $this->parentId = $parentFolder . "/";
            }
        }

        $this->created = $this->toUtc($data["ctime"]);
        $this->modified = $this->toUtc($data["mtime"]);
        $this->size = $data["size"];

        // Derive MIME Type
        $type = mime_content_type($this->fullPath);
        if ($type != 'directory') {
            $this->type = $type;
        }

        if ($parentFolder) {
            $ajxp_file = dirname($this->fullPath) . "/.ajxp_meta";
            if (file_exists($ajxp_file)) {
                $handle = fopen($ajxp_file, 'r');
                $content = fread($handle, filesize($ajxp_file));

                fclose($handle);
                $tmp = unserialize($content);
                $this->description = $tmp[$this->name]["AJXP_METADATA_SHAREDUSER"]["users_meta"]["comment"];
            }
        }
    }

    private function toUtc($unixTime)
    {
        return gmdate("o-m-d\TH:i:s\Z", $unixTime);
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return $length > 0 ? substr($haystack, -$length) === $needle : true;
    }

    public function getId()
    {
        if ($this->relativePath == "/") {
            return "root";
        } elseif ($this->relativePath == "/recycle_bin/") {
            return "trash";
        } else {
            return $this->relativePath;
        }
    }

    public function getContent()
    {
        if (is_dir($this->fullPath)) {
            throw new \InvalidArgumentException('getContent() only accepts Files. Input was: ' . $this->relativePath);
        }

        $handle = fopen($this->fullPath, 'r');
        $content = fread($handle, filesize($this->fullPath));

        fclose($handle);

        return $content;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getModified()
    {
        return $this->modified;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return $this->relativePath;
    }

    public function getBlobId()
    {
        if (!is_dir($this->fullPath)) {
            return $this->relativePath;
        }
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function getFullPath()
    {
        return $this->fullPath;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /* Returns relativePaths. */
    public function listDir($blobFilter = null)
    {
        $relativePath = $this->relativePath;
        $fullPath = $this->fullPath;

        if (!is_dir($fullPath)) {
            throw new \InvalidArgumentException('listDir() only accepts Directories. Input was: ' . $relativePath);
        }

        $fullPaths = glob($fullPath . '*', GLOB_MARK);

        $basePathLength = strlen(Config::$filesRoot . $this->accountId);
        $result = array();

        foreach ($fullPaths as $fullPath) {
            if (!is_null($blobFilter)) {
                // Only include files
                if ($blobFilter && !is_dir($fullPath)) {
                    array_push($result, substr($fullPath, $basePathLength));
                // Only include folders
                } elseif (!$blobFilter && is_dir($fullPath)) {
                    array_push($result, substr($fullPath, $basePathLength));
                }
            } else {
                array_push($result, substr($fullPath, $basePathLength));
            }
        }

        return $result;
    }
}
