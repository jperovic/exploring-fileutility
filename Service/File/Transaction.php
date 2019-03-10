<?php
    namespace Exploring\FileUtilityBundle\Service\File;

    use Exploring\FileUtilityBundle\Data\FileDescriptor;
    use Symfony\Component\HttpFoundation\File\File;

    class Transaction
    {
        /** @var FileManager */
        private $manager;

        /** @var TransactionEntry[] */
        private $entries;

        /** @var array */
        private $temporaryFiles;

        /**
         * @param FileManager $manager
         */
        function __construct(FileManager $manager)
        {
            $this->manager = $manager;

            $this->entries = array();

            $this->temporaryFiles = array();
        }

        /**
         * @param File   $file
         * @param string $targetDirectory
         * @param bool   $isTemporary
         * @param bool   $keepSourceFile
         *
         * @throws FileManagerException
         * @return FileDescriptor
         */
        function save(File $file, $targetDirectory, $isTemporary = false, $keepSourceFile = false)
        {
            $target = $this->manager->getRealPath($targetDirectory);

            $realPath = $file->getRealPath();

            if (strpos($realPath, $target) === 0) {
                $newRealPath = $realPath;
            } else {
                $newFileName = $this->manager->getFilenameGenerator()->generateRandom($file, $isTemporary);

                if (!$newFileName) {
                    throw new FileManagerException("Filename generator's generateRandom() must return string but the result was empty. Did you implement it properly?");
                }

                $newRealPath = $target . $newFileName;

                if ($keepSourceFile) {
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
            $wrap = new FileDescriptor($newRealPath, $targetDirectory);

            $this->entries[] = new TransactionEntry(TransactionEntry::UPLOAD, $newRealPath);

            if ($isTemporary) {
                $this->temporaryFiles[$file->getFilename()] = $newRealPath;
            }

            return $wrap;
        }

        /**
         * @param string $filename
         * @param string $directory
         *
         * @throws FileManagerException
         * @return bool
         */
        function remove($filename, $directory)
        {
            if ($filename !== null) {
                $target = $this->manager->getRealPath($directory);
                $absolutePath = realpath($target . $filename);

                if ($absolutePath === false) {
                    throw new FileManagerException("File \"$filename\" does not exist.");
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
                    $data = $e->getPayload();
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
                $data = $e->getPayload();

                if ($e->getAction() == TransactionEntry::UPLOAD) {
                    @unlink($data);
                } else { // REMOVE
                    rename($data[1], $data[0]);
                }
            }
        }
    }