<?php
namespace WindowsAzure\DistributionBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 */
class PackageCompilersPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $packageCompilers = array();
        
        foreach ($container->findTaggedServiceIds('windows_azure_distribution.package_compiler') as $serviceId => $attributes) {
            $packageCompilers[] = new Reference($serviceId);
        }
        
        $container->getDefinition('windows_azure_distribution.package_compiler')->addArgument($packageCompilers);
    }
}

