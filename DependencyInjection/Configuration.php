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

            $rootNode
                ->children()
                ->arrayNode('directories')->useAttributeAsKey('name')->requiresAtLeastOneElement()->prototype('scalar')
                ->end()->end()
                ->scalarNode('upload_root')->defaultValue('/tmp')->end()
                ->scalarNode('filename_generator')->defaultValue(null)->end()
                ->scalarNode('image_engine')->defaultValue('gd')->end();

            return $treeBuilder;
        }
    }
