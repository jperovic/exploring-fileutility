<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains\Steps;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Data\ImageWrapper;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;

    interface ChainStepInterface
    {
        /**
         * @param ImageProcessor $processor
         * @param FileWrapper    $fileWrapper
         * @param string         $saveToAlias
         * @param array          $arguments
         *
         * @return ImageWrapper
         */
        public function execute(ImageProcessor $processor, FileWrapper $fileWrapper, $saveToAlias, array $arguments = array());

        /**
         * @return string
         */
        public function getName();
    }