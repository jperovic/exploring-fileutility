<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains\Steps;

    use Exploring\FileUtilityBundle\Data\FileDescriptor;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;
    use Exploring\FileUtilityBundle\Utility\ArraysUtil;

    class ScaleChainStep implements ChainStepInterface
    {
        /**
         * @return string
         */
        public function getName()
        {
            return "scale";
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
                array('width', 'height', 'enlarge'),
                array(0, 0, FALSE)
            );

            return $processor->scale(
                $fileWrapper->getFile(),
                $directory,
                $arguments['width'],
                $arguments['height'],
                $arguments['enlarge']
            );
        }
    }