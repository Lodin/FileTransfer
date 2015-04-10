<?php

namespace FileTransfer;

class Getter
{
    protected $_transfer;
    protected $_gottenFileClass;

    public function __construct(Transfer $transfer, $gottenFileClass)
    {
        $this->_transfer = $transfer;
        $this->_returnClass = $gottenFileClass;
    }

    public function run($id, $subdir, $handler)
    {
        $path = "{$this->_transfer->dir}/$subdir/".$this->getDirByFileId($id);

        return new $this->_gottenFileClass(
            $this->_transfer,
            "$path/$id"."_$handler.".pathinfo($file[0], PATHINFO_EXTENSION)
        );
    }

    protected function getDirByFileId($id)
    {
        return explode('_', $id)[0];
    }
}
