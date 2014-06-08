<?php
    namespace Exploring\Tests;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Exploring\FileUtilityBundle\Service\Image\GDAbstractImageEngine;
    use Exploring\FileUtilityBundle\Service\Image\ImagickAbstractImageEngine;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class GdProcessorTest extends WebTestCase
    {
        /** @var ImagickAbstractImageEngine */
        private $imagick;

        /** @var GDAbstractImageEngine */
        private $gd;

        /**
         * {@inheritDoc}
         */
        public function setUp()
        {
            static::$kernel = static::createKernel();
            static::$kernel->boot();
            $c = static::$kernel->getContainer();
            $this->imagick = $c->get("exploring.image.processor.imagick");
            $this->gd = $c->get("exploring.image.processor.gd");
            /** @var FileManager */
        }

        public function testGdScale()
        {
            $photo = $this->getDummyFile();

            $fm = $this->gd->getFileManager();
            $filename = $fm->save($photo, 'news');
            $absolute = $fm->getAbsolutePath('news', $filename);
            $this->gd->scaleImage($absolute, 'news', 120, 0);
        }

        /**
         * @param string $res
         *
         * @return UploadedFile
         */
        private function getDummyFile($res = 'tomask.png')
        {
            $photo = new UploadedFile(__DIR__ . '/Resources/' . $res, $res, 'image/png', filesize(__DIR__ . '/Resources/' . $res), null, true);
            $newFileName = $this->gd->getFileManager()->getFilenameGenerator()->generateRandom($photo);
            copy(__DIR__ . '/Resources/' . $res, __DIR__ . '/Resources/' . $newFileName);

            return new UploadedFile(__DIR__ . '/Resources/' . $newFileName, $newFileName, 'image/png', filesize(__DIR__ . '/Resources/' . $newFileName), null, true);
        }

        public function testGdScaleLarge()
        {
            $photo = $this->getDummyFile('tomask_p.png');

            $fm = $this->gd->getFileManager();
            $filename = $fm->save($photo, 'news');
            $absolute = $fm->getAbsolutePath('news', $filename);

            $this->gd->scaleLargeEdge($absolute, 'news', 40);
        }

        public function testGdClip()
        {
            $photo = $this->getDummyFile();

            $fm = $this->gd->getFileManager();

            $filename = $fm->save($photo, 'news');
            $filename = $fm->getAbsolutePath('news', $filename);

            $this->gd->clipImage($filename, 'news', __DIR__ . '/Resources/mask.png');
        }

        public function testImagickScale()
        {
            $photo = $this->getDummyFile();

            $fm = $this->imagick->getFileManager();
            $filename = $fm->save($photo, 'news');
            $absolute = $fm->getAbsolutePath('news', $filename);
            $this->imagick->scaleImage($absolute, 'news', 120, 0);
        }

        public function testImagickScaleLarge()
        {
            $photo = $this->getDummyFile('tomask_p.png');

            $fm = $this->imagick->getFileManager();
            $filename = $fm->save($photo, 'news');
            $absolute = $fm->getAbsolutePath('news', $filename);

            $this->imagick->scaleLargeEdge($absolute, 'news', 40);
        }

        public function testImagickClip()
        {
            $photo = $this->getDummyFile();

            $fm = $this->imagick->getFileManager();

            $filename = $fm->save($photo, 'news');
            $filename = $fm->getAbsolutePath('news', $filename);

            $this->imagick->clipImage($filename, 'news', __DIR__ . '/Resources/mask.png');
        }
    }
 