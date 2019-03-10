<?php

    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\ImageDescriptor;
    use Exploring\FileUtilityBundle\Service\File\FileManagerException;
    use Imagick;
    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class ImagickImageEngine extends AbstractImageEngine
    {
        private $configuration;

        /**
         * @param array $configuration
         *
         * @throws ImageProcessorException
         */
        function __construct(array $configuration)
        {
            if (!class_exists("Imagick"))
            {
                throw new ImageProcessorException("Imagick library class was not found. Did you forget to install it?", 500);
            }

            $this->configuration = $configuration;
        }

        /**
         * @param File   $file
         * @param string $directory
         * @param File   $maskFile
         * @param bool   $keepSourceFile
         *
         * @return ImageDescriptor
         * @throws ImageProcessorException
         * @throws FileManagerException
         * @throws \ImagickException
         */
        public function clip(File $file, $directory, File $maskFile, $keepSourceFile = FALSE)
        {
            if ($file instanceof UploadedFile)
            {
                $entry = $this->fileManager->save($file, $directory, TRUE, $keepSourceFile);
                $file = $entry->getFile();
            }

            // Create new objects from png's
            $source = new Imagick($file->getRealPath());
            $sourceSize = $source->getimagegeometry();
            $source->setimagecompression($this->configuration['quality']['compression']);
            $source->setimagecompressionquality($this->configuration['quality']['quality']);
            $source->setImageMatte(TRUE);
            $mask = new Imagick($maskFile->getRealPath());
            $maskSize = $mask->getimagegeometry();

            // Create composite of two images using DSTIN
            $source->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);
            $source->cropimage($maskSize['width'], $maskSize['height'], 0, 0);

            $newFileName = $this->fileManager->getFilenameGenerator()->createMasked($file->getFilename());

            self::assertGeneratedName($newFileName, 'createMasked');

            $destination = $this->fileManager->getAbsolutePath($newFileName, $directory);

            // Write image to a file.
            $source->writeImage($destination);

            $source->destroy();
            $mask->destroy();

            if (!$keepSourceFile)
            {
                $this->removeSourceFile($file);
            }

            return new ImageDescriptor($this->fileManager->save(
                new File($destination),
                $directory
            ), $sourceSize['width'], $sourceSize['height']);
        }

        /**
         * @param File   $file
         * @param string $directory
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         * @param bool   $keepSourceFile
         *
         * @return ImageDescriptor
         * @throws ImageProcessorException
         * @throws FileManagerException
         * @throws \ImagickException
         */
        public function scale(File $file, $directory, $width, $height = 0, $enlarge = TRUE, $keepSourceFile = FALSE)
        {
            if ($file instanceof UploadedFile)
            {
                $entry = $this->fileManager->save($file, $directory, TRUE, $keepSourceFile);
                $file = $entry->getFile();
            }

            $source = new Imagick($file->getRealPath());
            $source->setImageMatte(TRUE);
            $source->setimagecompression($this->configuration['quality']['compression']);
            $source->setimagecompressionquality($this->configuration['quality']['quality']);
            $size = $source->getimagegeometry();
            $w = $size['width'];
            $h = $size['height'];

            $isLandscape = $w > $h;
            $ratio = $isLandscape ? $w / $h : $h / $w;

            if ($width == 0)
            {
                if ($height > $h && !$enlarge)
                {
                    $height = $h;
                }
                $width = $isLandscape ? $height * $ratio : $height / $ratio;
            }
            else
            {
                if ($height == 0)
                {
                    if ($width > $w && !$enlarge)
                    {
                        $width = $w;
                    }
                    $height = $isLandscape ? $width / $ratio : $width * $ratio;
                }
            }

            $source->scaleimage($width, $height);

            $newFileName = $this->fileManager
                ->getFilenameGenerator()
                ->createScaled($file->getFilename(), $width, $height);

            self::assertGeneratedName($newFileName, 'createScaled');

            $destination = $this->fileManager->getAbsolutePath($newFileName, $directory);

            $source->writeimage($destination);
            $source->destroy();

            if (!$keepSourceFile)
            {
                $this->removeSourceFile($file);
            }

            return new ImageDescriptor($this->fileManager->save(new File($destination), $directory), $width, $height);
        }

        /**
         * @param string $filename
         *
         * @return int[]
         * @throws \ImagickException
         */
        public function getImageSize($filename)
        {
            $source = new Imagick($filename);
            $size = $source->getimagegeometry();
            $source->destroy();

            return $size;
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
         * @return ImageDescriptor
         * @throws FileManagerException
         * @throws ImageProcessorException
         * @throws \ImagickException
         */
        public function crop(File $file, $directory, $x, $y, $width, $height, $keepSourceFile = FALSE)
        {
            if ($file instanceof UploadedFile)
            {
                $entry = $this->fileManager->save($file, $directory, TRUE, $keepSourceFile);
                $file = $entry->getFile();
            }

            $source = new Imagick($file->getRealPath());
            $source->setImageMatte(TRUE);
            $source->setimagecompression($this->configuration['quality']['compression']);
            $source->setimagecompressionquality($this->configuration['quality']['quality']);
            $source->cropimage($width, $height, $x, $y);

            $newFileName = $this->fileManager->getFilenameGenerator()->createScaled(
                $file->getFilename(),
                $width,
                $height
            );

            self::assertGeneratedName($newFileName, 'createScaled');

            $destination = $this->fileManager->getAbsolutePath($newFileName, $directory);

            $source->writeimage($destination);
            $source->destroy();

            if (!$keepSourceFile)
            {
                $this->removeSourceFile($file);
            }

            return new ImageDescriptor($this->fileManager->save(new File($destination), $directory), $width, $height);
        }
    }
