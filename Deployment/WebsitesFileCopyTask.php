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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Copy files from a source to a target directory on Azure Websites.
 *
 * TODO: Copy algorithm from Kudo to compute diffs.
 */
class WebsitesFileCopyTask
{

    public function copyFiles($sourceDirectory, $targetDirectory)
    {
        $this->copyDirectory($sourceDirectory, $targetDirectory);
    }

    private function copyDirectory($source, $target)
    {
        $it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);
        
        if (! file_exists($target)) {
            mkdir($target, 0777, true);
        }
        
        foreach ($ri as $file) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $ri->getSubPathName();
            if ($file->isDir()) {
                if (! file_exists($targetPath)) {
                    mkdir($targetPath);
                }
            } else 
                if (! file_exists($targetPath) || filemtime($targetPath) < filemtime($file->getPathname())) {
                    copy($file->getPathname(), $targetPath);
                }
        }
    }
}
