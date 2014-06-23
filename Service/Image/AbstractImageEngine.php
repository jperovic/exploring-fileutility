<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\DefaultFilenameGenerator;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\FilenameGeneratorInterface;

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
         * @var FilenameGeneratorInterface
         */
        protected $fileNameGenerator;

        public function __construct(FileManager $fileManager)
        {
            $this->fileManager = $fileManager;

            $this->fileNameGenerator = new DefaultFilenameGenerator();
        }

        /**
         * @param string $directoryAlias
         * @param string $filename
         * @param string $mask_path
         *
         * @return string
         */
        public abstract function clipImage($filename, $directoryAlias, $mask_path);

        /**
         * @param string $filename
         * @param string $directoryAlias
         * @param int    $size
         * @param bool   $enlarge
         *
         * @return string
         */
        public function scaleLargeEdge($filename, $directoryAlias, $size, $enlarge = true)
        {
            $dim = $this->getImageSize($filename);

            $landscape = $dim['width'] > $dim['height'];

            if ($landscape) {
                return $this->scaleImage($filename, $directoryAlias, $size, 0, $enlarge);
            } else {
                return $this->scaleImage($filename, $directoryAlias, 0, $size, $enlarge);
            }
        }

        /**
         * @param string $filename
         *
         * @return int[]
         */
        public abstract function getImageSize($filename);

        /**
         * @param string $filename
         * @param string $directoryAlias
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         *
         * @return string
         */
        public abstract function scaleImage($filename, $directoryAlias, $width, $height = 0, $enlarge = true);

        /**
         * @return FileManager
         */
        public function getFileManager()
        {
            return $this->fileManager;
        }

        /**
         * @return FilenameGeneratorInterface
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
