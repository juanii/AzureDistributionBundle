<?php
namespace WindowsAzure\DistributionBundle\Deployment;

/**
 *
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
interface CustomIteratorInterface
{

    /**
     *
     * @param array $dirs            
     * @param array $subdirs            
     */
    public function getIterator(array $dirs, array $subdirs);
}