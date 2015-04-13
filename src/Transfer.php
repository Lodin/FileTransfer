<?php

namespace FileTrasfer;

class Transfer
{
    protected $absolutePath = '';
    protected $relativePath = '';
    protected $dir = '';
    protected $allowedExtensions = array();
    protected $types = array();
    protected $filesInFolder = 50;
    protected $mimeCacheFile = '';
    protected $emptyFileReplacement = null;
    protected $gottenFileClass = 'GottenFile';
    protected $logFile = null;

    protected $uploader;
    protected $getter;

    public function __contstruct(array $settings)
    {
        if (isset($settings['absolutePath'])) {
            $this->relativePath = $settings['absolutePath'];
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

        if (isset($settings['types'])) {
            if (!is_array($settings['types'])) {
                throw new FileTransferException('Parameter `types` should be array');
            }

            foreach( $settings['types'] as $type => $action )
            {
                if(!is_string($type) || !is_callable($action))
                    throw new FileTransferException('Property `types` should'
                        .' be an array(string => callable)');
            }

            $this->types = $settings['types'];
        } else {
            throw new FileTransferException('Parameter `types` should be defined');
        }

        if (isset($settings['filesInFolder'])) {
            if (!is_integer($settings['filesInFolder'])) {
                throw new FileTransferException('Parameter `filesInFolder` should'
                .' be integer');
            }

            if( $this->filesInFolder <= 0 ) {
                throw new FileTransferException( 'At least one file should be'
                    . ' in folder' );
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

        if(isset($settings['gottenFileClass'])) {
            if(!class_exists($settings['gottenFileClass']))
                throw new FileTransferException("Class with name"
                    ." `{$settings['gottenFileClass']}` does not find");

            $this->gottenFileClass = $settings['gottenFileClass'];
        }
        
        if(isset($settings['logFile']) && $this->logFile !== $settings['logFile']) {
            if(!file_exists($settings['logFile'])) {
                throw new FileTransferException("File {$settings['logFile']}"
                . ' does not exist');
            }
            
            $this->logFile = $settings['logFile'];
        }

        $this->uploader = new Uploader($this);
        $this->getter = new Getter($this, $this->gottenFileClass);
    }

    public function __get($name)
    {
        if(!isset($this->$name)) {
            throw new FileTransferException("No property with name `$name` found");
        }

        return $this->$name;
    }

    public function upload(array $files, $subdir, array $userTypes)
    {
        $this->uploader->run($files, $subdir, $userTypes);
    }

    public function get($id, $subdir, $size, $isAbsolute = false)
    {
        $this->getter->run($id, $subdir, $size, $isAbsolute);
    }

    public function allowedTypes()
    {
        return array_keys($this->types);
    }
}
