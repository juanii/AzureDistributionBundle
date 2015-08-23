<?php
namespace WindowsAzure\DistributionBundle\Deployment;

/**
 */
class PackageCompiler
{

    /**
     *
     * @var array
     */
    private $compilers = array();

    public function __construct(array $compilers)
    {
        $this->compilers = $compilers;
    }

    /**
     *
     * @return array
     */
    public function getCompilers()
    {
        return $this->compilers;
    }
}
