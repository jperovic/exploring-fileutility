<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains\Steps;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
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
                                       array('size', 'enlarge'),
                                       array(0, false)
            );

            return $processor->scaleLargeEdge(
                             $fileWrapper->getFile(),
                                 $saveToAlias,
                                 $arguments['size'],
                                 $arguments['enlarge']
            );
        }
    }