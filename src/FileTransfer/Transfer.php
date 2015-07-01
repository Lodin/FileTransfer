<?php

namespace FileTrasfer;

/**
 * Implements common file transfering functional.
 */
class Transfer
{
    /**
     * Site root absolute path.
     *
     * @var string
     */
    protected $absolutePath = '';

    /**
     * Site root relative path.
     *
     * @var string
     */
    protected $relativePath = '';

    /**
     * Name of main file directory from site root.
     *
     * @var string
     */
    protected $dir = '';

    /**
     * List of file extensions allowed to upload.
     *
     * @var array
     */
    protected $allowedExtensions = array();

    /**
     * List of actions to do with file after uploading. Should have next
     * signature: function(SimpleFile $file, string $fname). For keys it is
     * preferable to be string handler names.
     *
     * @var array
     */
    protected $handlers = array();

    /**
     * Maximum count of files in single folder. After achieving this step a new
     * folder will be create.
     *
     * @var int
     */
    protected $filesInFolder = 50;

    /**
     * Name of file from site root to cache mime data of MimeList plugin.
     *
     * @var string
     */
    protected $mimeCacheFile = '';

    /**
     * Name of file returning if requested file does not exist (e.g. image
     * placeholder). System will throw exception if null and requested file
     * does not exist.
     *
     * @var string
     */
    protected $emptyFileReplacement = null;

    /**
     * Name of class extending GottenFile which is a wrapper over file returned
     * by file getting system.
     *
     * @var string
     */
    protected $gottenFileClass = 'GottenFile';

    /**
     * Name of file to log errors.
     *
     * @var string
     */
    protected $logFile = null;

    protected $uploader;
    protected $getter;

    /**
     * Creates an instance of Transfer class.
     *
     * @param array $settings contains list of settings similar to class
     *                        parameters
     *
     * @throws FileTransferException if some parameters is not set
     */
    public function __contstruct(array $settings)
    {
        if (isset($settings['absolutePath'])) {
            $this->absolutePath = $settings['absolutePath'];
        } else {
            throw new FileTransferException('Parameter `absolutePath` should be defined');
        }

        if (isset($settings['relativePath'])) {
            $this->relativePath = $settings['relativePath'];
        } else {
            throw new FileTransferException('Parameter `relativePath` should be defined');
        }

        if (isset($settings['dir'])) {
            $this->dir = $settings['dir'];
        } else {
            throw new FileTransferException('Parameter `dir` should be defined');
        }

        if (isset($settings['allowedExtensions'])) {
            if (!is_array($settings['allowedExtensions'])
                || !is_string(current($settings['allowedExtensions']))) {
                throw new FileTransferException('Parameter `allowedExtensions`'
                    .' should be array of string');
            }

            $this->allowedExtensions = $settings['allowedExtensions'];
        }

        if (isset($settings['handlers'])) {
            if (!is_array($settings['handlers'])) {
                throw new FileTransferException('Parameter `handlers` should be array');
            }

            foreach ($settings['handlers'] as $type => $action) {
                if (!is_string($type) || !is_callable($action)) {
                    throw new FileTransferException('Property `handlers` should'
                        .' be an array(string => callable)');
                }
            }

            $this->handlers = $settings['handlers'];
        } else {
            throw new FileTransferException('Parameter `handlers` should be defined');
        }

        if (isset($settings['filesInFolder'])) {
            if (!is_integer($settings['filesInFolder'])) {
                throw new FileTransferException('Parameter `filesInFolder` should'
                .' be integer');
            }

            if ($this->filesInFolder <= 0) {
                throw new FileTransferException('At least one file should be'
                    .' in folder');
            }

            $this->filesInFolder = $settings['filesInFolder'];
        }

        if (isset($settings['mimeCacheFile'])) {
            if (!is_string($settings['mimeCacheFile'])) {
                throw new FileTransferException('Parameter `mimeCacheFile`'
                    .' should be a string');
            }

            $this->mimeCacheFile = $settings['mimeCacheFile'];
        } else {
            throw new FileTransferException('Parameter `mimeCacheFile` should'
                .' be defined');
        }

        if (isset($settings['emptyFileReplacement'])
            && $this->emptyFileReplacement !== $settings['emptyFileReplacement']) {
            if (!realpath($settings['emptyFileReplacement'])) {
                throw new FileTransferException("File `{$settings['emptyFileReplacement']}`"
                    .' does not exist');
            }

            $this->emptyFileReplacement = realpath($settings['emptyFileReplacement']);
        }

        if (isset($settings['gottenFileClass'])) {
            if (!class_exists($settings['gottenFileClass'])) {
                throw new FileTransferException('Class with name'
                    ." `{$settings['gottenFileClass']}` does not find");
            }

            $this->gottenFileClass = $settings['gottenFileClass'];
        }

        if (isset($settings['logFile']) && $this->logFile !== $settings['logFile']) {
            if (!file_exists($settings['logFile'])) {
                throw new FileTransferException("File {$settings['logFile']}"
                .' does not exist');
            }

            $this->logFile = $settings['logFile'];
        }

        $this->uploader = new Uploader($this);
        $this->getter = new Getter($this, $this->gottenFileClass);
    }

    public function __get($name)
    {
        if (!isset($this->$name)) {
            throw new FileTransferException("No property with name `$name` found");
        }

        return $this->$name;
    }

    /**
     * Starts file uploading.
     *
     * @param array  $files        $_FILES array
     * @param string $subdir       subdirectory in the uploading files directory
     *                             to separate uploading files each from other
     * @param array  $userHandlers list of handlers names
     */
    public function upload(array $files, $subdir, array $userHandlers)
    {
        $this->uploader->run($files, $subdir, $userHandlers);
    }

    /**
     * Returns file by it's ID.
     *
     * @param string $code       file code (filename created at upload
     *                           operation)
     * @param string $subdir     subdirectory in the uploading files directory
     *                           to separate uploading files by user defined
     *                           types
     * @param string $handler    name of handler applied to file on uploading
     */
    public function get($code, $subdir, $handler)
    {
        $this->getter->run($code, $subdir, $handler);
    }

    /**
     * Returns list of allowed handler names.
     *
     * @return array
     */
    public function allowedHandlers()
    {
        return array_keys($this->handlers);
    }
}
