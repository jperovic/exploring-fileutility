<?php
    namespace Exploring\FileUtilityBundle\Data;

    class ImageDescriptor extends FileDescriptor
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
            parent::__construct($fileWrapper->getFile(), $fileWrapper->getDirectory());

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

        /**
         * @return array
         */
        public function getSizeAsArray()
        {
            return array($this->width, $this->height);
        }
    }