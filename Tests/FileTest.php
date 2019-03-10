<?php
    namespace Exploring\FileUtilityBundle\Tests;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\DependencyInjection\Container;
    use Symfony\Component\HttpFoundation\File\File;

    /**
     * Class FileTest
     * @package Exploring\FileUtilityBundle\Tests
     */
    class FileTest extends WebTestCase
    {
        /** @var Container */
        private static $container;

        /** @var  FileManager */
        private $fm;

        /** @var File */
        private $testFile;

        /**
         * {@inheritDoc}
         */
        public function setUp()
        {
            static::$kernel = static::createKernel();
            static::$kernel->boot();
            static::$container = static::$kernel->getContainer();

            $this->fm = new FileManager(array('temp'), __DIR__ . '/Resources');
            $this->testFile = new File(__DIR__ . '/Resources/tomask.png');
        }

        protected function tearDown()
        {
            $this->fm->rollback();
        }

        public function testUpload()
        {
            $wrap = $this->fm->save($this->testFile, 'temp', FALSE, TRUE);

            $this->assertTrue(file_exists($wrap->getFile()->getRealPath()));

            $this->fm->rollback();

            $this->assertFalse(file_exists($wrap->getFile()->getRealPath()));
        }

        public function testUploadThenRemoveFile()
        {
            $wrap = $this->fm->save($this->testFile, 'temp', FALSE, TRUE);

            $this->fm->commit();

            $this->assertTrue(file_exists($wrap->getFile()->getRealPath()));

            $this->fm->removeFile($wrap);

            $this->fm->commit();

            $this->assertFalse(file_exists($wrap->getFile()->getRealPath()));
        }

        public function testUploadFileRemoveItThenRollback()
        {
            $wrap = $this->fm->save($this->testFile, 'temp', FALSE, TRUE);

            $this->fm->commit();

            $absolutePath = $wrap->getFile()->getRealPath();
            $this->assertTrue(file_exists($absolutePath));

            $this->fm->removeFile($wrap);

            $this->fm->rollback();

            $this->assertTrue(file_exists($absolutePath));

            $this->fm->removeFile($wrap);

            $this->fm->commit();
        }

        public function testUploadAndGuessDirectory()
        {
            $wrap = $this->fm->save($this->testFile, 'temp', FALSE, TRUE);

            $directory = $this->fm->guessDirectoryOfFile($wrap->getFile());

            $this->assertEquals('temp', $directory);
        }

        public function testUploadThenRebuildTestWrapperUsingFilenameAndDirectory()
        {
            $wrap = $this->fm->save($this->testFile, 'temp', FALSE, TRUE);

            $filename = $wrap->getFile()->getFilename();
            $directory = $wrap->getDirectory();

            $wrap2 = $this->fm->getFileDescriptor($filename, $directory);

            $this->assertEquals($filename, $wrap2->getFile()->getFilename());
            $this->assertEquals($directory, $wrap2->getDirectory());
        }

        /**
         * @expectedException \Exploring\FileUtilityBundle\Service\File\FileManagerException
         */
        public function testResolveInvalidDirectory()
        {
            $this->fm->getRealPath('some.non.existent.directory');
        }

        public function testStripUploadPathFromRealPath()
        {
            $wrap = $this->fm->save($this->testFile, 'temp', FALSE, TRUE);

            $absoulute = $wrap->getFile()->getRealPath();

            $striped = $this->fm->stripAbsolutePath($absoulute, 'temp');

            $this->assertNotNull($striped);

            $this->assertEquals($striped, $wrap->getFile()->getFilename());
        }

        /**
         * @expectedException \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
         */
        public function testTryToResolveInvalidFile()
        {
            $this->fm->getFileDescriptor('foo.jpg', 'temp');
        }
    }
 