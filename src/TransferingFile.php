<?php

namespace FileTransfer;

use SimpleFile;
use MimeList;
use abeautifulsite\SimpleImage;

class TransferingFile extends SimpleFile
{
    protected $_transfer;

    public function setOwner(Transfer $transfer)
    {
        $this->_transfer = $transfer;
    }

    public function validate()
    {
        $isExtensionsValid = $this->checkExtensions();
        $isMimetypesValid = $this->checkMimeTypes();

        return $isExtensionsValid && $isMimetypesValid;
    }

    public function moveHandled($fname, $action)
    {
        try {
            $action($this, $fname);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    protected function checkMimeTypes()
    {
        $mimeList = new MimeList(
            MimeList::USE_CACHE,
            $this->_transfer->mimeCacheFile
        );

        foreach ($this->_transfer->allowedExtensions as $extension) {
            $type = mimeList->guess($extension);

            if ($type !== null) {
                $mimelist[] = $type;
            }
        }

        $mimetype = false;
        {
            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimetype = $finfo->file($this->tmpName);
            } elseif (function_exists('mime_content_type')) {
                $mimetype = mime_content_type($this->tmpName);
            } else {
                return true;
            }
        }

        if ($mimetype === false || !in_array($mimetype, $mimelist)) {
            return false;
        }

        return true;
    }

    protected function checkExtensions()
    {
        return in_array(
            pathinfo($this->name, PATHINFO_EXTENSION),
            $this->_transfer->allowedExtensions
        );
    }
}
