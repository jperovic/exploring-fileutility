<?php
    namespace Exploring\FileUtilityBundle;

    use Exploring\FileUtilityBundle\DependencyInjection\FileUtilityCompilerPass;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class ExploringFileUtilityBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);

            $container->addCompilerPass(new FileUtilityCompilerPass());
        }
    }
