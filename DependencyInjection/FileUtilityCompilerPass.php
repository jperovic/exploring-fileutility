<?php
    namespace Exploring\FileUtilityBundle\DependencyInjection;

    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    class FileUtilityCompilerPass implements CompilerPassInterface
    {
        /**
         * You can modify the container here before it is dumped to PHP code.
         *
         * @param ContainerBuilder $container
         *
         * @api
         */
        public function process(ContainerBuilder $container)
        {
            $uploadRoot = $container->getParameter("exploring_file_utility.upload_root");
            $filenameGenerator = $container->getParameter("exploring_file_utility.filename_generator");

            $availableSubdirs = array();
            /** @var \DirectoryIterator[] $dirIterator */
            $dirIterator = new \DirectoryIterator($uploadRoot);

            foreach ( $dirIterator as $dir ) {
                if ( $dir->isDir() && !$dir->isDot() ) {
                    $availableSubdirs[] = $dir->getFilename();
                }
            }

            if ( $filenameGenerator ) {
                $filenameGenerator = new Reference($container->getParameter(
                    "exploring_file_utility.filename_generator"
                ));
            }

            $container->getDefinition("exploring_file_utility.manager")
                ->setArguments(array($availableSubdirs, $uploadRoot, $filenameGenerator));

            $chainExecutorRef = NULL;
            $chains = $container->getParameter('exploring_file_utility.image_chains');

            if ( $chains ) {
                $taggedChainSteps = array_keys($container->findTaggedServiceIds('exploring_file_utility.image_chain_step'));

                $stepRefs = array();

                foreach ( $taggedChainSteps as $stepName ) {
                    $stepRefs[] = $container->getDefinition($stepName);
                }

                $container->getDefinition('exploring_file_utility.image_chain_executor')
                    ->setArguments(array($chains, $stepRefs));

                $chainExecutorRef = new Reference('exploring_file_utility.image_chain_executor');
            }

            $imageEngineService = $container->getParameter("exploring_file_utility.image_engine");

            switch ( $imageEngineService ) {
                case Constants::ENGINE_GD:
                    $arguments = array(
                        new Reference("exploring_file_utility.manager"),
                        new Reference("exploring_file_utility.imageengine_gd"),
                        $chainExecutorRef
                    );
                    break;
                case Constants::ENGINE_IMAGICK:
                    $arguments = array(
                        new Reference("exploring_file_utility.manager"),
                        new Reference("exploring_file_utility.imageengine_imagick"),
                        $chainExecutorRef
                    );
                    break;
                default:
                    $arguments = array(
                        new Reference("exploring_file_utility.manager"),
                        new Reference($imageEngineService),
                        $chainExecutorRef
                    );
            }

            $container->getDefinition("exploring_file_utility.imageprocessor")
                ->setArguments($arguments);
        }
    }