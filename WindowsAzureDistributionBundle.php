<?php

/**
 * WindowsAzure DistributionBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */
namespace WindowsAzure\DistributionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WindowsAzure\DistributionBundle\DependencyInjection\CompilerPass\ShardingPass;
use WindowsAzure\DistributionBundle\DependencyInjection\CompilerPass\CustomIteratorsPass;
use WindowsAzure\DistributionBundle\DependencyInjection\CompilerPass\PackageCompilersPass;
use WindowsAzure\DistributionBundle\Blob\Stream;

/**
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class WindowsAzureDistributionBundle extends Bundle
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::boot()
     */
    public function boot()
    {
        parent::boot();

        $streams = $this->container->getParameter('windows_azure_distribution.streams');

        foreach ($streams as $streamName => $clientName) {
            $client = $this->container->get('windows_azure.blob.' . $clientName);

            Stream::register($client, $streamName);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ShardingPass());
        $container->addCompilerPass(new CustomIteratorsPass());
        $container->addCompilerPass(new PackageCompilersPass());
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::getContainerExtension()
     */
    public function getContainerExtension()
    {
        $this->extension = new DependencyInjection\WindowsAzureDistributionExtension();

        return $this->extension;
    }

}

