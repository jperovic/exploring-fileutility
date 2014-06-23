<?php
    namespace Exploring\FileUtilityBundle\DependencyInjection;

    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    class FileUtilityCompilerPass implements CompilerPassInterface
    {
        const ENGINE_GD = "gd";

        const ENGINE_IMAGICK = "imagick";

        /**
         * You can modify the container here before it is dumped to PHP code.
         *
         * @param ContainerBuilder $container
         *
         * @api
         */
        public function process(ContainerBuilder $container)
        {
            $directoryAliases = $container->getParameter("exploring_file_utility.directories");
            $uploadRoot = $container->getParameter("exploring_file_utility.upload_root");
            $filenameGenerator = $container->getParameter("exploring_file_utility.filename_generator");

            $aliasNames = array_keys($directoryAliases);
            $keyIsNumberMatch = 0;
            for ($i = 0; $i < count($aliasNames); $i++) {
                $keyIsNumberMatch += (is_numeric($aliasNames[$i]) && $i == $aliasNames[$i]) ? 1 : 0;
            }

            if ($keyIsNumberMatch == count($directoryAliases)) {
                $normalizesAliasNames = array();
                foreach ($directoryAliases as $n) {
                    $normalizesAliasNames[] = strtolower(
                        preg_replace(
                            array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/', '/\s/'),
                            array('\\1_\\2', '\\1_\\2', '_'),
                            strtr($n, '_', '.')
                        )
                    );
                }

                $aliases = array_combine($normalizesAliasNames, $directoryAliases);
            } else {
                $aliases = $directoryAliases;
            }

            if ($filenameGenerator) {
                $filenameGenerator = new Reference($container->getParameter(
                                                             "exploring_file_utility.filename_generator"
                ));
            }

            $container->getDefinition("exploring_file_utility.manager")
                      ->setArguments(array($aliases, $uploadRoot, $filenameGenerator));

            $imageEngineService = $container->getParameter("exploring_file_utility.image_engine");

            switch ($imageEngineService) {
                case self::ENGINE_GD:
                    $arguments = array(
                        new Reference("exploring_file_utility.imageengine_gd"),
                        new Reference("exploring_file_utility.manager")
                    );
                    break;
                case self::ENGINE_IMAGICK:
                    $arguments = array(
                        new Reference("exploring_file_utility.imageengine_imagick"),
                        new Reference("exploring_file_utility.manager")
                    );
                    break;
                default:
                    $arguments = array(
                        new Reference($imageEngineService),
                        new Reference("exploring_file_utility.manager")
                    );
            }

            $container->getDefinition("exploring_file_utility.imageprocessor")
                      ->setArguments($arguments);

        }
    }