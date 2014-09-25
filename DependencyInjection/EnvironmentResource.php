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
namespace WindowsAzure\DistributionBundle\DependencyInjection;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * Resource for SYMFONY__ environment variables.
 *
 * Indicates a non-fresh cache, when the SYMFONY__ environment
 * variables changed.
 */
class EnvironmentResource implements ResourceInterface, \Serializable
{

    protected $envParameters;

    public function __construct(array $envParameters = array())
    {
        $this->envParameters = $envParameters;
    }

    public function __toString()
    {
        return implode(",", array_keys($this->envParameters));
    }

    protected function getEnvParameters()
    {
        $parameters = array();
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'SYMFONY__')) {
                $parameters[strtolower(str_replace('__', '.', substr($key, 9)))] = $value;
            }
        }
        
        return $parameters;
    }

    public function isFresh($timestamp)
    {
        return $this->getEnvParameters() === $this->envParameters;
    }

    public function getResource()
    {
        return $this->envParameters;
    }

    public function serialize()
    {
        return serialize($this->envParameters);
    }

    public function unserialize($serialized)
    {
        $this->envParameters = unserialize($serialized);
    }
}
