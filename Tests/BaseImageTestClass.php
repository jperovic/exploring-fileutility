<?php
    namespace Exploring\FileUtilityBundle\Tests;

    use Exploring\FileUtilityBundle\Service\Image\Chains\Executor;
    use Exploring\FileUtilityBundle\Service\Image\Chains\Steps\ClipChainStep;
    use Exploring\FileUtilityBundle\Service\Image\Chains\Steps\CropChainStep;
    use Exploring\FileUtilityBundle\Service\Image\Chains\Steps\ScaleChainStep;
    use Exploring\FileUtilityBundle\Service\Image\Chains\Steps\ScaleLargeEdgeChainStep;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\HttpFoundation\File\File;

    abstract class BaseImageTestClass extends WebTestCase
    {
        /** @var ImageProcessor */
        protected $ip;

        protected function tearDown()
        {
            $this->ip->rollback();
        }

        public function testScale()
        {
            $pngFile = new File(__DIR__ . '/Resources/tomask.png');

            $wrap = $this->ip->scale($pngFile, 't', 120, 0, true, true);

            $size = $this->ip->getImageSize($wrap->getFile()->getRealPath());

            $this->assertEquals(120, $size['width']);
            $this->assertGreaterThan(0, $size['height']);
        }

        public function testScaleLarge()
        {
            $pngFile = new File(__DIR__ . '/Resources/tomask.png');

            $wrap = $this->ip->scaleLargeEdge($pngFile, 't', 120, true, true);

            $size = $this->ip->getImageSize($wrap->getFile()->getRealPath());
            $this->assertEquals(120, $size['width']);
        }

        public function testClip()
        {
            $pngFile = new File(__DIR__ . '/Resources/tomask.png');
            $maskFile = new File(__DIR__ . '/Resources/mask.png');

            $wrap = $this->ip->clip($pngFile, 't', $maskFile, true);

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

        public function testImageChain(){
            $pngFile = new File(__DIR__ . '/Resources/tomask.png');

            $wrap = $this->ip->applyChain($pngFile, 'foo');

            $this->assertEquals(500, $wrap->getWidth());
            $this->assertEquals(100, $wrap->getHeight());
        }

        ##########################
        # COMMON METHODS
        ##########################

        /**
         * @return Executor
         */
        protected function getChainExec(){
            $chainDef = array(
                'foo' => array(
                    'alias' => 't',
                    'steps' => array(
                        'large_edge' => array(500),
                        'clip' => array(__DIR__ . '/Resources/mask.png'),
                        'crop' => array(0, 0, 500, 100)
                    )
                )
            );
            return new Executor($chainDef, array(new ClipChainStep(), new CropChainStep(), new ScaleLargeEdgeChainStep(), new ScaleChainStep()));
        }
    }
 