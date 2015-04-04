<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains\Steps;

    use Exploring\FileUtilityBundle\Data\FileDescriptor;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessorException;
    use Exploring\FileUtilityBundle\Utility\ArraysUtil;
    use Symfony\Component\HttpFoundation\File\File;

    class ClipChainStep implements ChainStepInterface
    {
        /**
         * @return string
         */
        public function getName()
        {
            return "clip";
        }

        /**
         * @param ImageProcessor $processor
         * @param FileDescriptor $fileWrapper
         * @param string         $directory
         * @param array          $arguments
         *
         * @throws ImageProcessorException
         *
         * @return FileDescriptor
         */
        public function execute(ImageProcessor $processor, FileDescriptor $fileWrapper, $directory, array $arguments = array())
        {
            $arguments = ArraysUtil::transformArrayToAssociative(
                $arguments,
                array('mask_file'),
                array(NULL)
            );

            if ( !$arguments['mask_file'] ) {
                throw new ImageProcessorException("You must specify mask file in order to perform clipping.");
            }

            $mask_file = new File($arguments['mask_file']);

            return $processor->clip(
                $fileWrapper->getFile(),
                $directory,
                $mask_file
            );
        }
    }