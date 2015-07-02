<?php

namespace FileTransfer;

/**
 * Implements main FileTransfer exception.
 */
class FileTransferException extends \Exception
{
    public function __construct($message, $code = 1, $previous = null)
    {
        parent::__construct("FileTransfer Exception: $message", $code, $previous);
    }
}
