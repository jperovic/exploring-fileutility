<?php
    namespace Exploring\FileUtilityBundle\Data;

    use Symfony\Component\HttpFoundation\File\File;

    class FileWrapper
    {
        /**
         * @var File
         */
        private $file;

        /**
         * @var string
         */
        private $directoryAlias;

        /**
         * @param File|string $fileOrRealPath
         * @param string      $directoryAlias
         */
        public function __construct($fileOrRealPath, $directoryAlias)
        {
            $this->file = is_string($fileOrRealPath) ? new File($fileOrRealPath) : $fileOrRealPath;
            $this->directoryAlias = $directoryAlias;
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
        public function getDirectoryAlias()
        {
            return $this->directoryAlias;
        }

        /**
         * @return File
         */
        public function getFile()
        {
            return $this->file;
        }
    }