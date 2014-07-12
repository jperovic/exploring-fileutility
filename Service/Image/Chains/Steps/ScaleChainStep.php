<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains\Steps;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
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
         * @param FileWrapper    $fileWrapper
         * @param string         $saveToAlias
         * @param array          $arguments
         *
         * @return FileWrapper
         */
        public function execute(ImageProcessor $processor, FileWrapper $fileWrapper, $saveToAlias, array $arguments = array())
        {
            $arguments = ArraysUtil::transformArrayToAssociative(
                                   $arguments,
                                       array('width', 'height', 'enlarge'),
                                       array(0, 0, false)
            );

            return $processor->scale(
                             $fileWrapper->getFile(),
                                 $saveToAlias,
                                 $arguments['width'],
                                 $arguments['height'],
                                 $arguments['enlarge']
            );
        }
    }