<?php

    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\ImageDescriptor;
    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Service\File\FileManagerException;
    use Symfony\Component\HttpFoundation\File\File;

    abstract class AbstractImageEngine
    {
        /**
         * @var FileManager
         */
        protected $fileManager;

        /**
         * @param File   $file
         * @param string $directory
         * @param File   $maskFile
         * @param bool   $keepSourceFile
         *
         * @return ImageDescriptor
         */
        public abstract function clip(File $file, $directory, File $maskFile, $keepSourceFile = FALSE);

        /**
         * @param File   $file
         * @param string $directory
         * @param int    $size
         * @param bool   $enlarge
         * @param bool   $keepSourceFile
         *
         * @return ImageDescriptor
         */
        public function scaleLargeEdge(File $file, $directory, $size, $enlarge = TRUE, $keepSourceFile = FALSE)
        {
            $dim = $this->getImageSize($file->getRealPath());

            $landscape = $dim['width'] > $dim['height'];

            if ($landscape)
            {
                return $this->scale($file, $directory, $size, 0, $enlarge, $keepSourceFile);
            }
            else
            {
                return $this->scale($file, $directory, 0, $size, $enlarge, $keepSourceFile);
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
         * @param string $directory
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         * @param bool   $keepSourceFile
         *
         * @return ImageDescriptor
         */
        public abstract function scale(File $file, $directory, $width, $height = 0, $enlarge = TRUE, $keepSourceFile = FALSE);

        /**
         * @param File   $file
         * @param string $directory
         * @param int    $x
         * @param int    $y
         * @param int    $width
         * @param int    $height
         * @param bool   $keepSourceFile
         *
         * @return ImageDescriptor
         */
        public abstract function crop(File $file, $directory, $x, $y, $width, $height, $keepSourceFile = FALSE);

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
         * @return $this
         */
        public function rollback()
        {
            $this->fileManager->rollback();

            return $this;
        }

        /**
         * @param string $name
         * @param string $invocation
         *
         * @throws ImageProcessorException
         */
        protected static function assertGeneratedName($name, $invocation)
        {
            if (!$name)
            {
                throw new ImageProcessorException("Filename generator's $invocation() must return string but the result was empty. Did you implement it properly?");
            }
        }

        /**
         * @param File $file
         *
         * @throws FileManagerException
         */
        protected function removeSourceFile(File $file)
        {
            $directory = $this->fileManager->guessDirectoryOfFile($file);
            $this->fileManager->remove($file->getFilename(), $directory);
        }
    }
