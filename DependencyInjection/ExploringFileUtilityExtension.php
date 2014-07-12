<?php

    namespace Exploring\FileUtilityBundle\DependencyInjection;

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader;
    use Symfony\Component\HttpKernel\DependencyInjection\Extension;

    /**
     * This is the class that loads and manages your bundle configuration
     *
     * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
     */
    class ExploringFileUtilityExtension extends Extension
    {
        /**
         * {@inheritDoc}
         */
        public function load(array $configs, ContainerBuilder $container)
        {
            $configuration = new Configuration();
            $config = $this->processConfiguration($configuration, $configs);

            $container->setParameter('exploring_file_utility.directories', $config['directories']);
            $container->setParameter('exploring_file_utility.upload_root', $config['upload_root']);
            $container->setParameter('exploring_file_utility.filename_generator', $config['filename_generator']);
            $container->setParameter('exploring_file_utility.image_engine', $config['image_engine']);

            $container->setParameter(
                      'exploring_file_utility.image_engine.gd.config',
                          array(
                              'quality' => $config['gd']['quality']
                          )
            );
            $container->setParameter(
                      'exploring_file_utility.image_engine.imagick.config',
                          array(
                              'quality' => $config['imagick']
                          )
            );

            $container->setParameter('exploring_file_utility.image_chains', $config['chains']);

            $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
            $loader->load('services.xml');
        }
    }