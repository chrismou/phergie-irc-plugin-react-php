<?php
/**
 * Phergie plugin for PHP function lookups (https://github.com/chrismou/phergie-irc-plugin-react-php)
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-php for the canonical source repository
 * @copyright Copyright (c) 2014 Chris Chrisostomou (http://mou.me)
 * @license http://phergie.org/license New BSD License
 * @package Chrismou\Phergie\Plugin\Php
 */

namespace Chrismou\Phergie\Tests\Plugin\Php;

require_once __DIR__ . '/../vendor/danrspencer/phpunit-expect-syntax/ExpectSyntax.php';

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

        $this->eventMock->shouldReceive('getSource')
            ->andReturn($source)
            ->times(count($expectedLines));

        $this->plugin->handleCommand($this->eventMock, $this->queueMock);

        foreach ($expectedLines as $line) {
            $this->queueMock->shouldReceive('ircPrivmsg')
                ->withArgs(array($source, $line));
        }
    }

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

        $this->eventMock->shouldReceive('getSource')
            ->andReturn($source)
            ->times(count($expectedLines));

        $this->plugin->handleCommand($this->eventMock, $this->queueMock);

        foreach ($expectedLines as $line) {
            $this->queueMock->shouldReceive('ircPrivmsg')
                ->withArgs(array($source, $line));
        }
    }

    public function testHandleCommandWithInvalidDb()
    {
        $plugin = $this->getPlugin('on/the/road/to/nowhere');
        $source = '#channel';

        $expectedLines = $plugin->getErrorLines();

        $this->eventMock->shouldReceive('getSource')
            ->andReturn($source)
            ->times(count($expectedLines));

        $plugin->handleCommand($this->eventMock, $this->queueMock);

        foreach ($expectedLines as $line) {
            $this->queueMock->shouldReceive('ircPrivmsg')
                ->withArgs(array($source, $line));
        }
    }

    public function testHandleCommandWithInvalidParams()
    {

    }

    public function testHandleCommandHelp()
    {
        $expectedLines = $this->plugin->getHelpLines();
        $source = '#channel';

        $this->eventMock->shouldReceive('getSource')
            ->andReturn($source)
            ->times(count($expectedLines));

        $this->plugin->handleCommandHelp($this->eventMock, $this->queueMock);

        foreach ($expectedLines as $expectedLine) {
            $this->queueMock->shouldReceive('ircPrivmsg')
                ->withArgs(array($source, $expectedLine));
        }
    }

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
