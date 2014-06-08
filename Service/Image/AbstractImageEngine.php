<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\DefaultFilenameGenerator;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\FilenameGenerator;

    /**
     * Created by JetBrains PhpStorm.
     * User: root
     * Date: 2/10/13
     * Time: 4:24 AM
     * To change this template use File | Settings | File Templates.
     */
    abstract class AbstractImageEngine
    {
        /**
         * @var FileManager
         */
        protected $fileManager;

        /**
         * @var FilenameGenerator
         */
        protected $fileNameGenerator;

        public function __construct($fileManager)
        {
            $this->fileManager = $fileManager;

            $this->fileNameGenerator = new DefaultFilenameGenerator();
        }

        /**
         * @param string $directory
         * @param string $filename
         * @param string $mask_path
         *
         * @return string
         */
        public abstract function clipImage($filename, $directory, $mask_path);

        /**
         * @param string $directory
         * @param string $filename
         * @param int    $size
         *
         * @return string
         */
        public function scaleLargeEdge($filename, $directory, $size)
        {
            $dim = $this->getImageSize($filename);

            $landscape = $dim['width'] > $dim['height'];

            if ($landscape) {
                return $this->scaleImage($filename, $directory, $size, 0);
            }
            else {
                return $this->scaleImage($filename, $directory, 0, $size);
            }
        }

        /**
         * @param string $filename
         *
         * @return int[]
         */
        public abstract function getImageSize($filename);

        /**
         * @param string $directory
         * @param string $filename
         * @param int    $width
         * @param int    $height
         *
         * @return string
         */
        public abstract function scaleImage($filename, $directory, $width, $height = 0);

        /**
         * @return FileManager
         */
        public function getFileManager()
        {
            return $this->fileManager;
        }

        /**
         * @return FilenameGenerator
         */
        public function getFileNameGenerator()
        {
            return $this->fileNameGenerator;
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
