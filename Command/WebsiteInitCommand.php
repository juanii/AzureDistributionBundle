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
use Symfony\Component\Console\Input\InputArgument;

class WebsiteInitCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('azure:websites:init')
            ->setDescription('Initialize project for deployment on Windows Azure Websites via Git')
            ->addOption('execute', null, InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL, 'Execute the request whereas dumping them')
            ->addArgument('name', InputArgument::OPTIONAL, 'Web Site name the configuration should be applied')
            ->addArgument('slot', InputArgument::OPTIONAL, 'Slot the configuration should be applied')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernelRoot = $this->getContainer()->getParameter('kernel.root_dir');
        $fileSystem = $this->getContainer()->get('filesystem');
        
        $output->writeln('Copy Files for Windows Azure Websites deployment to project root:');
        $fileSystem->mirror(__DIR__ . "/../Resources/private/websites/", $kernelRoot . "/../");
        
        if ($input->getOption('execute')) {
            $this->executeWebsiteConfiguration($input, $output);
            
            return;
        }
        
        $this->dumpWebsiteConfiguration($output);
    }
    
    protected function executeWebsiteConfiguration(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $slot = $input->getArgument('slot');
        $output->writeln('Configuring environment variables.');
        
        if (!$this->hasConfiguration('PHP_INI_SCAN_DIR', $name, $slot)) {
            $this->addWebsiteConfiguration('PHP_INI_SCAN_DIR', 'd:/home/site/wwwroot/app/websites/php', $name, $slot);
        }
        
        if (!$this->hasConfiguration('PHP_BASE_CUSTOM_EXTENSIONS_DIR', $name, $slot)) {
            $this->addWebsiteConfiguration('PHP_BASE_CUSTOM_EXTENSIONS_DIR', 'd:/home/site/wwwroot/app/websites/php/ext', $name, $slot);
        }
    }
    
    private function hasConfiguration($configKey, $name = null, $slot = null)
    {
        static $configurations = array();
        $key = 'k'.$name.'-'.$slot;
        
        if (!array_key_exists($key, $configurations)) {
            $configurations[$key] = $this->getWebsiteConfigurations($name, $slot);
        }
        
        foreach ($configurations[$key] as $configuration) {
            if ($configuration->name == $configKey) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param string $name
     * @param string $slot
     * @return Ambigous array
     */
    private function getWebsiteConfigurations($name = null, $slot = null)
    {
        $currentConfigurations = shell_exec(sprintf('azure site appsetting list %s --json %s', $slot?('--slot '.$slot):'', $name?:''));
        
        if ($currentConfigurations) {
            $currentConfigurations = json_decode($currentConfigurations);
        }
        
        if (!$currentConfigurations) {
            $currentConfigurations = array();
        }
        
        return $currentConfigurations;
    }
    
    private function addWebsiteConfiguration($key, $value, $name = null, $slot = null)
    {
        $configAddResult = shell_exec(sprintf('azure site appsetting add --json %s %s=%s %s ', $slot?('--slot '.$slot):'', escapeshellarg($key), escapeshellarg($value), $name?:''));
        
        if ($configAddResult) {
            $configAddResult = json_decode($configAddResult);
        }
        
        // TODO: handle error (key already exists)
        
        return true;
    }
    
    protected function dumpWebsiteConfiguration(OutputInterface $output)
    {
        $output->writeln('Configuration almost done! You should now define the following configurations into your Web Sites.');
        $tableHelper = $this->getHelper('table');
        
        $tableHelper
            ->setHeaders(array('KEY', 'VALUE'))
            ->setRows(array(
                array('PHP_INI_SCAN_DIR', 'd:/home/site/wwwroot/app/websites/php'),
                array('PHP_BASE_CUSTOM_EXTENSIONS_DIR', 'd:/home/site/wwwroot/app/websites/php/ext')
            ))
        ;
            
        $tableHelper->render($output);
    }
}
