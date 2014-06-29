<?php
    namespace Exploring\FileUtilityBundle\Tests;


    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Service\Image\GDImageEngine;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;

    class GdTest extends BaseImageTestClass
    {
        public function setUp()
        {
            $fm = new FileManager(array('t' => 'temp'), __DIR__ . '/Resources/');
            $this->ip = new ImageProcessor($fm, new GDImageEngine());
        }
    }