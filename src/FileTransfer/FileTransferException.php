<?php

namespace FileTransfer;

class FileTransferException extends Exception
{
    public function __construct($message, $code = 1, $previous = null)
    {
        parent::__construct("FileTransfer Exception: $message", $code, $previous);
    }
}
