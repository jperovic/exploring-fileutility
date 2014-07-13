<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\ImageWrapper;
    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Symfony\Component\HttpFoundation\File\File;

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
         * @param bool   $keepSourceFile
         *
         * @return ImageWrapper
         */
        public abstract function clip(File $file, $saveToAlias, File $maskFile, $keepSourceFile = false);

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $size
         * @param bool   $enlarge
         * @param bool   $keepSourceFile
         *
         * @return ImageWrapper
         */
        public function scaleLargeEdge(File $file, $saveToAlias, $size, $enlarge = true, $keepSourceFile = false)
        {
            $dim = $this->getImageSize($file->getRealPath());

            $landscape = $dim['width'] > $dim['height'];

            if ($landscape) {
                return $this->scale($file, $saveToAlias, $size, 0, $enlarge, $keepSourceFile);
            } else {
                return $this->scale($file, $saveToAlias, 0, $size, $enlarge, $keepSourceFile);
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
         * @param bool   $keepSourceFile
         *
         * @return ImageWrapper
         */
        public abstract function scale(File $file, $saveToAlias, $width, $height = 0, $enlarge = true, $keepSourceFile = false);

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $x
         * @param int    $y
         * @param int    $width
         * @param int    $height
         * @param bool   $keepSourceFile
         *
         * @return ImageWrapper
         */
        public abstract function crop(File $file, $saveToAlias, $x, $y, $width, $height, $keepSourceFile = false);

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

        /**
         * @param string $name
         * @param string $invocation
         *
         * @throws ImageProcessorException
         */
        protected function assertGeneratedName($name, $invocation)
        {
            if (!$name) {
                $error = sprintf(
                    "Filename generator's %s() must return string but the result was NULL. Did you implement it properly?",
                    $invocation
                );
                throw new ImageProcessorException($error);
            }
        }

        protected function removeSourceFile(File $file)
        {
            $directoryAlias = $this->fileManager->guessDirectoryAliasOfFile($file);
            $this->fileManager->remove($file->getFilename(), $directoryAlias);
        }
    }
