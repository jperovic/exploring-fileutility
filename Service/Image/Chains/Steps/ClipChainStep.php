<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains\Steps;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
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
         * @param FileWrapper    $fileWrapper
         * @param string         $saveToAlias
         * @param array          $arguments
         *
         * @throws ImageProcessorException
         *
         * @return FileWrapper
         */
        public function execute(ImageProcessor $processor, FileWrapper $fileWrapper, $saveToAlias, array $arguments = array())
        {
            $arguments = ArraysUtil::transformArrayToAssociative(
                                   $arguments,
                                       array('mask_file'),
                                       array(null)
            );

            if (!$arguments['mask_file']) {
                throw new ImageProcessorException("You must specify mask file in order to perform clipping.");
            }

            $mask_file = new File($arguments['mask_file']);

            return $processor->clip(
                             $fileWrapper->getFile(),
                                 $saveToAlias,
                                 $mask_file
            );
        }
    }