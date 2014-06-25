<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class GDImageEngine extends AbstractImageEngine
    {
        function __construct(FileManager $fileManager)
        {
            parent::__construct($fileManager);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param File   $maskFile
         *
         * @throws ImageProcessorException
         * @return FileWrapper
         */
        public function clipImage(File $file, $saveToAlias, File $maskFile)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true);
                $file = $entry->getFile();
            }

            $realPath = $file->getRealPath();

            /** @noinspection PhpUnusedLocalVariableInspection */
            if (!list($w, $h, $type) = @getimagesize($realPath)) {
                throw new ImageProcessorException("Invalid source file.");
            }

            $image = null;
            if ($type == IMAGETYPE_JPEG) {
                $image = @imagecreatefromjpeg($realPath);
            } else {
                if ($type == IMAGETYPE_PNG) {
                    $image = @imagecreatefrompng($realPath);
                } else {
                    if ($type == IMAGETYPE_GIF) {
                        $image = @imagecreatefromgif($realPath);
                    } else {
                        throw new ImageProcessorException("Unsupported image type!");
                    }
                }
            }

            $mask = @imagecreatefrompng($maskFile->getRealPath());

            if (!$image) {
                throw new ImageProcessorException("Could not create source object");
            }

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
            $destination = $this->fileManager->getAbsolutePath($newFileName, $saveToAlias);

            @imagepng($newPicture, $destination, 9);

            return $this->fileManager->save(new File($destination), $saveToAlias);
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         *
         * @throws ImageProcessorException
         *
         * @return FileWrapper
         */
        public function scaleImage(File $file, $saveToAlias, $width, $height = 0, $enlarge = true)
        {
            if ($file instanceof UploadedFile) {
                $entry = $this->fileManager->save($file, $saveToAlias, true);
                $file = $entry->getFile();
            }

            $realPath = $file->getRealPath();

            list($w, $h, $type) = getimagesize($realPath);

            if (!$w || !$h) {
                throw new ImageProcessorException(sprintf("Invalid image dimensions. Got %d x %d", $w, $h));
            }

            if ($type == IMAGETYPE_JPEG) {
                $Image = @imagecreatefromjpeg($realPath);
            } else {
                if ($type == IMAGETYPE_PNG) {
                    $Image = @imagecreatefrompng($realPath);
                } else {
                    if ($type == IMAGETYPE_GIF) {
                        $Image = @imagecreatefromgif($realPath);
                    } else {
                        throw new ImageProcessorException("Invalid image type.");
                    }
                }
            }

            if (!$Image) {
                throw new ImageProcessorException("Couldn't create source image.");
            }

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
            $destination = $this->fileManager->getAbsolutePath($scaledFileName, $saveToAlias);

            $NewImage = imagecreatetruecolor($width, $height);
            imagecopyresampled($NewImage, $Image, 0, 0, 0, 0, $width, $height, $w, $h);
            imagedestroy($Image);

            if ($type == IMAGETYPE_JPEG) {
                imagejpeg($NewImage, $destination, 100);
            } elseif ($type == IMAGETYPE_PNG) {
                imagepng($NewImage, $destination, 9);
            } elseif ($type == IMAGETYPE_GIF) {
                imagegif($NewImage, $destination);
            }
            imagedestroy($NewImage);

            return $this->fileManager->save(new File($destination), $saveToAlias);
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
    }
