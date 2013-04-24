<?php
/**
 * This file is part of BcMq.
 *
 * (c) 2013 Florian Eckerstorfer
 */

namespace Bc\Mq;

use \Mockery as m;

/**
 * ServerTest
 *
 * @group unit
 */
class ServerTest extends \PHPUnit_Framework_TestCase
{
    private $processFactory;
    private $loop;
    private $socket;

    public function setUp()
    {
        $this->processFactory   = m::mock('Bc\BackgroundProcess\Factory');
        $this->loop             = m::mock('React\EventLoop\LoopInterface');
        $this->socket           = m::mock('React\Socket\ServerInterface');

        $this->server = new Server($this->processFactory, $this->loop, $this->socket);
    }
    /**
     * Tests the <code>run()</code> method.
     *
     * @covers Bc\Mq\Server::__construct()
     * @covers Bc\Mq\Server::run()
     */
    public function testRun()
    {
        $this->socket
            ->shouldReceive('on')
            ->with('connection', m::any())
            ->once();

        $this->socket
            ->shouldReceive('listen')
            ->with(4000)
            ->once();

        $this->loop
            ->shouldReceive('run')
            ->withNoArgs()
            ->once();

        $this->server->run('php consumer.php', 4000);
    }

    /**
     * Tests the <code>handleData()</code> method.
     *
     * @covers Bc\Mq\Server::handleData()
     */
    public function testHandleData()
    {
        $process = m::mock('Bc\BackgroundProcess\BackgroundProcess');
        $process
            ->shouldReceive('run')
            ->withNoArgs()
            ->once();

        $this->processFactory
            ->shouldReceive('newProcess')
            ->with('php consumer.php "{\"message\":\"foobar\"}"')
            ->once()
            ->andReturn($process);

        $connection = m::mock('React\Socket\ConnectionInterface');
        $connection
            ->shouldReceive('close')
            ->withNoArgs()
            ->once();

        $this->server->handleData('{"message":"foobar"}', 'php consumer.php', $connection);
    }
}
