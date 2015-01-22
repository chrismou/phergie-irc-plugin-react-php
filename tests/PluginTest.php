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
use Phergie\Irc\Plugin\React\Command\CommandEvent as Event;
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
     * @var \Phergie\Irc\Plugin\React\Command\CommandEvent
     */
    private $eventMock;

    /**
     * @var \Phergie\Irc\Bot\React\EventQueueInterface
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
        $this->dbPath = __DIR__.'/../data/phpdoc.db';

        $this->plugin = new Plugin(array('dbpath' => $this->dbPath));
        $this->eventMock = $this->getMockEvent();
        $this->queueMock = $this->getMockQueue();
    }

    /**
     * Call closing methods
     */
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    /**
     * Tests that getSubscribedEvents() returns an array.
     */
    public function testGetSubscribedEvents()
    {
        $plugin = new Plugin;
        $this->assertInternalType('array', $plugin->getSubscribedEvents());
    }

    public function testHandleCommandSuccessfully()
    {
        $testParam = 'array_key_exists';

        $this->prepareMocksForParamValidation(array($testParam));

        $this->eventMock->shouldReceive('getSource')
            ->andReturn('#channel')
            ->twice();

        $this->eventMock->shouldReceive('getCustomCommand')
            ->andReturn("php");

        $this->eventMock->shouldReceive('getCustomParams')
            ->andReturn(array($testParam))
            ->once();

        $this->queueMock->shouldReceive('ircPrivmsg')
            ->twice();
        //->withArgs(array('#channel', 'wut'));

        $this->plugin->handleCommand($this->eventMock, $this->queueMock);
    }

    public function testHandleCommandWithUnknownFunction()
    {
        $testParam = 'woozlewozzle';

        $this->prepareMocksForParamValidation(array($testParam));

        $this->eventMock->shouldReceive('getSource')
            ->andReturn('#channel')
            ->once();

        $this->eventMock->shouldReceive('getCustomCommand')
            ->andReturn("php");

        $this->eventMock->shouldReceive('getCustomParams')
            ->andReturn(array($testParam))
            ->once();

        $this->queueMock->shouldReceive('ircPrivmsg')
            ->once();
        //->withArgs(array('#channel', 'wut'));

        $this->plugin->handleCommand($this->eventMock, $this->queueMock);
    }

    public function testHandleCommandWithInvalidDb()
    {
        $plugin = new Plugin(array('dbpath' => 'on/the/road/to/nowhere'));

        $this->prepareMocksForGenericResponse($this->plugin->getErrorLines());
        $plugin->handleCommand($this->eventMock, $this->queueMock);
    }

    public function testHandleCommandWithInvalidParams()
    {
        $this->prepareMocksForParamValidation(array());
        $this->prepareMocksForGenericResponse($this->plugin->getHelpLines());
        $this->plugin->handleCommand($this->eventMock, $this->queueMock);
    }

    public function testHandleCommandHelp()
    {
        $this->prepareMocksForGenericResponse($this->plugin->getHelpLines());
        $this->plugin->handleCommandHelp($this->eventMock, $this->queueMock);
    }

    protected function prepareMocksForGenericResponse($expectedLines)
    {
        $this->eventMock->shouldReceive('getSource')
            ->andReturn('#channel')
            ->times(count($expectedLines));

        foreach ($expectedLines as $expectedLine) {
            $this->queueMock->shouldReceive('ircPrivmsg')
                ->once()
                ->withArgs(array('#channel', $expectedLine));
        }
    }

    protected function prepareMocksForParamValidation($customParamArray = array())
    {
        $this->eventMock->shouldReceive('getCustomParams')
            ->andReturn($customParamArray)
            ->once();
    }

    /**
     * Returns a mock command event.
     *
     * @return \Phergie\Irc\Plugin\React\Command\CommandEvent
     */
    protected function getMockEvent()
    {
        return m::mock('\Phergie\Irc\Plugin\React\Command\CommandEvent');
    }

    /**
     * Returns a mock event queue.
     *
     * @return \Phergie\Irc\Bot\React\EventQueueInterface
     */
    protected function getMockQueue()
    {
        return m::mock('\Phergie\Irc\Bot\React\EventQueueInterface');
    }
}
