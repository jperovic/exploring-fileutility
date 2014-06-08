<?php
    namespace Exploring\FileUtilityBundle\Service\File;

    use Exploring\FileUtilityBundle\Utility\NameGenerator\DefaultFilenameGenerator;
    use Exploring\FileUtilityBundle\Utility\NameGenerator\FilenameGenerator;
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

        /**exploring_file_utility
         * @var string[]
         */
        private $uploadSubPaths = array();

        /**
         * @var string
         */
        private $uploadsRoot;

        /**
         * @var FilenameGenerator
         */
        private $filenameGenerator;

        /**
         * @var Transaction[]
         */
        private $transactions = array();

        /**
         * @param array             $folders
         * @param string            $uploadsRoot
         * @param FilenameGenerator $filenameGenerator
         */
        function __construct($folders, $uploadsRoot = self::UPLOAD_DIR, $filenameGenerator = null)
        {
            $this->uploadsRoot = rtrim($uploadsRoot, DIRECTORY_SEPARATOR);
            $this->filenameGenerator = $filenameGenerator ? $filenameGenerator : new DefaultFilenameGenerator();

            foreach ($folders as $k => $f) {
                $this->uploadSubPaths[$k] = $this->uploadsRoot . ($f ? DIRECTORY_SEPARATOR . $f : '') . DIRECTORY_SEPARATOR;
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
                throw new FileManagerException("No active transaction.");
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
         * @param File|string $file
         * @param string      $directory
         * @param bool        $temp
         *
         * @return string
         */
        public function save($file, $directory, $temp = false)
        {
            return $this->getTransaction()->save($file, $directory, $temp);
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

        /**
         * @return $this
         */
        public function beginTransaction()
        {
            $this->transactions[] = new Transaction($this);

            return $this;
        }

        /**
         * @param string $directory
         * @param string $filename
         * @param bool   $check
         *
         * @return string
         */
        public function getAbsolutePath($directory, $filename, $check = false)
        {
            $handle = new File($this->getUploadPath($directory) . $filename, $check);

            return $handle->getPath() . DIRECTORY_SEPARATOR . $handle->getFilename();
        }

        /**
         * @param string $directory
         *
         * @throws FileManagerException
         * @return null|string
         */
        public function getUploadPath($directory)
        {
            if (!array_key_exists($directory, $this->uploadSubPaths)) {
                throw new FileManagerException(sprintf("Directory \"%s\" not registered.", $directory));
            }

            $realPath = realpath($this->uploadSubPaths[$directory]);

            if (!$realPath) {
                throw new FileManagerException(sprintf("Missing destination for directory \"%s\". Tried: \"%s\"", $directory, $this->uploadSubPaths[$directory]));
            }

            if (!is_writable($realPath)) {
                throw new FileManagerException(sprintf("Directory \"%s\" not writable. Tried to write to: \"%s\"", $directory, $realPath));
            }

            return $realPath . DIRECTORY_SEPARATOR;
        }

        public function stripAbsolutePath($path, $directory)
        {
            if ($directory == null) {
                return $path;
            }

            $dirpath = $this->getUploadPath($directory);
            if (strpos($path, $dirpath) === 0) {
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
         * @param FilenameGenerator $filenameGenerator
         */
        public function setFilenameGenerator(FilenameGenerator $filenameGenerator)
        {
            $this->filenameGenerator = $filenameGenerator;
        }

        /**
         * @return FilenameGenerator
         */
        public function getFilenameGenerator()
        {
            return $this->filenameGenerator;
        }
    }
