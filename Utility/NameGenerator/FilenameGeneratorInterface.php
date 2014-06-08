<?php
    namespace Exploring\FileUtilityBundle\Utility\NameGenerator;

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    interface FilenameGenerator
    {
        function createMasked($filename);

        function createScaled($filename, $width, $height);

        function generateRandom(UploadedFile $file);
    }