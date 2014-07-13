<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\ImageWrapper;
    use Imagick;
    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class ImagickImageEngine extends AbstractImageEngine
    {
        private $configuration;

        function __construct(array $configuration)
        {
            if (!class_exists("Imagick")) {
                throw new ImageProcessorException("Imagick library class was not found. Did you forget to install it?", 500);
            }

            $this->configuration = $configuration;
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
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true, $keepSourceFile);
                $file = $entry->getFile();
            }

            // Create new objects from png's
            $source = new Imagick($file->getRealPath());
            $sourceSize = $source->getimagegeometry();
            $source->setimagecompression($this->configuration['compression']);
            $source->setimagecompressionquality($this->configuration['quality']);
            $mask = new Imagick($maskFile->getRealPath());
            $maskSize = $mask->getimagegeometry();

            // IMPORTANT! Must activate the opacity channel
            $source->setImageMatte(1);

            // Create composite of two images using DSTIN
            $source->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);
            $source->cropimage($maskSize['width'], $maskSize['height'], 0, 0);

            $newFileName = $this->fileManager->getFilenameGenerator()->createMasked($file->getFilename());

            $this->assertGeneratedName($newFileName, 'createMasked');

            $destination = $this->fileManager->getAbsolutePath($newFileName, $saveToAlias);


            // Write image to a file.
            $source->writeImage($destination);

            $source->destroy();
            $mask->destroy();

            if (!$keepSourceFile) {
                $this->removeSourceFile($file);
            }

            return new ImageWrapper($this->fileManager->save(
                                                      new File($destination),
                                                          $saveToAlias
            ), $sourceSize['width'], $sourceSize['height']);
        }

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
        public function scale(File $file, $saveToAlias, $width, $height = 0, $enlarge = true, $keepSourceFile = false)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true, $keepSourceFile);
                $file = $entry->getFile();
            }

            $source = new Imagick($file->getRealPath());
            $source->setimagecompression($this->configuration['compression']);
            $source->setimagecompressionquality($this->configuration['quality']);
            $size = $source->getimagegeometry();
            $w = $size['width'];
            $h = $size['height'];

            $isLandscape = $w > $h;
            $ratio = $isLandscape ? $w / $h : $h / $w;

            if ($width == 0) {
                if ($height > $h && !$enlarge) {
                    $height = $h;
                }
                $width = $isLandscape ? $height * $ratio : $height / $ratio;
            } else {
                if ($height == 0) {
                    if ($width > $w && !$enlarge) {
                        $width = $w;
                    }
                    $height = $isLandscape ? $width / $ratio : $width * $ratio;
                }
            }

            $source->scaleimage($width, $height);

            $newFileName = $this->fileManager->getFilenameGenerator()->createScaled(
                                             $file->getFilename(),
                                                 $width,
                                                 $height
            );

            $this->assertGeneratedName($newFileName, 'createScaled');

            $destination = $this->fileManager->getAbsolutePath($newFileName, $saveToAlias);

            $source->writeimage($destination);
            $source->destroy();

            if (!$keepSourceFile) {
                $this->removeSourceFile($file);
            }

            return new ImageWrapper($this->fileManager->save(new File($destination), $saveToAlias), $width, $height);
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
        public function crop(File $file, $saveToAlias, $x, $y, $width, $height, $keepSourceFile = false)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true, $keepSourceFile);
                $file = $entry->getFile();
            }

            $source = new Imagick($file->getRealPath());
            $source->setimagecompression($this->configuration['compression']);
            $source->setimagecompressionquality($this->configuration['quality']);
            $source->cropimage($width, $width, $x, $y);

            $newFileName = $this->fileManager->getFilenameGenerator()->createScaled(
                                             $file->getFilename(),
                                                 $width,
                                                 $height
            );

            $this->assertGeneratedName($newFileName, 'createScaled');

            $destination = $this->fileManager->getAbsolutePath($newFileName, $saveToAlias);

            $source->writeimage($destination);
            $source->destroy();

            if (!$keepSourceFile) {
                $this->removeSourceFile($file);
            }

            return new ImageWrapper($this->fileManager->save(
                                                      new File($destination),
                                                          $saveToAlias
            ), $width, $height);
        }
    }
