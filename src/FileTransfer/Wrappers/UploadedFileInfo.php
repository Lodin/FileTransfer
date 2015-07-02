<?php

namespace FileTransfer\Wrappers;

class UploadedFileInfo
{
    /**
     * Subdirectory which separates files with different purposes.
     *
     * @var string
     */
    public $subdir = '';

    /**
     * Subdirectory part which is limited by $filesInFolder parameter in
     * Transfer class.
     *
     * @var string
     */
    public $part = '';

    /**
     * File name.
     *
     * @var string
     */
    public $name = '';

    /**
     * File extension.
     *
     * @var string
     */
    public $ext = '';

    /**
     * Creates new instance of UploadedFileInfo class.
     *
     * @param string $subdir
     * @param string $part
     * @param string $name
     * @param string $ext
     */
    public function __construct($subdir, $part, $name, $ext)
    {
        $this->subdir = $subdir;
        $this->part = $part;
        $this->name = $name;
        $this->ext = $ext;
    }
}
