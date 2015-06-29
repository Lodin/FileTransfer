<?php

namespace FileTransfer\Wrappers;

use FileTransfer\Transfer;
use \SimpleFile;
use \MimeList;

/**
 * Implements wrapper around file uploading to server
 */
class TransferingFile extends SimpleFile
{
    protected $_transfer;

    /**
     * Adds controller to file instance
     * @param Transfer $transfer
     */
    public function setOwner(Transfer $transfer)
    {
        $this->_transfer = $transfer;
    }

    /**
     * Checks mimetypes and file extensions to be allowed to uploading
     * @return boolean
     */
    public function validate()
    {
        $isExtensionsValid = $this->checkExtensions();
        $isMimetypesValid = $this->checkMimeTypes();

        return $isExtensionsValid && $isMimetypesValid;
    }

    /**
     * Applies handler to the file. If something go wrong, returns false and
     * writes details to log file if set
     * 
     * @param string $fname file destination name
     * @param callable $action handler action
     * @return boolean operation result
     */
    public function handle($fname, $action)
    {
        try {
            $action($this, $fname);
        } catch (Exception $e) {
            if(!is_null($this->_transfer->logFile)) {
                $f = fopen($this->_transfer->logFile, 'a');
                fwrite($f, '[' . date('Y-m-d H:i:s') . '] in ' . $e->getFile()
                    .  ' on line ' . $e->getLine() 
                    . ': ' . $e->getMessage()
                    . "\n" . $e->getTrace() . "\n\n"
                );
                fclose($f);
            }
                
            return false;
        }

        return true;
    }

    /**
     * Check if this file's mimetype is allowed
     * @return boolean
     */
    protected function checkMimeTypes()
    {
        $mimeList = new MimeList(
            MimeList::USE_CACHE,
            $this->_transfer->mimeCacheFile
        );

        foreach ($this->_transfer->allowedExtensions as $extension) {
            $type = $mimeList->guess($extension);

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

    /**
     * Checks if this file's extension is allowed
     * @return boolean
     */
    protected function checkExtensions()
    {
        return in_array(
            pathinfo($this->name, PATHINFO_EXTENSION),
            $this->_transfer->allowedExtensions
        );
    }
}
