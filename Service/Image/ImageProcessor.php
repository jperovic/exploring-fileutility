<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Data\ImageWrapper;
    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Service\Image\Chains\Executor;
    use Symfony\Component\HttpFoundation\File\File;

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
            if ( $chainExecutor ){
                $this->chainExecutor->setProcessor($this);
            }
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param File   $maskFile
         * @param bool   $keepSourceFile
         *
         * @return ImageWrapper
         */
        public function clip(File $file, $saveToAlias, File $maskFile, $keepSourceFile = false)
        {
            return $this->engine->clip($file, $saveToAlias, $maskFile, $keepSourceFile);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $x
         * @param int    $y
         * @param int    $width
         * @param int    $height
         * @param bool   $keepSourceFile
         *
         * @return mixed
         */
        public function crop(File $file, $saveToAlias, $x, $y, $width, $height, $keepSourceFile = false)
        {
            return $this->engine->crop($file, $saveToAlias, $x, $y, $width, $height, $keepSourceFile);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $size
         * @param bool   $enlarge
         * @param bool   $keepSourceFile
         *
         * @return FileWrapper
         */
        public function scaleLargeEdge(File $file, $saveToAlias, $size, $enlarge = true, $keepSourceFile = false)
        {
            return $this->engine->scaleLargeEdge($file, $saveToAlias, $size, $enlarge, $keepSourceFile);
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
         * @param bool   $keepSourceFile
         *
         * @return FileWrapper
         */
        public function scale(File $file, $saveToAlias, $width, $height = 0, $enlarge = true, $keepSourceFile = false)
        {
            return $this->engine->scale($file, $saveToAlias, $width, $height, $enlarge, $keepSourceFile);
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