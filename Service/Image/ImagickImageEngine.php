<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Imagick;
    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

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
         * @param File   $file
         * @param string $saveToAlias
         * @param File   $maskFile
         *
         * @return FileWrapper
         */
        public function clipImage(File $file, $saveToAlias, File $maskFile)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true);
                $file = $entry->getFile();
            }

            // Create new objects from png's
            $source = new Imagick($file->getRealPath());
            $source->setimagecompression(Imagick::COMPRESSION_NO);
            $source->setimagecompressionquality(1);
            $mask = new Imagick($maskFile->getRealPath());
            $maskSize = $mask->getimagegeometry();

            // IMPORTANT! Must activate the opacity channel
            $source->setImageMatte(1);

            // Create composite of two images using DSTIN
            $source->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);
            $source->cropimage($maskSize['width'], $maskSize['height'], 0, 0);

            $newFileName = $this->fileManager->getFilenameGenerator()->createMasked($file->getFilename());
            $destination = $this->fileManager->getAbsolutePath($newFileName, $saveToAlias);

            // Write image to a file.
            $source->writeImage($destination);

            $source->destroy();
            $mask->destroy();

            return $this->fileManager->save(new File($destination), $saveToAlias);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         *
         * @return FileWrapper
         */
        public function scaleImage(File $file, $saveToAlias, $width, $height = 0, $enlarge = true)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true);
                $file = $entry->getFile();
            }

            $source = new Imagick($file);
            $source->scaleimage($width, $height);

            $newFileName = $this->fileManager->getFilenameGenerator()->createScaled(
                                             $file->getFilename(),
                                                 $width,
                                                 $height
            );
            $destination = $this->fileManager->getAbsolutePath($newFileName, $saveToAlias);

            $source->writeimage($destination);
            $source->destroy();

            return $this->fileManager->save(new File($destination), $saveToAlias);
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
    }
