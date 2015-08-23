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
namespace WindowsAzure\DistributionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Package a Symfony application for deployment.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class AssetsDeployCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('azure:deploy-assets')
            ->setDescription('Deploy assets on WindowsAzure Blob Storage for production.')
            ->addOption('increment-build', null, InputOption::VALUE_NONE, 'Increment the build number by one during this deployment.')
            ->addOption('build-number', null, InputOption::VALUE_REQUIRED, 'Set the build number to use during deployment.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernelRoot = $this->getContainer()->getParameter('kernel.root_dir');
        
        if ($input->hasOption('build-number')) {
            $buildNumber = $input->getOption('build-number');
        } else {
            $number = BuildNumber::createInDirectory($kernelRoot . "/config");
            
            if ($input->hasOption('increment-build')) {
                $output->writeln('Incrementing build number by one.');
                $buildNumber = $number->increment();
            } else {
                $buildNumber = $number->get();
            }
        }
        
        $output->writeln('Compiling assets for build ' . $buildNumber);
        $targetDir = $kernelRoot . "/../web";
        
        $webRoleStrategy = $this->getContainer()->get('windows_azure_distribution.assets');
        $webRoleStrategy->deploy($targetDir, $buildNumber);
    }
}

