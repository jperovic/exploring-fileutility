<?php
    namespace Exploring\FileUtilityBundle\Data;

    class File
    {
        /**
         * @var string
         */
        private $name;

        /**
         * @var string
         */
        private $extension;

        /**
         * @var string
         */
        private $directoryAlias;

        /**
         * @var string
         */
        private $mimeType;

        /**
         * @var int
         */
        private $size;

        /**
         * @param string $name
         * @param string $extension
         * @param string $directoryAlias
         * @param string $mimeType
         * @param int    $size
         */
        function __construct($name, $extension, $directoryAlias, $mimeType, $size)
        {
            $this->name = $name;
            $this->extension = $extension;
            $this->directoryAlias = $directoryAlias;
            $this->mimeType = $mimeType;
            $this->size = $size;
        }

        /**
         * @return string
         */
        public function getDirectoryAlias()
        {
            return $this->directoryAlias;
        }

        /**
         * @return string
         */
        public function getMimeType()
        {
            return $this->mimeType;
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @return int
         */
        public function getSize()
        {
            return $this->size;
        }

        /**
         * @return string
         */
        public function getExtension()
        {
            return $this->extension;
        }
    }