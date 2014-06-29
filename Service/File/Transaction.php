<?php
    namespace Exploring\FileUtilityBundle\Service\File;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Symfony\Component\HttpFoundation\File\File;

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
         * @param File   $file
         * @param string $saveToAlias
         * @param bool   $temp
         * @param bool   $keepOriginal
         *
         * @return FileWrapper
         */
        function save(File $file, $saveToAlias, $temp = false, $keepOriginal = false)
        {
            $target = $this->manager->resolveDirectoryAlias($saveToAlias);

            $realPath = $file->getRealPath();

            if (strpos($realPath, $target) === 0) {
                $newRealPath = $realPath;
            } else {
                $newFileName = $this->manager->getFilenameGenerator()->generateRandom($file, $temp);
                $newRealPath = $target . $newFileName;

                if ($keepOriginal) {
                    copy($file->getRealPath(), $newRealPath);
                } else {
                    $file->move($target, $newFileName);
                }
            }

            // create the reference to new file
            $file = new FileWrapper($newRealPath, $saveToAlias);

            $this->enties[] = new TransactionEntry(TransactionEntry::UPLOAD, $newRealPath);

            if ($temp) {
                $this->tempFiles[] = $newRealPath;
            }

            return $file;
        }

        /**
         * @param string $filename
         * @param string $directoryAlias
         *
         * @throws FileManagerException
         * @internal param string $queue
         *
         * @return bool
         */
        function remove($filename, $directoryAlias)
        {
            if ($filename !== null) {
                $target = $this->manager->resolveDirectoryAlias($directoryAlias);
                $absolutePath = realpath($target . $filename);

                if ($absolutePath === false) {
                    throw new FileManagerException(sprintf("File \"%s\" does not exist.", $filename));
                }

                $entryData = array($absolutePath, $target . md5($filename . time()));
                rename($absolutePath, $entryData[1]);
                $this->enties[] = new TransactionEntry(TransactionEntry::REMOVE, $entryData);

                return true;
            }

            return false;
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
                } else {
                    rename($data[1], $data[0]);
                }
            }
        }
    }