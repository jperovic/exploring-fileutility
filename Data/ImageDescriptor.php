<?php
    namespace Exploring\FileUtilityBundle\Data;

    class ImageDescriptor extends FileDescriptor
    {
        /** @var int */
        private $width;

        /** @var int */
        private $height;

        /**
         * @param FileDescriptor $descriptor
         * @param int         $width
         * @param int         $height
         */
        public function __construct(FileDescriptor $descriptor, $width, $height)
        {
            parent::__construct($descriptor->getFile(), $descriptor->getDirectory());

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