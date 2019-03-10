<?php

    namespace Exploring\FileUtilityBundle\DependencyInjection;

    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    /**
     * This is the class that validates and merges configuration from your app/config files
     *
     * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
     */
    class Configuration implements ConfigurationInterface
    {
        /**
         * {@inheritDoc}
         */
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('exploring_file_utility');

            /** @noinspection PhpUndefinedMethodInspection */
            $rootNode
                ->children()
                    ->scalarNode('upload_root')->isRequired()->end()
                    ->scalarNode('filename_generator')->defaultValue(null)->end()
                    ->scalarNode('image_engine')->defaultValue('gd')->end()
                    ->arrayNode('gd')->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('quality')->addDefaultsIfNotSet()
                                ->children()
                ->integerNode('jpeg')->defaultValue(Constants::DEFAULT_JPEG_QUALITY)->end()
                ->integerNode('png')->defaultValue(Constants::DEFAULT_PNG_QUALITY)->end()
                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('imagick')->addDefaultsIfNotSet()
                        ->children()
                ->integerNode('compression')->defaultValue(Constants::DEFAULT_IMAGICK_COMPRESSION)->end()
                ->integerNode('quality')->defaultValue(Constants::DEFAULT_IMAGICK_COMPRESSION_QUALITY)->end()
                ->end()
                    ->end()
                    ->arrayNode('chains')->defaultValue(array())->useAttributeAsKey('name')
                        ->prototype('array')->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('directory')->defaultNull()->end()
                                ->arrayNode('steps')->useAttributeAsKey('name')->requiresAtLeastOneElement()
                                    ->prototype('array')
                                        ->prototype('scalar')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ;

            return $treeBuilder;
        }
    }
