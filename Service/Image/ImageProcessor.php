<?php
    namespace Exploring\FileUtilityBundle\Service\Image;


    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\FilenameGeneratorInterface;

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
         * @param string $directory
         * @param string $filename
         * @param string $mask_path
         *
         * @return string
         */
        public function clipImage($filename, $directory, $mask_path)
        {
            return $this->engine->clipImage($filename, $directory, $mask_path);
        }

        /**
         * @param string $filename
         * @param string $directory
         * @param int    $size
         * @param bool   $enlarge
         *
         * @return string
         */
        public function scaleLargeEdge($filename, $directory, $size, $enlarge = true)
        {
            return $this->engine->scaleLargeEdge($filename, $directory, $size, $enlarge);
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
         * @param string $filename
         * @param string $directory
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         *
         * @return string
         */
        public function scaleImage($filename, $directory, $width, $height = 0, $enlarge = true)
        {
            return $this->engine->scaleImage($filename, $directory, $width, $height, $enlarge);
        }

        /**
         * @return FileManager
         */
        public function getFileManager()
        {
            return $this->engine->getFileManager();
        }

        /**
         * @return FilenameGeneratorInterface
         */
        public function getFileNameGenerator()
        {
            return $this->engine->getFileNameGenerator();
        }

        /**
         * @return $this
         */
        public function commit()
        {
            return $this->engine->commit();
        }

        /**
         * @param bool $onlyLastTransation
         *
         * @return $this
         */
        public function rollback($onlyLastTransation = false)
        {
            return $this->engine->rollback($onlyLastTransation);
        }
    }