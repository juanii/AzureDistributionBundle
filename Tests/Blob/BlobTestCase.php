<?php
namespace WindowsAzure\DistributionBundle\Tests\Blob;

use MicrosoftAzure\Storage\Blob\Models\ListContainersOptions;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\DistributionBundle\Blob\Stream;

class BlobTestCase extends \PHPUnit_Framework_TestCase
{

    const CONTAINER_PREFIX = 'aztest';

    protected static $path;

    protected static $uniqId;

    protected static $uniqStart;

    protected function setUp()
    {
        self::$path = dirname(__FILE__) . '/_files/';
        date_default_timezone_set('UTC');
        
        if (in_array('azure', stream_get_wrappers())) {
            stream_wrapper_unregister('azure');
        }
    }

    protected function tearDown()
    {
        $blobClient = $this->createBlobClient();
        for ($i = self::$uniqStart; $i <= self::$uniqId; $i ++) {
            try {
                $blobClient->deleteContainer(self::CONTAINER_PREFIX . $i);
            } catch (\Exception $e) {}
        }
    }

    protected function createBlobClient()
    {
        if (! isset($GLOBALS['AZURE_BLOB_CONNECTION'])) {
            $this->markTestSkipped("Configure <php><var name=\"AZURE_BLOB_CONNECTION\" value=\"\"></php> to run the blob  tests.");
        }
        
        $proxy = ServicesBuilder::getInstance()->createBlobService($GLOBALS['AZURE_BLOB_CONNECTION']);
        
        if (in_array('azure', stream_get_wrappers())) {
            stream_wrapper_unregister('azure');
        }
        Stream::register($proxy, 'azure');
        
        return $proxy;
    }

    protected function generateName()
    {
        if (self::$uniqId === null) {
            self::$uniqId = self::$uniqStart = time();
        }
        self::$uniqId ++;
        return self::CONTAINER_PREFIX . self::$uniqId;
    }
}

