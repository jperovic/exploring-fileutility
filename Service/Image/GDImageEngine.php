<?php

    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Data\FileDescriptor;
    use Exploring\FileUtilityBundle\Data\ImageDescriptor;
    use Exploring\FileUtilityBundle\Service\File\FileManagerException;
    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class GDImageEngine extends AbstractImageEngine
    {
        /** @var array */
        private $configuration;

        /**
         * @param array $configuration
         */
        function __construct(array $configuration)
        {
            $this->configuration = $configuration;
        }

        /**
         * @param File   $file
         * @param string $directory
         * @param File   $maskFile
         * @param bool   $keepSourceFile
         *
         * @throws ImageProcessorException
         * @throws FileManagerException
         * @internal param bool $isTemporary
         *
         * @return FileDescriptor
         */
        public function clip(File $file, $directory, File $maskFile, $keepSourceFile = FALSE)
        {
            if ($file instanceof UploadedFile)
            {
                $entry = $this->fileManager->save($file, $directory, TRUE, $keepSourceFile);
                $file = $entry->getFile();
            }

            $realPath = $file->getRealPath();

            /** @noinspection PhpUnusedLocalVariableInspection */
            if (!list($w, $h, $type) = @getimagesize($realPath))
            {
                throw new ImageProcessorException("Invalid source file.");
            }

            $image = $this->createImageObject($realPath, $type);

            $mask = @imagecreatefrompng($maskFile->getRealPath());

            if (!$mask)
            {
                throw new ImageProcessorException("Could not create mask object");
            }

            // Get sizes and set up new picture
            $width = imagesx($image);
            $height = imagesy($image);

            $newPicture = imagecreatetruecolor($width, $height);
            imagealphablending($newPicture, FALSE);
            imagesavealpha($newPicture, TRUE);
            imagefill($newPicture, 0, 0, imagecolorallocatealpha($newPicture, 0, 0, 0, 127));

            // Resize mask if necessary
            if ($width != imagesx($mask) || $height != imagesy($mask))
            {
                $tempPic = imagecreatetruecolor($width, $height);
                imagecopyresampled($tempPic, $mask, 0, 0, 0, 0, $width, $height, imagesx($mask), imagesy($mask));
                imagedestroy($mask);
                $mask = $tempPic;
            }

            // Perform pixel-based alpha map application
            for ($x = 0; $x < $width; $x++)
            {
                for ($y = 0; $y < $height; $y++)
                {
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

            self::assertGeneratedName($newFileName, 'createMasked');

            $destination = $this->fileManager->getAbsolutePath($newFileName, $directory);

            @imagepng($newPicture, $destination, 9);

            if (!$keepSourceFile)
            {
                $this->removeSourceFile($file);
            }

            return new ImageDescriptor($this->fileManager->save(new File($destination), $directory), $width, $height);
        }

        /**
         * @param File   $file
         * @param string $directory
         * @param int    $width
         * @param int    $height
         * @param bool   $enlarge
         * @param bool   $keepSourceFile
         *
         * @throws ImageProcessorException
         * @throws FileManagerException
         * @internal param bool $isTemporary
         *
         * @return FileDescriptor
         */
        public function scale(File $file, $directory, $width, $height = 0, $enlarge = TRUE, $keepSourceFile = FALSE)
        {
            if ($file instanceof UploadedFile)
            {
                $entry = $this->fileManager->save($file, $directory, TRUE, $keepSourceFile);
                $file = $entry->getFile();
            }

            $realPath = $file->getRealPath();

            list($w, $h, $type) = getimagesize($realPath);

            if (!$w || !$h)
            {
                throw new ImageProcessorException(sprintf("Invalid image dimensions. Got %d x %d", $w, $h));
            }

            $Image = $this->createImageObject($realPath, $type);

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

            $scaledFileName = $this->fileManager->getFilenameGenerator()->createScaled(
                $file->getFilename(),
                $width,
                $height
            );

            self::assertGeneratedName($scaledFileName, 'createScaled');

            $destination = $this->fileManager->getAbsolutePath($scaledFileName, $directory);

            $newImage = imagecreatetruecolor($width, $height);
            imagealphablending($newImage, FALSE);
            imagesavealpha($newImage, TRUE);
            imagecopyresampled($newImage, $Image, 0, 0, 0, 0, $width, $height, $w, $h);
            imagedestroy($Image);

            $this->saveImageObject($newImage, $type, $destination);

            if (!$keepSourceFile)
            {
                $this->removeSourceFile($file);
            }

            return new ImageDescriptor($this->fileManager->save(new File($destination), $directory), $width, $height);
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
         * @throws ImageProcessorException
         * @throws FileManagerException
         */
        public function crop(File $file, $directory, $x, $y, $width, $height, $keepSourceFile = FALSE)
        {
            if ($file instanceof UploadedFile)
            {
                $entry = $this->fileManager->save($file, $directory, TRUE, $keepSourceFile);
                $file = $entry->getFile();
            }

            $realPath = $file->getRealPath();

            list($w, $h, $type) = getimagesize($realPath);

            if (!$w || !$h)
            {
                throw new ImageProcessorException(sprintf("Invalid image dimensions. Got %d x %d", $w, $h));
            }

            $image = $this->createImageObject($realPath, $type);

            $newFileName = $this->fileManager->getFilenameGenerator()->generateRandom($file);

            if (!$newFileName)
            {
                throw new ImageProcessorException("Filename generator's generateRandom() must return string but the result was NULL. Did you implement it properly?");
            }

            self::assertGeneratedName($newFileName, 'generateRandom');

            $destination = $this->fileManager->getAbsolutePath($newFileName, $directory);

            $newImage = imagecreatetruecolor($width, $height);
            imagealphablending($newImage, FALSE);
            imagesavealpha($newImage, TRUE);
            imagecopyresampled($newImage, $image, 0, 0, $x, $y, $width, $height, $width, $height);
            imagedestroy($image);

            $this->saveImageObject($newImage, $type, $destination);

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
         */
        public function getImageSize($filename)
        {
            list($w, $h) = getimagesize($filename);

            return array ( 'width' => $w, 'height' => $h );
        }

        /**
         * @param string $realPath
         * @param string $type
         *
         * @return resource
         * @throws ImageProcessorException
         */
        private function createImageObject($realPath, $type)
        {
            if ($type == IMAGETYPE_JPEG)
            {
                $image = @imagecreatefromjpeg($realPath);
            }
            else
            {
                if ($type == IMAGETYPE_PNG)
                {
                    $image = @imagecreatefrompng($realPath);
                }
                else
                {
                    if ($type == IMAGETYPE_GIF)
                    {
                        $image = @imagecreatefromgif($realPath);
                    }
                    else
                    {
                        throw new ImageProcessorException("Invalid image type.");
                    }
                }
            }

            if (!$image)
            {
                throw new ImageProcessorException("Couldn't create source image.");
            }

            return $image;
        }

        /**
         * @param resource $image
         * @param string   $type
         * @param string   $destination
         */
        private function saveImageObject($image, $type, $destination)
        {
            if ($type == IMAGETYPE_JPEG)
            {
                imagejpeg($image, $destination, $this->configuration['quality']['jpeg']);
            }
            elseif ($type == IMAGETYPE_PNG)
            {
                imagepng($image, $destination, $this->configuration['quality']['png']);
            }
            elseif ($type == IMAGETYPE_GIF)
            {
                imagegif($image, $destination);
            }
            imagedestroy($image);
        }
    }
