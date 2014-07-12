<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Data\ImageWrapper;
    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Service\Image\Chains\Executor;
    use Symfony\Component\HttpFoundation\File\File;

    /**
     * Class ImageProcessor
     * @package Exploring\FileUtilityBundle\Service\Image
     *
     * TODO: rotate
     */
    class ImageProcessor
    {
        const ENGINE_GD = "gd";

        const ENGINE_IMAGICK = "imagick";

        /** @var FileManager */
        private $fileManager;

        /** @var AbstractImageEngine */
        private $engine;

        /**
         * @var Executor
         */
        private $chainExecutor;

        function __construct(FileManager $fileManager, AbstractImageEngine $engine, Executor $chainExecutor = null)
        {
            $this->fileManager = $fileManager;
            $this->engine = $engine;
            $this->engine->setFileManager($fileManager);
            $this->chainExecutor = $chainExecutor;
            $this->chainExecutor->setProcessor($this);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param File   $maskFile
         * @param bool   $keepOriginal
         *
         * @return FileWrapper
         */
        public function clip(File $file, $saveToAlias, File $maskFile, $keepOriginal = false)
        {
            return $this->engine->clip($file, $saveToAlias, $maskFile, $keepOriginal);
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
         * @param bool   $keepOriginal
         *
         * @return FileWrapper
         */
        public function scaleLargeEdge(File $file, $saveToAlias, $size, $enlarge = true, $keepOriginal = false)
        {
            return $this->engine->scaleLargeEdge($file, $saveToAlias, $size, $enlarge, $keepOriginal);
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
         * @param bool   $keepOriginal
         *
         * @return FileWrapper
         */
        public function scale(File $file, $saveToAlias, $width, $height = 0, $enlarge = true, $keepOriginal = false)
        {
            return $this->engine->scale($file, $saveToAlias, $width, $height, $enlarge, $keepOriginal);
        }

        /**
         * @param File        $file
         * @param string      $chainName
         * @param string|null $saveToAlias
         *
         * @return ImageWrapper
         */
        public function applyChain(File $file, $chainName, $saveToAlias = null)
        {
            return $this->chainExecutor->execute($file, $chainName, $saveToAlias);
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