<?php
    /**
     * Created by PhpStorm.
     * User: jovan
     * Date: 12/9/13
     * Time: 11:02 PM
     */

    namespace Exploring\FileUtilityBundle\Tests;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\DependencyInjection\Container;
    use Symfony\Component\HttpFoundation\File\File;

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

            $this->fm = new FileManager(array('t' => 'temp'), __DIR__ . '/Resources/');
            $this->testFile = new File(__DIR__ . '/Resources/tomask.png');
        }

        protected function tearDown()
        {
            $this->fm->rollback();
        }

        public function testUpload()
        {
            $wrap = $this->fm->save($this->testFile, 't', false, true);

            $this->assertTrue(file_exists($wrap->getFile()->getRealPath()));

            $this->fm->rollback();

            $this->assertFalse(file_exists($wrap->getFile()->getRealPath()));
        }

        public function testUploadThenRemoveFile()
        {
            $wrap = $this->fm->save($this->testFile, 't', false, true);

            $this->fm->commit();

            $this->assertTrue(file_exists($wrap->getFile()->getRealPath()));

            $this->fm->removeFile($wrap);

            $this->fm->commit();

            $this->assertFalse(file_exists($wrap->getFile()->getRealPath()));
        }

        public function testUploadFileRemoveItThenRollback()
        {
            $wrap = $this->fm->save($this->testFile, 't', false, true);

            $this->fm->commit();

            $absolutePath = $wrap->getFile()->getRealPath();
            $this->assertTrue(file_exists($absolutePath));

            $this->fm->removeFile($wrap);

            $this->fm->rollback();

            $this->assertTrue(file_exists($absolutePath));

            $this->fm->removeFile($wrap);

            $this->fm->commit();
        }

        public function testUploadAndGuessDirectoryAlias()
        {
            $wrap = $this->fm->save($this->testFile, 't', false, true);

            $alias = $this->fm->guessDirectoryAliasOfFile($wrap->getFile());

            $this->assertEquals('t', $alias);
        }

        public function testUploadThenRebuildTestWrapperUsingFilenameAndAlias()
        {
            $wrap = $this->fm->save($this->testFile, 't', false, true);

            $filename = $wrap->getFile()->getFilename();
            $alias = $wrap->getDirectoryAlias();

            $wrap2 = $this->fm->getFile($filename, $alias);

            $this->assertEquals($filename, $wrap2->getFile()->getFilename());
            $this->assertEquals($alias, $wrap2->getDirectoryAlias());
        }

        /**
         * @expectedException \Exploring\FileUtilityBundle\Service\File\FileManagerException
         */
        public function testResolveInvalidAliasToDirectory()
        {
            $this->fm->resolveDirectoryAlias('some.non.existent.alias');
        }

        public function testStripUploadPathFromRealPath()
        {
            $wrap = $this->fm->save($this->testFile, 't', false, true);

            $absoulute = $wrap->getFile()->getRealPath();

            $striped = $this->fm->stripAbsolutePath($absoulute, 't');

            $this->assertNotNull($striped);

            $this->assertEquals($striped, $wrap->getFile()->getFilename());
        }

        public function testUploadFileThenStartAnotherTransactionAndRemoveItThenRollbackLast()
        {
            $wrap = $this->fm->save($this->testFile, 't', false, true);

            $this->assertTrue(file_exists($wrap->getFile()->getRealPath()));

            $this->fm->beginTransaction();

            $this->fm->removeFile($wrap);

            $this->assertFalse(file_exists($wrap->getFile()->getRealPath()));

            $this->fm->rollback(true);

            $this->assertTrue(file_exists($wrap->getFile()->getRealPath()));
        }



//        public function testUpload()
//        {
//            $photo = new UploadedFile(__DIR__ . '/Resources/tomask.png', 'tomask.png', 'image/png', filesize(
//                __DIR__ . '/Resources/tomask.png'
//            ), null, true);
//            $newFileName = $this->manager->getFilenameGenerator()->generateRandom($photo);
//            copy(__DIR__ . '/Resources/tomask.png', __DIR__ . '/Resources/' . $newFileName);
//            $photo = new UploadedFile(__DIR__ . '/Resources/' . $newFileName, $newFileName, 'image/png', filesize(
//                __DIR__ . '/Resources/' . $newFileName
//            ), null, true);
//
//            $newFileName = $this->manager->beginTransaction()->save($photo, 'news');
//
//            $this->manager->commit();
//
//            $this->manager->beginTransaction()->remove($newFileName, 'news');
//
//            $this->manager->rollback();
//
//            $this->manager->remove($newFileName, 'news');
//        }
//
//        public function testReferenceFile()
//        {
//            $photo = new UploadedFile(__DIR__ . '/Resources/tomask.png', 'tomask.png', 'image/png', filesize(
//                __DIR__ . '/Resources/tomask.png'
//            ), null, true);
//            $newFileName = $this->manager->getFilenameGenerator()->generateRandom($photo);
//            $newFullPath = $this->manager->resolveDirectoryAlias('news') . $newFileName;
//            copy(__DIR__ . '/Resources/tomask.png', $newFullPath);
//
//            $M = $this->manager->beginTransaction();
//
//            $M->save($newFullPath, 'news');
//            $M->remove($newFileName, 'news');
//        }
    }
 