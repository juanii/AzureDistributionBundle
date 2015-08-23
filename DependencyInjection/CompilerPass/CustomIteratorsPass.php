<?php
namespace WindowsAzure\DistributionBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 *
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class CustomIteratorsPass implements CompilerPassInterface
{

    /**
     *
     * @param ContainerBuilder $container            
     */
    public function process(ContainerBuilder $container)
    {
        $container->setParameter('windows_azure_distribution.config.deployment', $this->mergeOptions($container, $container->getParameter('windows_azure_distribution.config.deployment')));
    }

    /**
     *
     * @param ContainerBuilder $container            
     * @param array $options            
     * @return array
     */
    protected function mergeOptions(ContainerBuilder $container, array $options)
    {
        if (! array_key_exists('customIterators', $options)) {
            $options['customIterators'] = array();
        }
        
        foreach ($container->findTaggedServiceIds('windows_azure_distribution.custom_iterator') as $serviceId => $attributes) {
            $options['customIterators'][] = new Reference($serviceId);
        }
        
        return $options;
    }
}

