<?php
    /**
     * Created by PhpStorm.
     * User: jovan
     * Date: 12/9/13
     * Time: 11:02 PM
     */

    namespace Exploring\Tests;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class FileTest extends \PHPUnit_Framework_TestCase
    {
        /** @var FileManager */
        private $manager;

        /**
         * {@inheritDoc}
         */
        public function setUp()
        {
            $this->manager = new FileManager(array('news' => 'novosti'), __DIR__ . '/Resources/');
        }

        public function testUpload()
        {
            $photo = new UploadedFile(__DIR__ . '/Resources/tomask.png', 'tomask.png', 'image/png', filesize(
                __DIR__ . '/Resources/tomask.png'
            ), null, true);
            $newFileName = $this->manager->getFilenameGenerator()->generateRandom($photo);
            copy(__DIR__ . '/Resources/tomask.png', __DIR__ . '/Resources/' . $newFileName);
            $photo = new UploadedFile(__DIR__ . '/Resources/' . $newFileName, $newFileName, 'image/png', filesize(
                __DIR__ . '/Resources/' . $newFileName
            ), null, true);

            $newFileName = $this->manager->beginTransaction()->save($photo, 'news');

            $this->manager->commit();

            $this->manager->beginTransaction()->remove($newFileName, 'news');

            $this->manager->rollback();

            $this->manager->remove($newFileName, 'news');
        }

        public function testReferenceFile()
        {
            $photo = new UploadedFile(__DIR__ . '/Resources/tomask.png', 'tomask.png', 'image/png', filesize(
                __DIR__ . '/Resources/tomask.png'
            ), null, true);
            $newFileName = $this->manager->getFilenameGenerator()->generateRandom($photo);
            $newFullPath = $this->manager->getUploadPath('news') . $newFileName;
            copy(__DIR__ . '/Resources/tomask.png', $newFullPath);

            $M = $this->manager->beginTransaction();

            $M->save($newFullPath, 'news');
            $M->remove($newFileName, 'news');
        }
    }
 