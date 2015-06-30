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

    public function run($id, $subdir, $handlerName)
    {
        $path = "{$this->_transfer->dir}/$subdir/".$this->getDirByFileId($id);

        return new $this->_gottenFileClass(
            $this->_transfer,
            "$path/$id"."_$handlerName.".pathinfo($file[0], PATHINFO_EXTENSION)
        );
    }

    protected function getDirByFileId($id)
    {
        return explode('_', $id)[0];
    }
}
