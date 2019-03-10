<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains\Steps;

    use Exploring\FileUtilityBundle\Data\FileDescriptor;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;
    use Exploring\FileUtilityBundle\Utility\ArraysUtil;

    class ScaleLargeEdgeChainStep implements ChainStepInterface
    {
        /**
         * @return string
         */
        public function getName()
        {
            return "large_edge";
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
                array('size', 'enlarge'),
                array(0, FALSE)
            );

            return $processor->scaleLargeEdge(
                $fileWrapper->getFile(),
                $directory,
                $arguments['size'],
                $arguments['enlarge']
            );
        }
    }