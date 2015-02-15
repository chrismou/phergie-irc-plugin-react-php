<?php
/**
 * Phergie plugin for PHP function lookups (https://github.com/chrismou/phergie-irc-plugin-react-php)
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-php for the canonical source repository
 * @copyright Copyright (c) 2015 Chris Chrisostomou (https://mou.me)
 * @license http://phergie.org/license New BSD License
 * @package Chrismou\Phergie\Plugin\Php
 */

namespace Chrismou\Phergie\Tests\Plugin\Php;

use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as Event;
use Chrismou\Phergie\Plugin\Php\Plugin;
use \Mockery as m;

/**
 * Tests for the Plugin class.
 *
 * @category Chrismou
 * @package Chrismou\Phergie\Plugin\Php
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $eventMock;

    /**
     * @var \Mockery\MockInterface
     */
    private $queueMock;

    /**
     * @var \Chrismou\Phergie\Plugin\Php\Plugin
     */
    private $plugin;

    private $dbPath;

    /**
     * Sets up the test class
     */
    public function setUp()
    {
        $this->dbPath = __DIR__ . '/../data/phpdoc.db';

        $this->plugin = $this->getPlugin();
        $this->eventMock = $this->getMockEvent();
        $this->queueMock = $this->getMockQueue();
    }

    /**
     * Tests that getSubscribedEvents() returns an array.
     */
    public function testGetSubscribedEvents()
    {
        $plugin = $this->getPlugin();
        $this->assertInternalType('array', $plugin->getSubscribedEvents());
    }

    /**
     * Test the process of searching for a valid PHP function
     */
    public function testHandleCommandWithFunctionFound()
    {
        $testParam = 'array_key_exists';
        $source = '#channel';

        $this->eventMock->shouldReceive('getCustomParams')
            ->andReturn(array($testParam))
            ->twice();

        $this->eventMock->shouldReceive('getCustomCommand')
            ->andReturn("php");

        $function = $this->plugin->doFunctionLookup($testParam);
        $expectedLines = $this->plugin->getFoundFunctionLines($function);
        $this->assertInternalType('array', $expectedLines);

        $this->eventMock->shouldReceive('getSource')
            ->andReturn($source)
            ->times(count($expectedLines));

        $this->plugin->handleCommand($this->eventMock, $this->queueMock);

        foreach ($expectedLines as $line) {
            $this->queueMock->shouldReceive('ircPrivmsg')
                ->withArgs(array($source, $line));
        }
    }

    /**
     * Test the process of searching for a non-existent PHP function
     */
    public function testHandleCommandWithUnknownFunction()
    {
        $testParam = 'woozlewozzle';
        $source = '#channel';

        $this->eventMock->shouldReceive('getCustomParams')
            ->andReturn(array($testParam))
            ->twice();

        $this->eventMock->shouldReceive('getCustomCommand')
            ->andReturn("php");

        $expectedLines = $this->plugin->getUnknownFunctionLines();
        $this->assertInternalType('array', $expectedLines);

        $this->eventMock->shouldReceive('getSource')
            ->andReturn($source)
            ->times(count($expectedLines));

        $this->plugin->handleCommand($this->eventMock, $this->queueMock);

        foreach ($expectedLines as $line) {
            $this->queueMock->shouldReceive('ircPrivmsg')
                ->withArgs(array($source, $line));
        }
    }

    /**
     * Test the process of attempting to instantiate the plugin with a reference to an invalid DB
     */
    public function testHandleCommandWithInvalidDb()
    {
        $plugin = $this->getPlugin('on/the/road/to/nowhere');
        $source = '#channel';

        $expectedLines = $plugin->getErrorLines();
        $this->assertInternalType('array', $expectedLines);

        $this->eventMock->shouldReceive('getSource')
            ->andReturn($source)
            ->times(count($expectedLines));

        $plugin->handleCommand($this->eventMock, $this->queueMock);

        foreach ($expectedLines as $line) {
            $this->queueMock->shouldReceive('ircPrivmsg')
                ->withArgs(array($source, $line));
        }
    }

    /**
     * Test the process of providing invalid parameters
     */
    public function testHandleCommandWithInvalidParams()
    {
        $source = '#channel';

        $this->eventMock->shouldReceive('getCustomParams')
            ->andReturn(array())
            ->once();

        $expectedLines = $this->plugin->getHelpLines();
        $this->assertInternalType('array', $expectedLines);

        $this->eventMock->shouldReceive('getSource')
            ->andReturn($source)
            ->times(count($expectedLines));

        $this->plugin->handleCommand($this->eventMock, $this->queueMock);

        foreach ($expectedLines as $expectedLine) {
            $this->queueMock->shouldReceive('ircPrivmsg')
                ->withArgs(array($source, $expectedLine));
        }
    }

    /**
     * Test the help command
     */
    public function testHandleCommandHelp()
    {
        $source = '#channel';

        $expectedLines = $this->plugin->getHelpLines();
        $this->assertInternalType('array', $expectedLines);

        $this->eventMock->shouldReceive('getSource')
            ->andReturn($source)
            ->times(count($expectedLines));

        $this->plugin->handleCommandHelp($this->eventMock, $this->queueMock);

        foreach ($expectedLines as $expectedLine) {
            $this->queueMock->shouldReceive('ircPrivmsg')
                ->withArgs(array($source, $expectedLine));
        }
    }

    /**
     * Return an instance of the PHP plugin
     *
     * @param bool|string $customDbPath
     * @return \Chrismou\Phergie\Plugin\Php\Plugin
     */
    protected function getPlugin($customDbPath=false)
    {
        $plugin = new Plugin(array('dbpath' => ($customDbPath) ? $customDbPath : $this->dbPath));
        $plugin->setEventEmitter(m::mock('\Evenement\EventEmitterInterface'));
        $plugin->setLogger(m::mock('\Psr\Log\LoggerInterface'));

        return $plugin;
    }

    /**
     * Returns a mock command event.
     *
     * @return \Phergie\Irc\Plugin\React\Command\CommandEventInterface
     */
    protected function getMockEvent()
    {
        return m::mock('\Phergie\Irc\Plugin\React\Command\CommandEventInterface');
    }

    /**
     * Returns a mock event queue.
     *
     * @return \Phergie\Irc\Bot\React\EventQueueInterface
     */
    protected function getMockQueue()
    {
        return m::mock('\Phergie\Irc\Bot\React\EventQueue', array('ircPrivmsg' => null));
    }
}
