<?php
    namespace Exploring\FileUtilityBundle\Service\Image;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Symfony\Component\HttpFoundation\File\File;

    class GDImageEngine extends AbstractImageEngine
    {
        function __construct(FileManager $fileManager)
        {
            parent::__construct($fileManager);
        }

        /**
         * @param string $directory
         * @param string $filename
         * @param string $mask_path
         *
         * @throws ImageProcessorException
         * @internal param string $absFilename
         * @return string
         */
        public function clipImage($filename, $directory, $mask_path)
        {
            $file = new File($filename);

            /** @noinspection PhpUnusedLocalVariableInspection */
            if (!list($w, $h, $type) = @getimagesize($filename)) {
                throw new ImageProcessorException("Invalid source file.");
            }

            $image = null;
            if ($type == IMAGETYPE_JPEG) {
                $image = @imagecreatefromjpeg($filename);
            }
            else if ($type == IMAGETYPE_PNG) {
                $image = @imagecreatefrompng($filename);
            }
            else if ($type == IMAGETYPE_GIF) {
                $image = @imagecreatefromgif($filename);
            }
            else {
                throw new ImageProcessorException("Unsupported image type!");
            }

            $mask = @imagecreatefrompng($mask_path);

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
                    imagesetpixel($newPicture, $x, $y, imagecolorallocatealpha($newPicture, $color['red'], $color['green'], $color['blue'], $alpha));
                }
            }

            // Copy back to original picture
            imagedestroy($image);
            $newFileName = $this->fileNameGenerator->createMasked($file->getFilename());
            $destination = $this->fileManager->getAbsolutePath($directory, $newFileName);

            @imagepng($newPicture, $destination, 9);

            $this->fileManager->save($destination, $directory);

            return $newFileName;
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

        /**
         * @param string $directory
         * @param string $filename
         * @param int    $width
         * @param int    $height
         *
         * @throws ImageProcessorException
         * @return string
         */
        public function scaleImage($filename, $directory, $width, $height = 0)
        {
            $file = new File($filename, true);

            list($w, $h, $type) = getimagesize($filename);

            if (!$w || !$h) {
                throw new ImageProcessorException(sprintf("Invalid image dimensions. Got %d x %d", $w, $h));
            }

            if ($type == IMAGETYPE_JPEG) {
                $Image = @imagecreatefromjpeg($filename);
            }
            else if ($type == IMAGETYPE_PNG) {
                $Image = @imagecreatefrompng($filename);
            }
            else if ($type == IMAGETYPE_GIF) {
                $Image = @imagecreatefromgif($filename);
            }
            else {
                throw new ImageProcessorException("Invalid image type.");
            }

            if (!$Image) {
                throw new ImageProcessorException("Couldn't create source image.");
            }

            $scaledFileName = $this->fileNameGenerator->createScaled($file->getFilename(), $width, $height);

            $destination = $this->fileManager->getAbsolutePath($directory, $scaledFileName);

            $isLandscape = $w > $h;

            $ratio = $isLandscape ? $w / $h : $h / $w;

            if ($width == 0) {
                $width = $isLandscape ? $height * $ratio : $height / $ratio;
            }
            else if ($height == 0) {
                $height = $isLandscape ? $width / $ratio : $width * $ratio;
            }

            $NewImage = imagecreatetruecolor($width, $height);
            imagecopyresampled($NewImage, $Image, 0, 0, 0, 0, $width, $height, $w, $h);
            imagedestroy($Image);

            if ($type == IMAGETYPE_JPEG) {
                imagejpeg($NewImage, $destination, 100);
            }
            elseif ($type == IMAGETYPE_PNG) {
                imagepng($NewImage, $destination, 9);
            }
            elseif ($type == IMAGETYPE_GIF) {
                imagegif($NewImage, $destination);
            }
            imagedestroy($NewImage);

            $this->fileManager->save($destination, $directory);

            return $scaledFileName;
        }
    }
