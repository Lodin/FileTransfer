<?php

namespace FileTransfer;

class Getter
{
    protected $_transfer;
    protected $_gottenFileClass;

    public function __construct(Transfer $transfer, $gottenFileClass)
    {
        if(!is_subclass_of($gottenFileClass, 'GottenFile'))
            throw new FileTransferException("`$gottenFileClass` should be an"
                .' an instance of `GottenFile`');

        $this->_transfer = $transfer;
        $this->_gottenFileClass = $gottenFileClass;
    }

    public function run($id, $subdir, $type)
    {
        $path = "{$this->_transfer->dir}/$subdir/".$this->getDirByFileId($id);

        return new $this->_gottenFileClass(
            $this->_transfer,
            "$path/$id"."_$type.".pathinfo($file[0], PATHINFO_EXTENSION)
        );
    }

    protected function getDirByFileId($id)
    {
        return explode('_', $id)[0];
    }
}
