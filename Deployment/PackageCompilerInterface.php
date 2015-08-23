<?php
namespace WindowsAzure\DistributionBundle\Deployment;

/**
 */
interface PackageCompilerInterface
{

    /**
     *
     * @param ServiceDefinition $serviceDefinition            
     * @param int $buildNumber            
     */
    public function compileDependencies(ServiceDefinition $serviceDefinition, $buildNumber);
}