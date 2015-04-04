<?php
    namespace Exploring\FileUtilityBundle\DependencyInjection;

    class Constants
    {
        const ENGINE_GD = "gd";
        const ENGINE_IMAGICK = "imagick";

        const DEFAULT_JPEG_QUALITY = 75;
        const DEFAULT_PNG_QUALITY = 7;
        const DEFAULT_IMAGICK_COMPRESSION = 1; // COMPRESSION_NO
        const DEFAULT_IMAGICK_COMPRESSION_QUALITY = 86;
    }