<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileDescriptor;
    use Exploring\FileUtilityBundle\Data\ImageDescriptor;
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

        /**
         * @param FileManager         $fileManager
         * @param AbstractImageEngine $engine
         * @param Executor            $chainExecutor
         */
        function __construct(FileManager $fileManager, AbstractImageEngine $engine, Executor $chainExecutor = NULL)
        {
            $this->fileManager = $fileManager;
            $this->engine = $engine;
            $this->engine->setFileManager($fileManager);
            $this->chainExecutor = $chainExecutor;
            if ( $chainExecutor ) {
                $this->chainExecutor->setProcessor($this);
            }
        }

        /**
         * @param File   $file
         * @param string $directory
         * @param File   $maskFile
         * @param bool   $keepSourceFile
         *
         * @return ImageDescriptor
         */
        public function clip(File $file, $directory, File $maskFile, $keepSourceFile = FALSE)
        {
            return $this->engine->clip($file, $directory, $maskFile, $keepSourceFile);
        }

        /**
         * @param File   $file
         * @param string $directory
         * @param int    $x
         * @param int    $y
         * @param int    $width
         * @param int    $height
         * @param bool   $keepSourceFile
         *
         * @return mixed
         */
        public function crop(File $file, $directory, $x, $y, $width, $height, $keepSourceFile = FALSE)
        {
            return $this->engine->crop($file, $directory, $x, $y, $width, $height, $keepSourceFile);
        }

        /**
         * @param File   $file
         * @param string $directory
         * @param int    $size
         * @param bool   $enlarge
         * @param bool   $keepSourceFile
         *
         * @return FileDescriptor
         */
        public function scaleLargeEdge(File $file, $directory, $size, $enlarge = TRUE, $keepSourceFile = FALSE)
        {
            return $this->engine->scaleLargeEdge($file, $directory, $size, $enlarge, $keepSourceFile);
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
         * @param string $directory
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         * @param bool   $keepSourceFile
         *
         * @return FileDescriptor
         */
        public function scale(File $file, $directory, $width, $height = 0, $enlarge = TRUE, $keepSourceFile = FALSE)
        {
            return $this->engine->scale($file, $directory, $width, $height, $enlarge, $keepSourceFile);
        }

        /**
         * @param File        $file
         * @param string      $chainName
         * @param string|null $directory
         *
         * @return ImageDescriptor
         */
        public function applyChain(File $file, $chainName, $directory = NULL)
        {
            return $this->chainExecutor->execute($file, $chainName, $directory);
        }

        /**
         * @return FileManager
         */
        public function getFileManager()
        {
            return $this->fileManager;
        }

        /**
         * @param string $filename
         * @param string $directory
         *
         * @return ImageDescriptor
         */
        public function getImage($filename, $directory)
        {
            $fileDescriptor = $this->fileManager->getFileDescriptor($filename, $directory);
            $size = $this->getImageSize($fileDescriptor->getRealPath());

            return new ImageDescriptor($fileDescriptor, $size['width'], $size['height']);
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
         * @return $this
         */
        public function rollback()
        {
            $this->fileManager->rollback();

            return $this;
        }
    }