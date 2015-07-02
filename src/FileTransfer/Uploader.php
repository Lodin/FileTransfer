<?php

namespace FileTransfer;

use FileTransfer\Wrappers\UploadedFileInfo;
use FileTransfer\Wrappers\TransferingFile;

/**
 * Implemets file uploading action.
 */
class Uploader
{
    protected $_transfer;

    /**
     * Creates a new implementation of Uploader class.
     *
     * @param \FileTransfer\Transfer $transfer
     */
    public function __construct(Transfer $transfer)
    {
        $this->_transfer = $transfer;
    }

    /**
     * Starts file uploading.
     *
     * @param array  $unhandledFiles $_FILES array
     * @param string $subdir         subdirectory in the uploading files directory to
     *                               separate uploading files each from other
     * @param array  $userHandlers   list of handler names
     *
     * @return array|null if there are allowed files returns array with
     *                    uploaded files info
     */
    public function run(array $unhandledFiles, $subdir, array $userHandlers)
    {
        $this->checkData($userHandlers);

        $info = array();

        $files = $this->disassemble($unhandledFiles);

        // if there no allowed to upload files, function returns null;
        $hasAllowed = false;

        foreach ($files as $file) {
            if (!$file->validate()) {
                continue;
            }

            $hasAllowed = true;

            $dir = $this->buildPath($subdir);

            $filename = basename($dir).'_'.md5(microtime());

            // TODO: improve algorithm if no handlers is set (now even
            // move_uploaded_files is not provided)
            $isHandled = true;
            foreach ($userHandlers as $handler) {
                $fname = "$dir/$filename"."_$handler.".pathinfo($file->name, PATHINFO_EXTENSION);

                if (!$file->handle($fname, $this->_transfer->handlers[$handler])) {
                    $isHandled = false;
                }
            }

            if (!$isHandled) {
                continue;
            }

            $info[] = new UploadedFileInfo(
                $subdir,
                basename($dir),
                $filename,
                pathinfo($file->name, PATHINFO_EXTENSION)
            );
        }

        if ($hasAllowed) {
            return $info;
        } else {
            return;
        }
    }

    /*
     * Creates a path to upload file. It looks like:
     * /images/avatars/1/
     *  ^        ^     ^
     * dir    subdir   part (max files limited by $filesInFolder parameter)
     *
     * @param string $subdir subdirectory name
     * @return created path
     */
    protected function buildPath($subdir)
    {
        $dir = $this->_transfer->absolutePath."/{$this->_transfer->dir}";
        $dir .= "/$subdir";

        if (!is_dir($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }

        $folderlist = array_filter(scandir($dir), function ($element) {
            return ctype_digit(pathinfo($element, PATHINFO_FILENAME));
        });

        if (empty($folderlist)) {
            $result = "$dir/0";

            mkdir($result);
            chmod($result, 0777);
        } else {
            $result = "$dir/".max($folderlist);
        }

        
        $countDir = count(array_filter(scandir($dir), function ($file) use ($dir) {
            if ($file != '.' && $file != '..' && !is_dir($dir.$file)) {
                return $file;
            }
        }));
        
        if ($countDir >= $this->_transfer->filesInFolder) {
            $newDir = "$dir/".((int) basename($result) + 1);
            mkdir($newDir);
            chmod($newDir, 0777);

            return $newDir;
        }

        return $result;
    }

    /*
     * Disassembles $_FILES array to TransferingFile instances
     * @param array $files
     * @return array disassembled files array
     */
    protected function disassemble($files)
    {
        $disassembledFiles = TransferingFile::disassemble($files, true);

        foreach ($disassembledFiles as &$file) {
            $file->setOwner($this->_transfer);
        }

        return $disassembledFiles;
    }

    /*
     * Checks $userHandlers
     * @param array $userHandlers
     * @throws FileTransferException if checking is gone wrong
     */
    protected function checkData($userHandlers)
    {
        // TODO: remove and make default handler to move uploaded files
        if (empty($userHandlers)) {
            throw new FileTransferException('Upload method should receive at'
                .' least one handler in `userHandlers` attribute');
        }

        foreach ($userHandlers as $handler) {
            if (!is_string($handler)) {
                throw new FileTransferException('All handlers names in'
                    .' `userHandlers` list should be a string');
            }

            if (!isset($this->_transfer->handlers[$handler])) {
                throw new FileTransferException("Handler `$handler` is not defined");
            }
        }
    }
}
