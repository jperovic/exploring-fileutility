<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains\Steps;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
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
                                       array('x', 'y', 'width', 'height'),
                                       array(0, 0, 0, 0)
            );

            return $processor->crop(
                             $fileWrapper->getFile(),
                                 $saveToAlias,
                                 $arguments['x'],
                                 $arguments['y'],
                                 $arguments['width'],
                                 $arguments['height']
            );
        }
    }