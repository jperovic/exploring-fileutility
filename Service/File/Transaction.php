<?php
    namespace Exploring\FileUtilityBundle\Service\File;

    use Exploring\FileUtilityBundle\Data as Data;
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
         * @param string              $directoryAlias
         * @param bool                $temp
         *
         * @return Data\File
         */
        function save($file, $directoryAlias, $temp = false)
        {
            $target = $this->manager->getUploadPath($directoryAlias);

            if ($file instanceof UploadedFile) {
                $newFileName = $this->manager->getFilenameGenerator()->generateRandom($file);
                $newRealPath = $target . $newFileName;
                $file->move($target, $newFileName);

                $handle = new File($newRealPath, true);
            } else {
                $handle = new File($file, true);
                $file = new UploadedFile($handle->getRealPath(), $handle->getFilename());
                $newFileName = $this->manager->getFilenameGenerator()->generateRandom($file);
                $newRealPath = $target . DIRECTORY_SEPARATOR . $newFileName;
                copy($handle->getRealPath(), $newRealPath);
            }

            $mimeType = $handle->getMimeType();
            $size = $handle->getSize();
            $extension = $handle->getExtension();

            $this->enties[] = new TransactionEntry(TransactionEntry::UPLOAD, $newRealPath);

            if ($temp) {
                $this->tempFiles[] = $newRealPath;
            }

            return new Data\File($newFileName, $extension, $directoryAlias, $mimeType, $size);
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
                $target = $this->manager->getUploadPath($directoryAlias);
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