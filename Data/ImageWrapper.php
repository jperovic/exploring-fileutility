<?php
    namespace Exploring\FileUtilityBundle\Data;

    class ImageWrapper extends FileWrapper
    {
        /** @var int */
        private $width;

        /** @var int */
        private $height;

        /**
         * @param FileWrapper $fileWrapper
         * @param int         $width
         * @param int         $height
         */
        public function __construct(FileWrapper $fileWrapper, $width, $height)
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