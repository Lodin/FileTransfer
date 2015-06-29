<?php

namespace FileTransfer\Wrappers;

use FileTransfer\Transfer;
use FileTransfer\FileTransferException;

/**
 * Implements a wrapper around file uri returned by file getting system
 */
class GottenFile
{
    protected $_url = '';
    protected $_transfer;

    /**
     * Creates new GottenFile instance
     * @param Transfer $transfer
     * @param string $url
     */
    public function __construct(Transfer $transfer, $url = '')
    {
        $this->_transfer = $transfer;

        if (!empty($url) && realpath($url)) {
            $this->_url = $url;
        } elseif (!$this->hasReplacement()) {
            $this->_url = $this->_transfer->emptyFileReplacement;
        } else {
            throw new FileTransferException('Requested file does not exist');
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'relativeUrl':
                return $this->_transfer->relativePath."/{$this->_url}";
            case 'absoluteUrl':
                return $this->_transfer->absolutePath."/{$this->_url}";
            default:
                throw new FileTransferException('Class `GottenFile` does not have'
                    ." property named `$name`");
        }
    }

    /**
     * Tests file to be exist
     * @return boolean
     */
    public function exists()
    {
        if ($this->hasReplacement()) {
            return $this->_url !== $this->_transfer->emptyFileReplacement;
        } else {
            return !empty($this->_url);
        }
    }

    /**
     * Removes file
     * @throws FileTransferException if file to remove does not exist
     */
    public function remove()
    {
        if ($this->exists()) {
            ulink($this->absoluteUrl);
        } else {
            throw new FileTransferException('No file to remove');
        }
    }

    /*
     * Tests file to has replacement
     * @return boolean
     */
    protected function hasReplacement()
    {
        return !is_null($this->_transfer->emptyFileReplacement);
    }
}
