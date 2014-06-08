<?php
    namespace Exploring\FileUtilityBundle\Service\Image;


    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\FilenameGenerator;
    use Symfony\Component\Debug\Exception\ClassNotFoundException;

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

        public static function get($engineName, FileManager $fileManager)
        {
            switch ($engineName) {
                case self::ENGINE_GD:
                    return new ImageProcessor(new GDImageEngine($fileManager));
                case self::ENGINE_IMAGICK:
                    return new ImageProcessor(new ImagickImageEngine($fileManager));
                default:
                    throw new ClassNotFoundException("Unknown image engine name: " . $engineName, null);
            }
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
         * @param string $directory
         * @param string $filename
         * @param int    $size
         *
         * @return string
         */
        public function scaleLargeEdge($filename, $directory, $size)
        {
            return $this->engine->scaleImage($filename, $directory, $size);
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
         * @param string $directory
         * @param string $filename
         * @param int    $width
         * @param int    $height
         *
         * @return string
         */
        public function scaleImage($filename, $directory, $width, $height = 0)
        {
            return $this->engine->scaleImage($filename, $directory, $width, $height);
        }

        /**
         * @return FileManager
         */
        public function getFileManager()
        {
            return $this->engine->getFileManager();
        }

        /**
         * @return FilenameGenerator
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