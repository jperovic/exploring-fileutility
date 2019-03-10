<?php
    namespace Exploring\FileUtilityBundle\Service\File;

    use Exploring\FileUtilityBundle\Data\FileDescriptor;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\DefaultFilenameGenerator;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\FilenameGeneratorInterface;
    use InvalidArgumentException;
    use Symfony\Component\HttpFoundation\File\File;

    class FileManager
    {
        const DEFAULT_UPLOAD_DIR = "/tmp";

        /**
         * @var string[]
         */
        private $paths = array();

        /**
         * @var string
         */
        private $uploadsRoot;

        /**
         * @var FilenameGeneratorInterface
         */
        private $filenameGenerator;

        /**
         * @var Transaction
         */
        private $transaction = NULL;

        /**
         * @param array                      $directoriesNames
         * @param string                     $uploadsRoot
         * @param FilenameGeneratorInterface $filenameGenerator
         *
         * @throws InvalidArgumentException
         */
        function __construct($directoriesNames, $uploadsRoot = self::DEFAULT_UPLOAD_DIR, FilenameGeneratorInterface $filenameGenerator = NULL)
        {
            $this->uploadsRoot = rtrim($uploadsRoot, DIRECTORY_SEPARATOR);

            $this->filenameGenerator = $filenameGenerator ? $filenameGenerator : new DefaultFilenameGenerator();

            foreach ( $directoriesNames as $dirName ) {
                $this->paths[$dirName] = $this->uploadsRoot . ($dirName ? DIRECTORY_SEPARATOR . $dirName : '') . DIRECTORY_SEPARATOR;
            }
        }

        function __destruct()
        {
            $this->rollback();
        }

        /**
         * @throws FileManagerException
         * @return $this
         */
        public function rollback()
        {
            if ( $this->hasActiveTransaction() ) {
                $this->transaction->rollback();
                $this->transaction = NULL;
            }

            return $this;
        }

        /**
         * @return bool
         */
        private function hasActiveTransaction()
        {
            return NULL !== $this->transaction;
        }

        /**
         * @param File   $file
         * @param string $targetDirectory
         * @param bool   $temp
         * @param bool   $keepSourceFile
         *
         * @return FileDescriptor
         */
        public function save(File $file, $targetDirectory, $temp = FALSE, $keepSourceFile = FALSE)
        {
            return $this->getTransaction()->save($file, $targetDirectory, $temp, $keepSourceFile);
        }

        /**
         * @return $this
         */
        public function beginTransaction()
        {
            // Check if the transaction was already created
            if ( !$this->hasActiveTransaction() ) {
                $this->transaction = new Transaction($this);
            }

            return $this;
        }

        /**
         * @param string $filename
         * @param string $directory
         * @param bool   $checkFileExists
         *
         * @return string
         */
        public function getAbsolutePath($filename, $directory, $checkFileExists = FALSE)
        {
            $handle = new File($this->getRealPath($directory) . $filename, $checkFileExists);

            return $handle->getPath() . DIRECTORY_SEPARATOR . $handle->getFilename();
        }

        /**
         * @param $filename
         * @param $directory
         *
         * @return FileDescriptor
         */
        public function getFileDescriptor($filename, $directory)
        {
            $absolute = $this->getAbsolutePath($filename, $directory, TRUE);

            return new FileDescriptor($absolute, $directory);
        }

        /**
         * @param File $file
         *
         * @return mixed
         * @throws \InvalidArgumentException
         */
        public function guessDirectoryOfFile(File $file)
        {
            $real = $file->getRealPath();
            $directories = array_keys($this->paths);
            foreach ( $directories as $k ) {
                $stripped = $this->stripAbsolutePath($real, $k);

                if ( $stripped !== NULL ) {
                    return $k;
                }
            }

            throw new InvalidArgumentException("Given file \"$real\" does not belong to any available directory.");
        }

        /**
         * @param string $directory
         *
         * @throws FileManagerException
         * @return null|string
         */
        public function getRealPath($directory)
        {
            if ( !array_key_exists($directory, $this->paths) ) {
                $directories = implode(',', array_map(function ($item) {
                        return "\"$item\"";
                    }, array_keys($this->paths))
                );
                throw new FileManagerException("Directory \"$directory\" was not found. Available directories are: [$directories]");
            }

            $realPath = realpath($this->paths[$directory]);

            if ( !$realPath ) {
                throw new FileManagerException("Path to directory \"$directory\" does not exist. Tried: \"{$this->paths[$directory]}\"");
            }

            if ( !is_writable($realPath) ) {
                throw new FileManagerException("Path of directory \"$directory\" is not writable. Tried to write to: \"$realPath\"");
            }

            return $realPath . DIRECTORY_SEPARATOR;
        }

        /**
         * @param string $path
         * @param string $directory
         *
         * @return null|string
         */
        public function stripAbsolutePath($path, $directory)
        {
            if ( $directory == NULL ) {
                return $path;
            }

            $dirpath = $this->getRealPath($directory);
            if ( strpos($path, $dirpath) === 0 ) {
                return substr($path, strlen($dirpath));
            }

            return NULL;
        }

        /**
         * @param string $filename
         * @param string $directory
         *
         * @return $this
         */
        public function remove($filename, $directory)
        {
            $this->getTransaction()->remove($filename, $directory);

            return $this;
        }

        /**
         * @param FileDescriptor|File $file
         *
         * @throws \InvalidArgumentException
         * @return $this
         */
        public function removeFile($file)
        {
            if ( !$file instanceof FileDescriptor && $file instanceof File ) {
                throw new \InvalidArgumentException(sprintf("Argument should be instance of Symfony\\Component\\HttpFoundation\\File or "
                    . "Exploring\\FileUtilityBundle\\Data\\FileDescriptor. %s given.", gettype($file)));
            }

            if ( $file instanceof FileDescriptor ) {
                return $this->remove($file->getFile()->getFilename(), $file->getDirectory());
            }
            else {
                /** @var File $file */
                $targetDirectory = $this->guessDirectoryOfFile($file);

                return $this->remove($file->getFilename(), $targetDirectory);
            }
        }

        /**
         * @throws FileManagerException
         * @return $this
         */
        public function commit()
        {
            if ( $this->hasActiveTransaction() ) {
                $this->transaction->commit();
                $this->transaction = NULL;
            }

            return $this;
        }

        /**
         * @param FilenameGeneratorInterface $filenameGenerator
         */
        public function setFilenameGenerator(FilenameGeneratorInterface $filenameGenerator)
        {
            $this->filenameGenerator = $filenameGenerator;
        }

        /**
         * @return FilenameGeneratorInterface
         */
        public function getFilenameGenerator()
        {
            return $this->filenameGenerator;
        }

        /**
         * @throws FileManagerException
         * @return Transaction
         */
        private function getTransaction()
        {
            if ( !$this->hasActiveTransaction() ) {
                $this->beginTransaction();
            }

            return $this->transaction;
        }
    }
