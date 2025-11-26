<?php

namespace SafeComms\Tests;

use PHPUnit\Framework\TestCase;
use SafeComms\SafeCommsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

class SafeCommsClientTest extends TestCase
{
    public function testModerateText()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'isClean' => true,
                'severity' => 'none'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // We need to inject the mocked client. 
        // Since SafeCommsClient creates its own Guzzle client in constructor, 
        // we might need to refactor SafeCommsClient to accept a client option or use reflection.
        // For now, let's use reflection to replace the client property.
        
        $safeComms = new SafeCommsClient('test-key');
        
        $reflection = new \ReflectionClass($safeComms);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($safeComms, $client);

        $result = $safeComms->moderateText('test content');

        $this->assertTrue($result['isClean']);
        $this->assertEquals('none', $result['severity']);
    }

    public function testGetUsage()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'tokensUsed' => 150,
                'tier' => 'Free'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $safeComms = new SafeCommsClient('test-key');
        
        $reflection = new \ReflectionClass($safeComms);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($safeComms, $client);

        $result = $safeComms->getUsage();

        $this->assertEquals(150, $result['tokensUsed']);
        $this->assertEquals('Free', $result['tier']);
    }
}
