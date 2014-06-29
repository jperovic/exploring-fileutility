<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Data\ImageWrapper;
    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class GDImageEngine extends AbstractImageEngine
    {
        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param File   $maskFile
         * @param bool   $keepOriginal
         *
         * @throws ImageProcessorException
         * @return FileWrapper
         */
        public function clip(File $file, $saveToAlias, File $maskFile, $keepOriginal = false)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true, $keepOriginal);
                $file = $entry->getFile();
            }

            $realPath = $file->getRealPath();

            /** @noinspection PhpUnusedLocalVariableInspection */
            if (!list($w, $h, $type) = @getimagesize($realPath)) {
                throw new ImageProcessorException("Invalid source file.");
            }

            $image = $this->createImageObject($realPath, $type);

            $mask = @imagecreatefrompng($maskFile->getRealPath());

            if (!$mask) {
                throw new ImageProcessorException("Could not create mask object");
            }

            // Get sizes and set up new picture
            $width = imagesx($image);
            $height = imagesy($image);

            $newPicture = imagecreatetruecolor($width, $height);
            imagesavealpha($newPicture, true);
            imagefill($newPicture, 0, 0, imagecolorallocatealpha($newPicture, 0, 0, 0, 127));

            // Resize mask if necessary
            if ($width != imagesx($mask) || $height != imagesy($mask)) {
                $tempPic = imagecreatetruecolor($width, $height);
                imagecopyresampled($tempPic, $mask, 0, 0, 0, 0, $width, $height, imagesx($mask), imagesy($mask));
                imagedestroy($mask);
                $mask = $tempPic;
            }

            // Perform pixel-based alpha map application
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $alpha = imagecolorsforindex($mask, imagecolorat($mask, $x, $y));
                    $alpha = 127 - floor($alpha['red'] / 2);
                    $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                    imagesetpixel(
                        $newPicture,
                        $x,
                        $y,
                        imagecolorallocatealpha($newPicture, $color['red'], $color['green'], $color['blue'], $alpha)
                    );
                }
            }

            // Copy back to original picture
            imagedestroy($image);
            $newFileName = $this->fileManager->getFilenameGenerator()->createMasked($file->getFilename());

            $this->assertGeneratedName($newFileName, 'createMasked');

            $destination = $this->fileManager->getAbsolutePath($newFileName, $saveToAlias);

            @imagepng($newPicture, $destination, 9);

            return new ImageWrapper($this->fileManager->save(new File($destination), $saveToAlias), $width, $height);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         * @param bool   $keepOriginal
         *
         * @throws ImageProcessorException
         * @return FileWrapper
         */
        public function scale(File $file, $saveToAlias, $width, $height = 0, $enlarge = true, $keepOriginal = false)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true, $keepOriginal);
                $file = $entry->getFile();
            }

            $realPath = $file->getRealPath();

            list($w, $h, $type) = getimagesize($realPath);

            if (!$w || !$h) {
                throw new ImageProcessorException(sprintf("Invalid image dimensions. Got %d x %d", $w, $h));
            }

            $Image = $this->createImageObject($realPath, $type);

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

            $scaledFileName = $this->fileManager->getFilenameGenerator()->createScaled(
                                                $file->getFilename(),
                                                    $width,
                                                    $height
            );

            $this->assertGeneratedName($scaledFileName, 'createScaled');

            $destination = $this->fileManager->getAbsolutePath($scaledFileName, $saveToAlias);

            $newImage = imagecreatetruecolor($width, $height);
            imagecopyresampled($newImage, $Image, 0, 0, 0, 0, $width, $height, $w, $h);
            imagedestroy($Image);

            $this->saveImageObject($newImage, $type, $destination);

            return new ImageWrapper($this->fileManager->save(new File($destination), $saveToAlias), $width, $height);
        }

        public function crop(File $file, $saveToAlias, $x, $y, $width, $height, $keepOriginal = false)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true, $keepOriginal);
                $file = $entry->getFile();
            }

            $realPath = $file->getRealPath();

            list($w, $h, $type) = getimagesize($realPath);

            if (!$w || !$h) {
                throw new ImageProcessorException(sprintf("Invalid image dimensions. Got %d x %d", $w, $h));
            }

            $image = $this->createImageObject($realPath, $type);

            $newFileName = $this->fileManager->getFilenameGenerator()->generateRandom($file);

            if (!$newFileName) {
                throw new ImageProcessorException("Filename generator's generateRandom() must return string but the result was NULL. Did you implement it properly?");
            }

            $this->assertGeneratedName($newFileName, 'generateRandom');

            $destination = $this->fileManager->getAbsolutePath($newFileName, $saveToAlias);

            $newImage = imagecreatetruecolor($width, $height);
            imagecopyresampled($newImage, $image, 0, 0, $x, $y, $width, $height, $width, $height);
            imagedestroy($image);

            $this->saveImageObject($newImage, $type, $destination);

            return new ImageWrapper($this->fileManager->save(new File($destination), $saveToAlias), $width, $height);
        }

        /**
         * @param string $filename
         *
         * @return int[]
         */
        public function getImageSize($filename)
        {
            list($w, $h) = getimagesize($filename);

            return array('width' => $w, 'height' => $h);
        }

        private function createImageObject($realPath, $type)
        {
            if ($type == IMAGETYPE_JPEG) {
                $image = @imagecreatefromjpeg($realPath);
            } else if ($type == IMAGETYPE_PNG) {
                $image = @imagecreatefrompng($realPath);
            } else if ($type == IMAGETYPE_GIF) {
                $image = @imagecreatefromgif($realPath);
            } else {
                throw new ImageProcessorException("Invalid image type.");
            }

            if (!$image) {
                throw new ImageProcessorException("Couldn't create source image.");
            }

            return $image;
        }

        private function saveImageObject($image, $type, $destination)
        {
            if ($type == IMAGETYPE_JPEG) {
                imagejpeg($image, $destination, 100); // TODO: Expose to config
            } elseif ($type == IMAGETYPE_PNG) {
                imagepng($image, $destination, 9); // TODO: Expose to config
            } elseif ($type == IMAGETYPE_GIF) {
                imagegif($image, $destination);
            }
            imagedestroy($image);
        }
    }
