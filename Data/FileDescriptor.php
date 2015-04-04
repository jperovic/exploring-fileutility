<?php
    namespace Exploring\FileUtilityBundle\Data;

    use Symfony\Component\HttpFoundation\File\File;

    class FileDescriptor
    {
        /**
         * @var File
         */
        private $file;

        /**
         * @var string
         */
        private $directory;

        /**
         * @param File|string $fileOrRealPath
         * @param string      $directory
         */
        public function __construct($fileOrRealPath, $directory)
        {
            $this->file = is_string($fileOrRealPath) ? new File($fileOrRealPath) : $fileOrRealPath;
            $this->directory = $directory;
        }

        /**
         * @return null|string
         */
        public function getRealPath()
        {
            return $this->file->getRealPath();
        }

        /**
         * @return null|string
         */
        public function getFileName()
        {
            return $this->file->getFilename();
        }

        /**
         * @return string
         */
        public function getDirectory()
        {
            return $this->directory;
        }

        /**
         * @return File
         */
        public function getFile()
        {
            return $this->file;
        }
    }