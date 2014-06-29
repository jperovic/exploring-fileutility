<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Data\ImageWrapper;
    use Imagick;
    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class ImagickImageEngine extends AbstractImageEngine
    {
        function __construct()
        {
            if (!class_exists("Imagick")) {
                throw new ImageProcessorException("Imagick library class was not found. Did you forget to install it?", 500);
            }
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param File   $maskFile
         * @param bool   $keepOriginal
         *
         * @return ImageWrapper
         */
        public function clip(File $file, $saveToAlias, File $maskFile, $keepOriginal = false)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true, $keepOriginal);
                $file = $entry->getFile();
            }

            // Create new objects from png's
            $source = new Imagick($file->getRealPath());
            $sourceSize = $source->getimagegeometry();
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
         * @param bool   $keepOriginal
         *
         * @return ImageWrapper
         */
        public function scale(File $file, $saveToAlias, $width, $height = 0, $enlarge = true, $keepOriginal = false)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true, $keepOriginal);
                $file = $entry->getFile();
            }

            $source = new Imagick($file->getRealPath());
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
            $destination = $this->fileManager->getAbsolutePath($newFileName, $saveToAlias);

            $source->writeimage($destination);
            $source->destroy();

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
         * @param bool   $keepOriginal
         *
         * @return ImageWrapper
         */
        public function crop(File $file, $saveToAlias, $x, $y, $width, $height, $keepOriginal = false)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true, $keepOriginal);
                $file = $entry->getFile();
            }

            $source = new Imagick($file->getRealPath());
            $source->cropimage($width, $width, $x, $y);

            $newFileName = $this->fileManager->getFilenameGenerator()->createScaled(
                                             $file->getFilename(),
                                                 $width,
                                                 $height
            );
            $destination = $this->fileManager->getAbsolutePath($newFileName, $saveToAlias);

            $source->writeimage($destination);
            $source->destroy();

            return new ImageWrapper($this->fileManager->save(new File($destination), $saveToAlias), $width, $height);
        }
    }
