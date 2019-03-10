<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains\Steps;

    use Exploring\FileUtilityBundle\Data\FileDescriptor;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;
    use Exploring\FileUtilityBundle\Utility\ArraysUtil;

    class CropChainStep implements ChainStepInterface
    {
        /**
         * @return string
         */
        public function getName()
        {
            return "crop";
        }

        /**
         * @param ImageProcessor $processor
         * @param FileDescriptor $fileWrapper
         * @param string         $directory
         * @param array          $arguments
         *
         * @return FileDescriptor
         */
        public function execute(ImageProcessor $processor, FileDescriptor $fileWrapper, $directory, array $arguments = array())
        {
            $arguments = ArraysUtil::transformArrayToAssociative(
                $arguments,
                array('x', 'y', 'width', 'height'),
                array(0, 0, 0, 0)
            );

            return $processor->crop(
                $fileWrapper->getFile(),
                $directory,
                $arguments['x'],
                $arguments['y'],
                $arguments['width'],
                $arguments['height']
            );
        }
    }