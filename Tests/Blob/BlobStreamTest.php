<?php
namespace WindowsAzure\DistributionBundle\Tests\Blob;

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Blob\Models\ListContainersOptions;
use WindowsAzure\DistributionBundle\Blob\Stream;

class BlobTest extends BlobTestCase
{

    public function testGetUnknownClient()
    {
        $this->setExpectedException('WindowsAzure\DistributionBundle\Blob\BlobException');
        Stream::getClient('unknown');
    }

    /**
     * Test read file
     */
    public function testReadFile()
    {
        $containerName = $this->generateName();
        $fileName = 'azure://' . $containerName . '/test.txt';
        
        $blobClient = $this->createBlobClient();
        
        $fh = fopen($fileName, 'w');
        fwrite($fh, "Hello world!");
        $pos = ftell($fh);
        fseek($fh, 0);
        fwrite($fh, "Hello world!");
        fclose($fh);
        
        $result = file_get_contents($fileName);
        
        $this->assertEquals('Hello world!', $result);
        $this->assertEquals(12, $pos);
    }

    public function testReadUnknownFile()
    {
        $containerName = $this->generateName();
        $fileName = 'azure://' . $containerName . '/test.txt';
        
        $blobClient = $this->createBlobClient();
        
        $this->setExpectedException('WindowsAzure\Common\ServiceException');
        $result = file_get_contents($fileName);
    }

    public function testWriteInvalidFile()
    {
        $containerName = $this->generateName();
        $fileName = 'azure://' . $containerName . '/';
        
        $blobClient = $this->createBlobClient();
        
        $this->setExpectedException('WindowsAzure\DistributionBundle\Blob\BlobException', 'Empty blob path name given. Has to be a full filename.');
        $fh = fopen($fileName, 'w');
    }

    /**
     * Test write file
     */
    public function testWriteFile()
    {
        $containerName = $this->generateName();
        $fileName = 'azure://' . $containerName . '/test.txt';
        
        $blobClient = $this->createBlobClient();
        
        $fh = fopen($fileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);
        
        $instance = $blobClient->getBlobProperties($containerName, 'test.txt');
        $this->assertEquals(12, $instance->getProperties()
            ->getContentLength());
    }

    /**
     * Test unlink file
     */
    public function testUnlinkFile()
    {
        $containerName = $this->generateName();
        $fileName = 'azure://' . $containerName . '/test.txt';
        
        $blobClient = $this->createBlobClient();
        
        $fh = fopen($fileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);
        
        unlink($fileName);
        
        $result = $blobClient->listBlobs($containerName);
        $this->assertEquals(0, count($result->getBlobs()));
    }

    /**
     * Test copy file
     */
    public function testCopyFile()
    {
        $containerName = $this->generateName();
        $sourceFileName = 'azure://' . $containerName . '/test.txt';
        $destinationFileName = 'azure://' . $containerName . '/test2.txt';
        
        $blobClient = $this->createBlobClient();
        
        $fh = fopen($sourceFileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);
        
        copy($sourceFileName, $destinationFileName);
        
        $instance = $blobClient->getBlobProperties($containerName, 'test2.txt');
        $this->assertEquals(12, $instance->getProperties()
            ->getContentLength());
    }

    public function testRenameChangeContainerInvalid()
    {
        $containerName = $this->generateName();
        $containerName2 = $this->generateName();
        
        $sourceFileName = 'azure://' . $containerName . '/test.txt';
        $destinationFileName = 'azure://' . $containerName2 . '/test2.txt';
        
        $blobClient = $this->createBlobClient();
        
        $this->setExpectedException('WindowsAzure\DistributionBundle\Blob\BlobException', 'Container name can not be changed.');
        rename($sourceFileName, $destinationFileName);
    }

    public function testRenameSameName()
    {
        $containerName = $this->generateName();
        $sourceFileName = 'azure://' . $containerName . '/test.txt';
        $destinationFileName = 'azure://' . $containerName . '/test.txt';
        
        $blobClient = $this->createBlobClient();
        
        $fh = fopen($sourceFileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);
        
        rename($sourceFileName, $destinationFileName);
        
        $this->assertEquals('Hello world!', file_get_contents($destinationFileName));
    }

    /**
     * Test rename file
     */
    public function testRenameFile()
    {
        $containerName = $this->generateName();
        $sourceFileName = 'azure://' . $containerName . '/test.txt';
        $destinationFileName = 'azure://' . $containerName . '/test2.txt';
        
        $blobClient = $this->createBlobClient();
        
        $fh = fopen($sourceFileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);
        
        rename($sourceFileName, $destinationFileName);
        
        $this->assertEquals('Hello world!', file_get_contents($destinationFileName));
        $this->assertFalse(file_exists($sourceFileName));
    }

    /**
     * Test mkdir
     */
    public function testMkdir()
    {
        $containerName = $this->generateName();
        
        $blobClient = $this->createBlobClient();
        
        $current = count($blobClient->listContainers()->getContainers());
        
        mkdir('azure://' . $containerName);
        
        $after = count($blobClient->listContainers()->getContainers());
        
        $this->assertEquals($current + 1, $after, "One new container should exist");
        
        $options = new ListContainersOptions();
        $options->setPrefix($containerName);
        $this->assertEquals(1, count($blobClient->listContainers($options)
            ->getContainers()));
    }

    public function testMkdirMulptileLevelsNotAllowed()
    {
        $containerName = $this->generateName();
        
        $blobClient = $this->createBlobClient();
        
        $current = count($blobClient->listContainers()->getContainers());
        
        $this->setExpectedException('WindowsAzure\DistributionBundle\Blob\BlobException', 'mkdir() with multiple levels is not supported on Windows Azure Blob Storage.');
        mkdir('azure://' . $containerName . '/foo');
    }

    /**
     * Test rmdir
     */
    public function testRmdir()
    {
        $containerName = $this->generateName();
        
        $blobClient = $this->createBlobClient();
        
        mkdir('azure://' . $containerName);
        rmdir('azure://' . $containerName);
        
        $options = new ListContainersOptions();
        $options->setPrefix($containerName);
        $result = $blobClient->listContainers($options);
        
        $this->assertEquals(0, count($result->getContainers()));
    }

    public function testRmdirWithMultipleLevelsNotAllowed()
    {
        $containerName = $this->generateName();
        
        $blobClient = $this->createBlobClient();
        
        $this->setExpectedException('WindowsAzure\DistributionBundle\Blob\BlobException', 'rmdir() with multiple levels is not supported on Windows Azure Blob Storage.');
        rmdir('azure://' . $containerName . '/foo');
    }

    /**
     * Test opendir
     */
    public function testOpendir()
    {
        $containerName = $this->generateName();
        $blobClient = $this->createBlobClient();
        $blobClient->createContainer($containerName);
        
        $blobClient->createBlockBlob($containerName, 'images/WindowsAzure1.gif', file_get_contents(self::$path . 'WindowsAzure.gif'));
        $blobClient->createBlockBlob($containerName, 'images/WindowsAzure2.gif', file_get_contents(self::$path . 'WindowsAzure.gif'));
        $blobClient->createBlockBlob($containerName, 'images/WindowsAzure3.gif', file_get_contents(self::$path . 'WindowsAzure.gif'));
        $blobClient->createBlockBlob($containerName, 'images/WindowsAzure4.gif', file_get_contents(self::$path . 'WindowsAzure.gif'));
        $blobClient->createBlockBlob($containerName, 'images/WindowsAzure5.gif', file_get_contents(self::$path . 'WindowsAzure.gif'));
        
        $result1 = $blobClient->listBlobs($containerName)->getBlobs();
        
        $result2 = array();
        if ($handle = opendir('azure://' . $containerName)) {
            while (false !== ($file = readdir($handle))) {
                $result2[] = $file;
            }
            closedir($handle);
        }
        
        $this->assertEquals(count($result1), count($result2));
    }

    static public function dataNestedDirectory()
    {
        return array(
            array(
                '/nested/test.txt'
            ),
            array(
                '/nested1/nested2/test.txt'
            )
        );
    }

    /**
     * @dataProvider dataNestedDirectory
     */
    public function testNestedDirectory($file)
    {
        $containerName = $this->generateName();
        $fileName = 'azure://' . $containerName . $file;
        
        $blobClient = $this->createBlobClient();
        
        $fh = fopen($fileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);
        
        $result = file_get_contents($fileName);
        
        $this->assertEquals('Hello world!', $result);
    }
}

