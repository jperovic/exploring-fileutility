<?php
    namespace Exploring\FileUtilityBundle\Tests;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Service\Image\GDImageEngine;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\HttpFoundation\File\File;

    abstract class BaseImageTestClass extends WebTestCase
    {
        /** @var ImageProcessor */
        protected $ip;

        /**
         * {@inheritDoc}
         */
        public function setUp()
        {
            $fm = new FileManager(array('t' => 'temp'), __DIR__ . '/Resources/');
            $this->ip = new ImageProcessor($fm, new GDImageEngine());
        }

        public function testScale()
        {
            $pngFile = new File(__DIR__ . '/Resources/tomask.png');

            $wrap = $this->ip->scale($pngFile, 't', 120, 0);

            $size = $this->ip->getImageSize($wrap->getFile()->getRealPath());

            $this->assertEquals(120, $size['width']);
            $this->assertGreaterThan(0, $size['height']);
        }

        public function testScaleLarge()
        {
            $pngFile = new File(__DIR__ . '/Resources/tomask.png');

            $wrap = $this->ip->scaleLargeEdge($pngFile, 't', 120);

            $size = $this->ip->getImageSize($wrap->getFile()->getRealPath());
            $this->assertEquals(120, $size['width']);
        }

        public function testClip()
        {
            $pngFile = new File(__DIR__ . '/Resources/tomask.png');
            $maskFile = new File(__DIR__ . '/Resources/mask.png');

            $wrap = $this->ip->clip($pngFile, 't', $maskFile);

            $this->assertTrue(file_exists($wrap->getFile()->getRealPath()));
        }

        public function testCrop()
        {
            $pngFile = new File(__DIR__ . '/Resources/tomask.png');

            $wrap = $this->ip->crop($pngFile, 't', 0, 0, 100, 100, true);

            $size = $this->ip->getImageSize($wrap->getFile()->getRealPath());
            $this->assertEquals(100, $size['width']);
            $this->assertEquals(100, $size['height']);
        }
    }
 