<?php
namespace WindowsAzure\DistributionBundle\Tests\Deployment;

use WindowsAzure\DistributionBundle\Deployment\Composer\DeploymentListener;

class ComposerDeploymentListenerTest extends \PHPUnit_Framework_TestCase
{

    public function testPostInstallSkipsIfEvnironmentNotSet()
    {
        $io = $this->getMock('Composer\IO\IOInterface', array(
            'write'
        ));
        $io->expects($this->any())
            ->method('write');
        
        $event = $this->getMock('Composer\Script\Event', array(
            'getIo'
        ));
        $event->expects($this->once())
            ->method('getIo')
            ->will($this->returnValue($io));
        
        $this->assertFalse(DeploymentListener::postInstall($event));
    }

    public function testTriggerListenerIfEnvironmentPresent()
    {
        $io = $this->getMock('Composer\IO\IOInterface', array(
            'write'
        ));
        $io->expects($this->any())
            ->method('write');
        
        $_SERVER['DEPLOYMENT_SOURCE'] = '/foo';
        $_SERVER['DEPLOYMENT_TARGET'] = '/bar';
        
        $task = $this->getMock('WindowsAzure\DistributionBundle\Deployment\WebsitesFileCopyTask', array(
            'copyFiles'
        ));
        $task->expects($this->once())
            ->method('copyFiles')
            ->with('/foo', '/bar');
        
        $listener = new DeploymentListener($task);
        $listener->triggerWebsitesFileCopyTask($io);
    }
}
