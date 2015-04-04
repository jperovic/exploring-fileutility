<?php
    namespace Exploring\FileUtilityBundle\Data;

    class ImageWrapper extends FileDescriptor
    {
        /** @var int */
        private $width;

        /** @var int */
        private $height;

        /**
         * @param FileDescriptor $fileWrapper
         * @param int         $width
         * @param int         $height
         */
        public function __construct(FileDescriptor $fileWrapper, $width, $height)
        {
            parent::__construct($fileWrapper->getFile(), $fileWrapper->getDirectoryAlias());

            $this->width = $width;
            $this->height = $height;
        }

        /**
         * @return int
         */
        public function getHeight()
        {
            return $this->height;
        }

        /**
         * @return int
         */
        public function getWidth()
        {
            return $this->width;
        }
    }