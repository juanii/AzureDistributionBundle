<?php
namespace WindowsAzure\DistributionBundle\Deployment\Composer;

use WindowsAzure\DistributionBundle\Deployment\WebsitesFileCopyTask;
use Composer\Script\Event;
use Composer\IO\IOInterface;

/**
 * Windows Azure Deployment Listener for Composer.
 *
 * Detects if this is a Windows Azure Websites deployment, and then
 * copies all files from the source directory (Git repository) into
 * the destination directory (document root).
 */
class DeploymentListener
{

    public function __construct(WebsitesFileCopyTask $task = null)
    {
        $this->task = $task ?  : new WebsitesFileCopyTask();
    }

    public static function postInstall(Event $event)
    {
        $listener = new self();
        
        return $listener->triggerWebsitesFileCopyTask($event->getIo());
    }

    public function triggerWebsitesFileCopyTask(IOInterface $io)
    {
        if (! isset($_SERVER['DEPLOYMENT_SOURCE']) || ! isset($_SERVER['DEPLOYMENT_TARGET'])) {
            $io->write("[Azure] Detect environemnt: NOT FOUND.");
            $io->write("[Azure] Skipping Azure Website file copy task");
            return false;
        }
        
        $io->write("[Azure] Detect environemnt: FOUND.");
        $io->write("[Azure] Copying files to webroot directory.", true);
        
        $this->task->copyFiles($_SERVER['DEPLOYMENT_SOURCE'], $_SERVER['DEPLOYMENT_TARGET']);
        
        return true;
    }
}

