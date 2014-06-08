<?php
    namespace Exploring\FileUtilityBundle\Service\File;

    use Exploring\FileUtilityBundle\Service\File\FileManager;
    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class Transaction
    {
        /** @var FileManager */
        private $manager;

        /** @var TransactionEntry[] */
        private $enties;

        /** @var array */
        private $tempFiles;

        function __construct(FileManager $manager)
        {
            $this->manager = $manager;

            $this->enties = array();

            $this->tempFiles = array();
        }

        /**
         * @param UploadedFile|string $file
         * @param string              $directory
         * @param bool                $temp
         *
         * @return string
         */
        function save($file, $directory, $temp = false)
        {
            $target = $this->manager->getUploadPath($directory);

            if (is_string($file)) {
                $handle = new File($file, true);
                $newFileName = $handle->getFilename();
                $newRealPath = $handle->getRealPath();
            }
            else {
                $newFileName = $this->manager->getFilenameGenerator()->generateRandom($file);
                $file->move($target, $newFileName);
                $newRealPath = $target . $newFileName;
            }

            $this->enties[] = new TransactionEntry(TransactionEntry::UPLOAD, $newRealPath);

            if ($temp) {
                $this->tempFiles[] = $newRealPath;
            }

            return $newFileName;
        }

        /**
         * @param string $filename
         * @param string $directory
         *
         * @throws FileManagerException
         * @internal param string $queue
         *
         * @return bool
         */
        function remove($filename, $directory)
        {
            if ($filename !== NULL) {
                $target = $this->manager->getUploadPath($directory);
                $absPath = realpath($target . $filename);

                if ($absPath === FALSE) {
                    throw new FileManagerException(sprintf("File \"%s\" does not exist.", $filename));
                }

                $entryData = array($absPath, $target . md5($filename . time()));
                rename($absPath, $entryData[1]);
                $this->enties[] = new TransactionEntry(TransactionEntry::REMOVE, $entryData);

                return TRUE;
            }

            return FALSE;
        }

        function commit()
        {
            foreach ($this->enties as $e) {
                if ($e->getAction() == TransactionEntry::REMOVE) {
                    $data = $e->getData();
                    @unlink($data[1]);
                }
            }

            foreach ($this->tempFiles as $tmp) {
                @unlink($tmp);
            }
        }

        function rollback()
        {
            while (!empty($this->enties)) {
                /** @var TransactionEntry $e */
                $e = array_pop($this->enties);
                $data = $e->getData();

                if ($e->getAction() == TransactionEntry::UPLOAD) {
                    @unlink($data);
                }
                else {
                    rename($data[1], $data[0]);
                }
            }
        }
    }