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
 * Renew remote desktop certificates
 * @package WindowsAzure\DistributionBundle\Command
 * @author Stéphane Escandell <stephane.escandell@gmail.com>
 */
class RenewCertificateCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('azure:cloud-services:renew-certificate')
            ->setDescription('Renew certificate to remote desktop access.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deployment = $this->getContainer()->get('windows_azure_distribution.deployment');
        if (!$deployment->exists()) {
            throw new \RuntimeException("Azure Cloud Services is not initialized for this Symfony project. Please run the azure:cloud-services:init first.");
        }

        $roleName = 'Sf2.Web';
        
        if (extension_loaded('openssl')) {
            $length = 16;
            $keyPassword = base64_encode(openssl_random_pseudo_bytes(8, $strong));
            $keyPassword = substr($keyPassword, 0, $length);
            $desktopPassword = base64_encode(openssl_random_pseudo_bytes(8, $strong));
            $desktopPassword = substr($desktopPassword, 0, $length);
            
            $deployment->generateRemoteDesktopKey($roleName, $desktopPassword, $keyPassword);
            
            $output->writeln('');
            $output->writeln('Automatically created certificates to open a remote desktop to this role.');
            $output->writeln('Private Key Password: <info>' . $keyPassword . '</info>');
            $output->writeln('RemoteDesktop Password: <info>' . $desktopPassword . '</info>');
            $output->writeln('<comment>Write these passwords down, you need them during deployment.</comment>');
            $output->writeln('You can disable RemoteDesktop in ServiceConfiguration.cscfg');
        }
    }
}
