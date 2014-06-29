<?php
    namespace Exploring\FileUtilityBundle\Service\File;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Symfony\Component\HttpFoundation\File\File;

    class Transaction
    {
        /** @var FileManager */
        private $manager;

        /** @var TransactionEntry[] */
        private $entries;

        /** @var array */
        private $temporaryFiles;

        function __construct(FileManager $manager)
        {
            $this->manager = $manager;

            $this->entries = array();

            $this->temporaryFiles = array();
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param bool   $isTemporary
         * @param bool   $keepOriginal
         *
         * @return FileWrapper
         */
        function save(File $file, $saveToAlias, $isTemporary = false, $keepOriginal = false)
        {
            $target = $this->manager->resolveDirectoryAlias($saveToAlias);

            $realPath = $file->getRealPath();

            if (strpos($realPath, $target) === 0) {
                $newRealPath = $realPath;
            } else {
                $newFileName = $this->manager->getFilenameGenerator()->generateRandom($file, $isTemporary);
                $newRealPath = $target . $newFileName;

                if ($keepOriginal) {
                    if ($isTemporary && array_key_exists($file->getFilename(), $this->temporaryFiles)) {
                        $newRealPath = $this->temporaryFiles[$file->getFilename()];
                    } else {
                        copy($file->getRealPath(), $newRealPath);
                    }
                } else {
                    $file->move($target, $newFileName);
                }
            }

            // create the reference to new file
            $wrap = new FileWrapper($newRealPath, $saveToAlias);

            $this->entries[] = new TransactionEntry(TransactionEntry::UPLOAD, $newRealPath);

            if ($isTemporary) {
                $this->temporaryFiles[$file->getFilename()] = $newRealPath;
            }

            return $wrap;
        }

        /**
         * @param string $filename
         * @param string $directoryAlias
         *
         * @throws FileManagerException
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
                $this->entries[] = new TransactionEntry(TransactionEntry::REMOVE, $entryData);

                return true;
            }

            return false;
        }

        function commit()
        {
            foreach ($this->entries as $e) {
                if ($e->getAction() == TransactionEntry::REMOVE) {
                    $data = $e->getData();
                    @unlink($data[1]);
                }
            }

            foreach ($this->temporaryFiles as $tmp) {
                @unlink($tmp);
            }
        }

        function rollback()
        {
            while (!empty($this->entries)) {
                /** @var TransactionEntry $e */
                $e = array_pop($this->entries);
                $data = $e->getData();

                if ($e->getAction() == TransactionEntry::UPLOAD) {
                    @unlink($data);
                } else { // REMOVE
                    rename($data[1], $data[0]);
                }
            }
        }
    }