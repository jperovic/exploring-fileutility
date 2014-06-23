<?php
    namespace Exploring\FileUtilityBundle\Utility\NameGenerator;

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    interface FilenameGeneratorInterface
    {

        /**
         * @param string $filename
         *
         * @return string
         */
        function createMasked($filename);

        /**
         * @param string $filename
         * @param int    $width
         * @param int    $height
         *
         * @return string
         */
        function createScaled($filename, $width, $height);

        /**
         * @param UploadedFile $file
         *
         * @return string
         */
        function generateRandom(UploadedFile $file);
    }