<?php
    namespace Exploring\FileUtilityBundle\Tests;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;
    use Exploring\FileUtilityBundle\Service\Image\ImagickImageEngine;

    class ImagickTest extends BaseImageTestClass
    {
        public function setUp()
        {
            $fm = new FileManager(array('temp'), __DIR__ . '/Resources/');
            $this->ip = new ImageProcessor($fm, new ImagickImageEngine(array('compression' => 1, 'quality' => 86)), $this->getChainExec());
        }
    }