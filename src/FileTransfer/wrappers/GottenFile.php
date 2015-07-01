<?php

namespace FileTransfer\wrappers;

use FileTransfer\Transfer;
use FileTransfer\FileTransferException;

/**
 * Implements a wrapper around file uri returned by file getting system.
 */
class GottenFile
{
    protected $_url = '';
    protected $_transfer;

    /**
     * Creates new GottenFile instance.
     *
     * @param Transfer $transfer
     * @param string   $url
     */
    public function __construct(Transfer $transfer, $url = '')
    {
        $this->_transfer = $transfer;

        if ((!empty($url) && realpath($url)) || $this->hasReplacement()) {
            $this->_url = $url;
        } else {
            throw new FileTransferException('Requested file does not exist');
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'url': {
                if($this->exists()) {
                    return "{$this->_transfer->relativePath}/{$this->_url}";
                } elseif ($this->hasReplacement()) {
                    return "{$this->_transfer->relativePath}/{$this->_transfer->emptyFileReplacement}";
                }
            }
            case 'absoluteUrl': {
                if($this->exists()) {
                    return "{$this->_transfer->absolutePath}/{$this->_url}";
                } elseif ($this->hasReplacement()) {
                    return "{$this->_transfer->absolutePath}/{$this->_transfer->emptyFileReplacement}";
                }
            }
            default: {
                throw new FileTransferException('Class `GottenFile` does not have'
                    ." property named `$name`");
            }
        }
        
        return false;
    }

    /**
     * Tests file to be exist.
     *
     * @return boolean
     */
    public function exists()
    {
        return !empty($this->_url);
    }
    
    /**
     * Tests file to be placeholder instead of requested file
     * 
     * @return boolean
     */
    public function isPlaceholder()
    {
        return !$this->exists() && $this->hasReplacement();
    }

    /**
     * Removes file.
     *
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
