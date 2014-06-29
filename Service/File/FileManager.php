<?php
    namespace Exploring\FileUtilityBundle\Service\File;

    use Exploring\FileUtilityBundle\Data\FileWrapper;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\DefaultFilenameGenerator;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\FilenameGeneratorInterface;
    use InvalidArgumentException;
    use Symfony\Component\HttpFoundation\File\File;

    /**
     * Created by JetBrains PhpStorm.
     * User: root
     * Date: 2/7/13
     * Time: 7:44 PM
     * To change this template use File | Settings | File Templates.
     */
    class FileManager
    {
        const UPLOAD_DIR = "/tmp";

        const QUEUE_OLD = "__old__";

        const QUEUE_NEW = "__new__";

        const QUEUE_TEMP = "__temp__";

        /**
         * @var string[]
         */
        private $directoryAliases = array();

        /**
         * @var string
         */
        private $uploadsRoot;

        /**
         * @var FilenameGeneratorInterface
         */
        private $filenameGenerator;

        /**
         * @var Transaction[]
         */
        private $transactions = array();

        /**
         * @param array                             $directoryAliases
         * @param string                            $uploadsRoot
         * @param FilenameGeneratorInterface|string $filenameGenerator
         *
         * @throws InvalidArgumentException
         */
        function __construct($directoryAliases, $uploadsRoot = self::UPLOAD_DIR, FilenameGeneratorInterface $filenameGenerator = null)
        {
            $this->uploadsRoot = rtrim($uploadsRoot, DIRECTORY_SEPARATOR);

            $this->filenameGenerator = $filenameGenerator ? $filenameGenerator : new DefaultFilenameGenerator();

            foreach ($directoryAliases as $alias => $directory) {
                $this->directoryAliases[$alias] = $this->uploadsRoot . ($directory ? DIRECTORY_SEPARATOR . $directory : '') . DIRECTORY_SEPARATOR;
            }

            $this->transactions = array();
        }

        function __destruct()
        {
            $this->rollback();
        }

        /**
         * @param bool $onlyLatestTransaction
         *
         * @throws FileManagerException
         * @return $this
         */
        public function rollback($onlyLatestTransaction = false)
        {
            if (!$this->hasActiveTransaction()) {
                return $this;
            }

            do {
                /** @var Transaction $transaction */
                $transaction = array_pop($this->transactions);

                $transaction->rollback();
                if ($onlyLatestTransaction) {
                    break;
                }
            } while ($this->hasActiveTransaction());

            return $this;
        }

        /**
         * @return bool
         */
        private function hasActiveTransaction()
        {
            return count($this->transactions) > 0;
        }

        /**
         * @param File   $file
         * @param string $saveToAlias
         * @param bool   $temp
         * @param bool   $keepOriginal
         *
         * @return FileWrapper
         */
        public function save(File $file, $saveToAlias, $temp = false, $keepOriginal = false)
        {
            return $this->getTransaction()->save($file, $saveToAlias, $temp, $keepOriginal);
        }

        /**
         * @return $this
         */
        public function beginTransaction()
        {
            $this->transactions[] = new Transaction($this);

            return $this;
        }

        /**
         * @param string $filename
         * @param string $directoryAlias
         * @param bool   $check
         *
         * @return string
         */
        public function getAbsolutePath($filename, $directoryAlias, $check = false)
        {
            $handle = new File($this->resolveDirectoryAlias($directoryAlias) . $filename, $check);

            return $handle->getPath() . DIRECTORY_SEPARATOR . $handle->getFilename();
        }

        /**
         * @param $filename
         * @param $directoryAlias
         *
         * @return FileWrapper
         */
        public function getFile($filename, $directoryAlias)
        {
            $absolute = $this->getAbsolutePath($filename, $directoryAlias, true);

            return new FileWrapper($absolute, $directoryAlias);
        }

        public function guessDirectoryAliasOfFile(File $file)
        {
            $real = $file->getRealPath();
            $aliasNames = array_keys($this->directoryAliases);
            foreach ($aliasNames as $k) {
                $stripped = $this->stripAbsolutePath($real, $k);

                if ($stripped !== null) {
                    return $k;
                }
            }

            throw new InvalidArgumentException(sprintf(
                "Given file \"%s\" does not belong to any configured directory alias.",
                $real
            ));
        }

        /**
         * @param string $alias
         *
         * @throws FileManagerException
         * @return null|string
         */
        public function resolveDirectoryAlias($alias)
        {
            if (!array_key_exists($alias, $this->directoryAliases)) {
                $availableAliases = implode(
                    ',',
                    array_map(
                        function ($item) {
                            return "\"$item\"";
                        },
                        array_keys($this->directoryAliases)
                    )
                );
                throw new FileManagerException(sprintf(
                    "Directory alias \"%s\" was not defined. Available aliases are: [%s]",
                    $alias,
                    $availableAliases
                ));
            }

            $realPath = realpath($this->directoryAliases[$alias]);

            if (!$realPath) {
                throw new FileManagerException(sprintf(
                    "Path to directory alias \"%s\" does not exist. Tried: \"%s\"",
                    $alias,
                    $this->directoryAliases[$alias]
                ));
            }

            if (!is_writable($realPath)) {
                throw new FileManagerException(sprintf(
                    "Path of directory alias \"%s\" is not writable. Tried to write to: \"%s\"",
                    $alias,
                    $realPath
                ));
            }

            return $realPath . DIRECTORY_SEPARATOR;
        }

        /**
         * @param $path
         * @param $directoryAlias
         *
         * @return null|string
         *
         * TODO: Sta sa ovom f-om?
         *
         */
        public function stripAbsolutePath($path, $directoryAlias)
        {
            if ($directoryAlias == null) {
                return $path;
            }

            $dirpath = $this->resolveDirectoryAlias($directoryAlias);
            if (strpos($path, $dirpath) === 0) {
                return substr($path, strlen($dirpath));
            }

            return null;
        }

        /**
         * @param string $filename
         * @param string $directoryAlias
         *
         * @return $this
         */
        public function remove($filename, $directoryAlias)
        {
            $this->getTransaction()->remove($filename, $directoryAlias);

            return $this;
        }

        /**
         * @param FileWrapper $file
         *
         * @return $this
         */
        public function removeFile(FileWrapper $file)
        {
            return $this->remove($file->getFile()->getFilename(), $file->getDirectoryAlias());
        }

        /**
         * @throws FileManagerException
         * @return $this
         */
        public function commit()
        {
            if (!$this->hasActiveTransaction()) {
                return $this;
            }

            do {
                /** @var Transaction $transaction */
                $transaction = array_pop($this->transactions);
                $transaction->commit();

            } while ($this->hasActiveTransaction());

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
            if (!$this->hasActiveTransaction()) {
                $this->beginTransaction();
            }

            $lastIndex = count($this->transactions) - 1;

            return $this->transactions[$lastIndex];
        }
    }
