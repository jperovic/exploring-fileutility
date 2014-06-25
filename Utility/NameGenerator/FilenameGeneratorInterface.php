<?php
    namespace Exploring\FileUtilityBundle\Utility\NameGenerator;

    use Symfony\Component\HttpFoundation\File\File;

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
         * @param File $file
         *
         * @return string
         */
        function generateRandom(File $file);
    }