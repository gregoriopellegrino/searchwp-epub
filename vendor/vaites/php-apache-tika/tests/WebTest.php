<?php

namespace Vaites\ApacheTika\Tests;

use Vaites\ApacheTika\Client;

/**
 * Tests for web mode
 */
class WebTest extends BaseTest
{
    protected static $process = null;

    /**
     * Start Tika server and create shared instance of clients
     */
    public static function setUpBeforeClass()
    {
        self::$client = Client::make('localhost', 9998, [CURLOPT_TIMEOUT => 30]);
    }

    /**
     * cURL multiple options test
     */
    public function testCurlOptions()
    {
        $client = Client::make('localhost', 9998, [CURLOPT_TIMEOUT => 3]);
        $options = $client->getOptions();

        $this->assertEquals(3, $options[CURLOPT_TIMEOUT]);
    }

    /**
     * cURL single option test
     */
    public function testCurlSingleOption()
    {
        $client = Client::make('localhost', 9998)->setOption(CURLOPT_TIMEOUT, 3);

        $this->assertEquals(3, $client->getOption(CURLOPT_TIMEOUT));
    }

    /**
     * cURL timeout option test
     */
    public function testCurlTimeoutOption()
    {
        $client = Client::make('localhost', 9998)->setTimeout(3);

        $this->assertEquals(3, $client->getTimeout());
    }

    /**
     * cURL headers test
     */
    public function testCurlHeaders()
    {
        $header = 'Content-Type: image/jpeg';

        $client = Client::make('localhost', 9998, [CURLOPT_HTTPHEADER => [$header]]);
        $options = $client->getOptions();

        $this->assertContains($header, $options[CURLOPT_HTTPHEADER]);
    }

    /**
     * Set host test
     */
    public function testSetHost()
    {
        $client = Client::make('localhost', 9998);
        $client->setHost('127.0.0.1');

        $this->assertEquals('127.0.0.1', $client->getHost());
    }

    /**
     * Set port test
     */
    public function testSetPort()
    {
        $client = Client::make('localhost', 9998);
        $client->setPort(9997);

        $this->assertEquals(9997, $client->getPort());
    }

    /**
     * Set url host test
     */
    public function testSetUrlHost()
    {
        $client = Client::make('http://localhost:9998');

        $this->assertEquals('localhost', $client->getHost());
    }

    /**
     * Set url port test
     */
    public function testSetUrlPort()
    {
        $client = Client::make('http://localhost:9998');

        $this->assertEquals(9998, $client->getPort());
    }

    /**
     * Set retries test
     */
    public function testSetRetries()
    {
        $client = Client::make('localhost', 9998);
        $client->setRetries(5);

        $this->assertEquals(5, $client->getRetries());
    }

    /**
     * Recursive text metadata test
     *
     * @dataProvider    ocrProvider
     *
     * @param   string $file
     * @throws  \Exception
     */
    public function testTextRecursiveMetadata($file)
    {
        if(version_compare(self::$version, '1.11') < 0)
        {
            $this->markTestSkipped('Apache Tika ' . self::$version . ' lacks recursive metadata extraction');
        }
        else
        {
            $metadata = self::$client->getRecursiveMetadata($file, 'text');

            $this->assertContains('Sed do eiusmod tempor incididunt', $metadata->content);
        }
    }

    /**
     * Recursive HTML metadata test
     *
     * @dataProvider    ocrProvider
     *
     * @param   string $file
     * @throws  \Exception
     */
    public function testHtmlRecursiveMetadata($file)
    {
        if(version_compare(self::$version, '1.11') < 0)
        {
            $this->markTestSkipped('Apache Tika ' . self::$version . ' lacks recursive metadata extraction');
        }
        else
        {
            $metadata = self::$client->getMetadata($file, 'html');

            $this->assertContains('Sed do eiusmod tempor incididunt', $metadata->content);
        }
    }

    /**
     * Recursive ignore metadata test
     *
     * @dataProvider    ocrProvider
     *
     * @param   string $file
     * @throws  \Exception
     */
    public function testIgnoreRecursiveMetadata($file)
    {
        if(version_compare(self::$version, '1.11') < 0)
        {
            $this->markTestSkipped('Apache Tika ' . self::$version . ' lacks recursive metadata extraction');
        }
        else
        {
            $metadata = self::$client->getMetadata($file, 'ignore');

            $this->assertNull($metadata->content);
        }
    }

    /**
     * Test delayed check
     */
    public function testDelayedCheck()
    {
        $client = Client::prepare('localhost', 9997);
        $client->setPort(9998);

        $this->assertStringEndsWith(self::$version, $client->getVersion());
    }
}