<?php

namespace FileTransfer;

class GottenFile
{
    protected $_url = '';
    protected $_transfer;

    public function __construct(Transfer $transfer, $url = '')
    {
        $this->_transfer = $transfer;

        if (!empty($url) && realpath($url)) {
            $this->_url = $url;
        } elseif (!is_null($this->_transfer->emptyFileReplacement)) {
            $this->_url = $this->_transfer->emptyFileReplacement;
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'relativeUrl':
                return $this->_transfer->relativePath."/{$this->_url}";
                break;
            case 'absoluteUrl':
                return $this->_transfer->absolutePath."/{$this->_url}";
                break;
            default:
                throw new FileTransferException('Class `GottenFile` does not have'
                    ." property named `$name`");
        }
    }

    public function exists()
    {
        if ($this->hasReplacement()) {
            return $this->_url !== $this->_transfer->emptyFileReplacement;
        } else {
            return !empty($this->_url);
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
