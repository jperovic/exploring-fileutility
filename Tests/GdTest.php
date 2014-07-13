<?php
    namespace Exploring\FileUtilityBundle\Tests;


    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Service\Image\GDImageEngine;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;

    class GdTest extends BaseImageTestClass
    {
        public function setUp()
        {
            $fm = new FileManager(array('t' => 'temp', 'd' => ''), __DIR__ . '/Resources/');
            $this->ip = new ImageProcessor($fm, new GDImageEngine(array(
                'quality' => array(
                    'jpeg' => 75,
                    'png'  => 7
                )
            )), $this->getChainExec());
        }
    }