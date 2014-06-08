<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Imagick;

    /**
     * Created by JetBrains PhpStorm.
     * User: root
     * Date: 2/10/13
     * Time: 4:24 AM
     * To change this template use File | Settings | File Templates.
     */
    class ImagickImageEngine extends AbstractImageEngine
    {
        function __construct(FileManager $fileManager)
        {
            parent::__construct($fileManager);

            if (!class_exists("Imagick")) {
                throw new ImageProcessorException("Imagick not installed!", 500);
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
            // Create new objects from png's
            $source = new Imagick($filename);
            $source->setimagecompression(Imagick::COMPRESSION_NO);
            $source->setimagecompressionquality(1);
            $mask = new Imagick($mask_path);
            $maskSize = $mask->getimagegeometry();

            // IMPORTANT! Must activate the opacity channel
            $source->setImageMatte(1);

            // Create composite of two images using DSTIN
            $source->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);
            $source->cropimage($maskSize['width'], $maskSize['height'], 0, 0);

            $destination = $this->fileNameGenerator->createMasked($filename);

            // Write image to a file.
            $source->writeImage($destination);
            $this->fileManager->save($destination, $directory);

            $source->destroy();
            $mask->destroy();

            return $this->fileManager->stripAbsolutePath($destination, $directory);
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
            $source = new Imagick($filename);
            $source->scaleimage($width, $height);

            $destination = $this->fileNameGenerator->createScaled($filename, $width, $height);

            $source->writeimage($destination);
            $this->fileManager->save($destination, $directory);

            $source->destroy();

            return $this->fileManager->stripAbsolutePath($destination, $directory);
        }

        /**
         * @param string $filename
         *
         * @return int[]
         */
        public function getImageSize($filename)
        {
            $source = new Imagick($filename);
            $size = $source->getimagegeometry();
            $source->destroy();

            return $size;
        }

        /**
         * @return string
         */
        public function getName()
        {
            return "ImagickImageProcessor";
        }
    }
