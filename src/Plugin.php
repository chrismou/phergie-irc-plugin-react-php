<?php
/**
 * Phergie plugin for PHP function lookups (https://github.com/chrismou/phergie-irc-plugin-react-php)
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-php for the canonical source repository
 * @copyright Copyright (c) 2016 Chris Chrisostomou (https://mou.me)
 * @license http://phergie.org/license New BSD License
 * @package Chrismou\Phergie\Plugin\Php
 */

namespace Chrismou\Phergie\Plugin\Php;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as Event;

use Doctrine\DBAL\DriverManager;
use Psr\Log\LoggerAwareInterface;

/**
 * Plugin class.
 *
 * @category Chrismou
 * @package Chrismou\Phergie\Plugin\Php
 */
class Plugin extends AbstractPlugin implements LoggerAwareInterface
{
    /** @var */
    protected $db;

    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *
     *
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (!isset($config['dbpath']) || !$config['dbpath']) {
            $config['dbpath'] = __DIR__ . '/../data/phpdoc.db';
        }

        try {
            $connectionParams = array(
                'driver' => 'pdo_sqlite',
                'path' => $config['dbpath']
            );
            $this->db = DriverManager::getConnection($connectionParams, new \Doctrine\DBAL\Configuration());
            $this->db->connect();
        } catch (\Exception $e) {
            $this->db = null;
        }
    }

    /**
     * Return an array of event subscriptions
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'command.php' => 'handleCommand',
            'command.php.help' => 'handleCommandHelp'
        );
    }

    /**
     * Handle the main "php" command
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleCommand(Event $event, Queue $queue)
    {
        if (!$this->db) {
            $this->handleCommandError($event, $queue);
            return;
        }

        if (!$this->validateParams($event)) {
            $this->handleCommandHelp($event, $queue);
            return;
        }

        $function = $this->doFunctionLookup($event->getCustomParams()[0]);

        $this->doSuccessResponse($event, $queue, $function);
    }

    /**
     * Perform a DB lookup on a provided PHP function name1
     *
     * @param string $functionName
     * @return mixed
     */
    public function doFunctionLookup($functionName)
    {
        return $this->db->fetchAssoc('SELECT * FROM function f WHERE name = ?', array($functionName));
    }

    /**
     * Handle the help command
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleCommandHelp(Event $event, Queue $queue)
    {
        $this->sendIrcResponse($event, $queue, $this->getHelpLines());
    }

    /**
     * Handle errors
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleCommandError(Event $event, Queue $queue)
    {
        $this->sendIrcResponse($event, $queue, $this->getErrorLines());
    }

    /**
     * Process the response from a successful DB query
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     * @param array $function
     */
    public function doSuccessResponse(Event $event, Queue $queue, $function)
    {
        if (is_array($function) && count($function)) {
            $this->sendIrcResponse($event, $queue, $this->getFoundFunctionLines($function));
        } else {
            $this->sendIrcResponse($event, $queue, $this->getUnknownFunctionLines());
        }
    }

    /**
     * Return an array of sucessful, 'function found' lines
     *
     * @param array $function
     * @return array
     */
    public function getFoundFunctionLines($function)
    {
        $response = array(
            sprintf(
                '%s ( %s )',
                $function['name'],
                $function['parameterString']
            )
        );
        if ($function['description']) {
            $response[] = $function['description'];
        }
        return $response;
    }

    /**
     * Return an array of unsucessful, 'function not found' lines
     *
     * @return array
     */
    public function getUnknownFunctionLines()
    {
        return array("Function not found");
    }

    /**
     * Return an array of help command response lines
     *
     * @return array
     */
    public function getHelpLines()
    {
        return array(
            'Usage: php [function] [full]',
            '[function] - the PHP function you want to search for',
            //'[full] (optional) - add "full" after the function name to include the description',
            'Returns information about the specified PHP function'
        );
    }

    /**
     * Return a=n array of error response lines
     *
     * @return array
     */
    public function getErrorLines()
    {
        return array('Something went wrong... ಠ_ಠ');
    }

    /**
     * Check the supplied parameters are valid
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEventInterface $event
     * @return bool
     */
    protected function validateParams(Event $event)
    {
        return (count($event->getCustomParams()) > 0) ? true : false;
    }

    /**
     * Send an array of response lines back to IRC
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     * @param array $ircResponse
     */
    protected function sendIrcResponse(Event $event, Queue $queue, array $ircResponse)
    {
        foreach ($ircResponse as $ircResponseLine) {
            $this->sendIrcResponseLine($event, $queue, $ircResponseLine);
        }
    }


    /**
     * Send a single response line back to IRC
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     * @param string $ircResponseLine
     */
    protected function sendIrcResponseLine(Event $event, Queue $queue, $ircResponseLine)
    {
        $queue->ircPrivmsg($event->getSource(), $ircResponseLine);
    }
}
