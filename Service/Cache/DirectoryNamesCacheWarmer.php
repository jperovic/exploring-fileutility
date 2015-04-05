<?php
    namespace Exploring\FileUtilityBundle\Service\Cache;

    use Monolog\Logger;
    use Symfony\Component\Config\ConfigCache;
    use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;

    class DirectoryNamesCacheWarmer extends CacheWarmer
    {
        /**
         * @var Logger
         */
        private $logger;

        /**
         * @var bool
         */
        private $debug;

        /**
         * @var
         */
        private $uploadRoot;

        function __construct($uploadRoot, $debug, Logger $logger)
        {
            $this->logger = $logger;
            $this->debug = $debug;
            $this->uploadRoot = $uploadRoot;
        }

        /**
         * Checks whether this warmer is optional or not.
         *
         * Optional warmers can be ignored on certain conditions.
         *
         * A warmer should return true if the cache can be
         * generated incrementally and on-demand.
         *
         * @return bool    true if the warmer is optional, false otherwise
         */
        public function isOptional()
        {
            return TRUE;
        }

        /**
         * Warms up the cache.
         *
         * @param string $cacheDir The cache directory
         */
        public function warmUp($cacheDir)
        {
            $classNamespace = __NAMESPACE__;
            $className = "FileUtilityDirectory";

            /** @var \DirectoryIterator[] $dirIterator */
            $dirIterator = new \DirectoryIterator($this->uploadRoot);

            $code = "<?php namespace $classNamespace {class $className{";

            foreach ( $dirIterator as $dir ) {
                if ( $dir->isDir() && !$dir->isDot() ) {
                    $normalizedName = strtoupper(preg_replace("/([a-z])([A-Z0-9])|([0-9])([a-z])/", "$1_$2", $dir));

                    // Constants cannot start with digit
                    if ( preg_match('/^[0-9]/', $normalizedName) ) {
                        $normalizedName = '_' . $normalizedName;
                    }

                    $code .= "const " . strtoupper($normalizedName) . "=\"$dir\";";
                    $this->logger->debug("Found directory: " . $dir . "; normalized: $normalizedName");
                }
            }

            $code .= "}}";

            $cache = new ConfigCache(__DIR__ . '/' . $className . '.php', $this->debug);
            if ( !$cache->isFresh() ) {
                $cache->write($code, NULL);
            }

            /** @noinspection PhpIncludeInspection */
            require_once $cache;
        }
    }