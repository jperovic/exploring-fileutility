<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Symfony\Component\HttpFoundation\File\File;

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

        public function __construct(FileManager $fileManager)
        {
            $this->fileManager = $fileManager;
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param File   $maskFile
         *
         * @return FileWrapper
         */
        public abstract function clipImage(File $file, $saveToAlias, File $maskFile);

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
            $dim = $this->getImageSize($this->fileManager->getAbsolutePath($filename, $directoryAlias));

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
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         *
         * @return FileWrapper
         */
        public abstract function scaleImage(File $file, $saveToAlias, $width, $height = 0, $enlarge = true);

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
