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

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param File   $maskFile
         * @param bool   $keepOriginal
         *
         * @return FileWrapper
         */
        public abstract function clip(File $file, $saveToAlias, File $maskFile, $keepOriginal = false);

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $size
         * @param bool   $enlarge
         * @param bool   $keepOriginal
         *
         * @return string
         */
        public function scaleLargeEdge(File $file, $saveToAlias, $size, $enlarge = true, $keepOriginal = false)
        {
            $dim = $this->getImageSize($file->getRealPath());

            $landscape = $dim['width'] > $dim['height'];

            if ($landscape) {
                return $this->scale($file, $saveToAlias, $size, 0, $enlarge, $keepOriginal);
            } else {
                return $this->scale($file, $saveToAlias, 0, $size, $enlarge, $keepOriginal);
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
         * @param bool   $keepOriginal
         *
         * @return FileWrapper
         */
        public abstract function scale(File $file, $saveToAlias, $width, $height = 0, $enlarge = true, $keepOriginal = false);

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
        public abstract function crop(File $file, $saveToAlias, $x, $y, $width, $height, $keepOriginal = false);

        /**
         * @param FileManager $fileManager
         */
        public function setFileManager(FileManager $fileManager)
        {
            $this->fileManager = $fileManager;
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
