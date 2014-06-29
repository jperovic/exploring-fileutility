<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Symfony\Component\HttpFoundation\File\File;

    /**
     * Class ImageProcessor
     * @package Exploring\FileUtilityBundle\Service\Image
     *
     * TODO: crop
     * TODO: rotate
     *
     */
    class ImageProcessor
    {
        const ENGINE_GD = "gd";

        const ENGINE_IMAGICK = "imagick";

        /** @var FileManager */
        private $fileManager;

        /** @var AbstractImageEngine */
        private $engine;

        function __construct(FileManager $fileManager, AbstractImageEngine $engine)
        {
            $this->fileManager = $fileManager;
            $this->engine = $engine;
            $this->engine->setFileManager($fileManager);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param File   $maskFile
         *
         * @return FileWrapper
         */
        public function clip(File $file, $saveToAlias, File $maskFile)
        {
            return $this->engine->clip($file, $saveToAlias, $maskFile);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $x
         * @param int    $y
         * @param int    $width
         * @param int    $height
         * @param bool   $keepOriginal
         *
         * @return mixed
         */
        public function crop(File $file, $saveToAlias, $x, $y, $width, $height, $keepOriginal = false)
        {
            return $this->engine->crop($file, $saveToAlias, $x, $y, $width, $height, $keepOriginal);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $size
         * @param bool   $enlarge
         *
         * @return FileWrapper
         */
        public function scaleLargeEdge(File $file, $saveToAlias, $size, $enlarge = true)
        {
            return $this->engine->scaleLargeEdge($file, $saveToAlias, $size, $enlarge);
        }

        /**
         * @param string $filename
         *
         * @return int[]
         */
        public function getImageSize($filename)
        {
            return $this->engine->getImageSize($filename);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         *
         * @return FileWrapper
         */
        public function scale(File $file, $saveToAlias, $width, $height = 0, $enlarge = true)
        {
            return $this->engine->scale($file, $saveToAlias, $width, $height, $enlarge);
        }

        /**
         * @return FileManager
         */
        public function getFileManager()
        {
            return $this->fileManager;
        }

        /**
         * @return $this
         */
        public function commit()
        {
            $this->fileManager->commit();

            return $this;
        }

        /**
         * @param bool $onlyLastTransation
         *
         * @return $this
         */
        public function rollback($onlyLastTransation = false)
        {
            $this->fileManager->rollback($onlyLastTransation);

            return $this;
        }
    }