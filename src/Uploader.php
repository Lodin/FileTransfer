<?php

namespace FileTransfer;

class Uploader
{
    protected $_transfer;

    public function __construct(Transfer $transfer)
    {
        $this->_transfer = $transfer;
    }

    public function run(array $files, $subdir, array $userHandlers)
    {
        $this->checkData($userHandlers);

        $info = array();

        $files = $this->disassemble($files);

        // if there no allowed to upload files, function returns null;
        $hasAllowed = false;

        foreach ($files as $file) {
            if (!$file->validate()) {
                continue;
            }

            $hasAllowed = true;

            $dir = $this->buildPath($subdir);

            $info[$file->id]['sub'] = $this->subdir;
            $info[$file->id]['part'] = basename($dir);

            $filename = basename($dir).'_'.md5(microtime());
            $info[$file->id]['name'] = $filename;
            $info[$file->id]['ext'] = pathinfo($file->name, PATHINFO_EXTENSION);

            foreach ($userHandlers as $handler => $action) {
                $fname = "$dir/$filename"."_$handler.".pathinfo($file->name, PATHINFO_EXTENSION);

                if (!$file->moveHandled($fname, $this->_transfer->handlers[$handler])) {
                    unset($info[$file->id]);
                }
            }
        }

        if ($hasAllowed) {
            return $info;
        } else {
            return;
        }
    }

    protected function buildPath($subdir)
    {
        function countDir($dir)
        {
            return count(array_filter(
                scandir($dir),
                function ($file) use ($dir) {
                    if ($file != '.' && $file != '..' && !is_dir($dir.$file)) {
                        return $file;
                    }
                }
             ));
        }

        function scanDir($dir)
        {
            return array_filter(
                scandir($dir),
                function ($element) {
                    return ctype_digit(pathinfo($element, PATHINFO_FILENAME));
                }
             );
        }

        $dir = $this->_transfer->absolutePath."/{$this->_transfer->dir}";
        $dir .= "/$subdir";

        if (!is_dir($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }

        $folderlist = scanDir($dir);

        if (empty($folderlist)) {
            $result = "$dir/0";

            mkdir($result);
            chmod($result, 0777);
        } else {
            $result = "$dir/".max($folderlist);
        }

        if (countDir($result) >= $this->_transfer->filesInFolder) {
            $newDir = "$dir/".((int) basename($result) + 1);
            mkdir($newDir);
            chmod($newDir, 0777);

            return $newDir;
        }

        return $result;
    }

    protected function disassemble($files)
    {
        $disassembledFiles = TransferingFile::disassemble($files, true);

        foreach ($disassembledFiles as &$file) {
            $file->setOwner($this->_transfer);
        }

        return $disassembledFiles;
    }

    protected function checkData($userHandlers)
    {
        if (empty($userHandlers)) {
            throw new FileTransferException('Upload method should receive at'
                .' least one handler in `userHandlers` attribute');
        }

        foreach ($userHandlers as $handler => $action) {
            if (!isset($this->_transfer->handlers[$handler])) {
                throw new FileTransferException("Handler `$handler` is not defined");
            }
        }
    }
}
