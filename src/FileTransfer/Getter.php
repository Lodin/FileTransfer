<?php

namespace FileTransfer;

/**
 * Implements file getting system.
 */
class Getter
{
    protected $_transfer;
    protected $_gottenFileClass;

    /**
     * Creates new instance of Getter class.
     *
     * @param Transfer $transfer
     * @param string   $gottenFileClass name of class extending GottenFile
     *
     * @throws FileTransferException if gottenFileClass is not a name of class
     *                               extending GottenFile
     */
    public function __construct(Transfer $transfer, $gottenFileClass)
    {
        if (!is_subclass_of($gottenFileClass, 'GottenFile')) {
            throw new FileTransferException("`$gottenFileClass` should be an"
                .' an instance of `GottenFile`');
        }

        $this->_transfer = $transfer;
        $this->_gottenFileClass = $gottenFileClass;
    }
    
    /**
     * 
     * @param string $code code of image created at uploading
     * @param string $subdir subdirectory in the uploading files directory to
     *                       separate uploading files by user defined types
     * @param string $handlerName name of handler used at uploaded operation
     * @return GottenFile wrapper over file link
     */
    public function run($code, $subdir, $handlerName)
    {
        $path = "{$this->_transfer->dir}/$subdir/{$this->getDirByFileId($code)}/$code"."_$handlerName";
        $found = glob("{$this->_transfer->absolutePath}/$path.*");
        
        return new $this->_gottenFileClass(
            $this->_transfer,
            !empty($found)? "$path.".pathinfo($found[0], PATHINFO_EXTENSION) : ''
        );
    }

    /*
     * Gets name of directory limited by `$filesInFolder` property
     * 
     * @param string $code filename generated at uploading operation
     * @return integer
     */
    protected function getDirByFileId($code)
    {
        $data = explode('_', $code);
        return (int)$data[0];
    }
}
