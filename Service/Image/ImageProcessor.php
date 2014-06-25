<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Symfony\Component\HttpFoundation\File\File;

    class ImageProcessor
    {
        const ENGINE_GD = "gd";

        const ENGINE_IMAGICK = "imagick";

        /** @var AbstractImageEngine */
        private $engine;

        function __construct(AbstractImageEngine $engine)
        {
            $this->engine = $engine;
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param File   $maskFile
         *
         * @return FileWrapper
         */
        public function clipImage(File $file, $saveToAlias, File $maskFile)
        {
            return $this->engine->clipImage($file, $saveToAlias, $maskFile);
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
        public function scaleImage(File $file, $saveToAlias, $width, $height = 0, $enlarge = true)
        {
            return $this->engine->scaleImage($file, $saveToAlias, $width, $height, $enlarge);
        }

        /**
         * @return FileManager
         */
        public function getFileManager()
        {
            return $this->engine->getFileManager();
        }

        /**
         * @return $this
         */
        public function commit()
        {
            $this->getFileManager()->commit();

            return $this;
        }

        /**
         * @param bool $onlyLastTransation
         *
         * @return $this
         */
        public function rollback($onlyLastTransation = false)
        {
            $this->getFileManager()->rollback($onlyLastTransation);

            return $this;
        }
    }