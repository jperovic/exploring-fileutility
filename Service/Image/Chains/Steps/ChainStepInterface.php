<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains\Steps;

    use Exploring\FileUtilityBundle\Data\FileDescriptor;
    use Exploring\FileUtilityBundle\Data\ImageDescriptor;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;

    interface ChainStepInterface
    {
        /**
         * @param ImageProcessor $processor
         * @param FileDescriptor $fileWrapper
         * @param string         $directory
         * @param array          $arguments
         *
         * @return ImageDescriptor
         */
        public function execute(ImageProcessor $processor, FileDescriptor $fileWrapper, $directory, array $arguments = array());

        /**
         * @return string
         */
        public function getName();
    }