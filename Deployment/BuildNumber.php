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
namespace WindowsAzure\DistributionBundle\Deployment;

use Symfony\Component\Yaml\Yaml;

/**
 * Represents an Azure Build Number used for asset deployment.
 */
class BuildNumber
{

    /**
     *
     * @var string
     */
    private $file;

    /**
     * Create a new Build Number inside a directory.
     *
     * The filename will be "azure_build_number.yml".
     */
    static public function createInDirectory($dir)
    {
        if (! is_dir($dir) || ! is_writable($dir)) {
            throw new \InvalidArgumentException("Directory to load build number from is not writable or does not exist: " . $dir);
        }
        
        $buildFile = $dir . DIRECTORY_SEPARATOR . "azure_build_number.yml";
        
        if (! file_exists($buildFile)) {
            file_put_contents($buildFile, "parameters:\n  azure_build: 0");
        }
        
        return new self($buildFile);
    }

    private function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Return current build number.
     *
     * @return int
     */
    public function get()
    {
        $config = Yaml::parse($this->file);
        
        return (int) $config['parameters']['azure_build'];
    }

    private function write($buildNumber)
    {
        $yaml = Yaml::dump(array(
            'parameters' => array(
                'azure_build' => $buildNumber
            )
        ), 2);
        file_put_contents($this->file, $yaml);
    }

    /**
     * Increment and return new build number.
     *
     * @return int
     */
    public function increment()
    {
        $buildNumber = $this->get();
        $buildNumber ++;
        
        $this->write($buildNumber);
        
        return $buildNumber;
    }
}

