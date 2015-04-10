<?php

namespace FileTransfer;

class GottenFile
{
    protected $url = '';
    protected $_transfer;

    public function __construct(Transfer $transfer, $url = '')
    {
        $this->_transfer = $transfer;

        if (!empty($url) && realpath($url)) {
            $this->url = $url;
        } elseif (!is_null($this->_transfer->emptyFileReplacement)) {
            $this->url = $this->_transfer->emptyFileReplacement;
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'relativeUrl':
                return $this->_transfer->relativePath."/$url";
                break;
            case 'absoluteUrl':
                return $this->_transfer->absolutePath."/$url";
                break;
            default:
                throw new FileTransferException('Class `GottenFile` does not have'
                    ." variable with name `$name`");
        }
    }

    public function exists()
    {
        if ($this->hasReplacement()) {
            return $this->url !== $this->_transfer->emptyFileReplacement;
        } else {
            return !empty($this->url);
        }
    }

    public function remove()
    {
        if ($this->exists()) {
            ulink($this->absoluteUrl);
        } else {
            throw new FileTransferException('No file to remove');
        }
    }

    protected function hasReplacement()
    {
        return !is_null($this->_transfer->emptyFileReplacement);
    }
}
