<?php
/**
 * Phergie plugin for PHP function lookups (https://github.com/chrismou/phergie-irc-plugin-react-php)
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-php for the canonical source repository
 * @copyright Copyright (c) 2014 Chris Chrisostomou (http://mou.me)
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
        if (isset($config['dbpath'])) {
            $connectionParams = array(
                'driver' => 'pdo_sqlite',
                'path' => $config['dbpath']
            );
            try {
                // Load the DB and attempt a connection to ensure it's valids
                $this->db = DriverManager::getConnection($connectionParams, new \Doctrine\DBAL\Configuration());
                $this->db->connect();
            } catch (\PDOException $e) {
                // Handle failed connections - most likely the supplied config path was wrong
                $this->db = null;
                //$this->logger->debug('[PHP Plugin] ' . $e->getMessage());
            }
        }
    }

    /**
     *
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'command.php'       => 'handleCommand',
            'command.php.help'  => 'handleCommandHelp'
        );
    }

    /**
     * Handle the main "php" command
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
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

        $functionName = $event->getCustomParams()[0];

        $function = $this->db->fetchAssoc('SELECT * FROM function f WHERE name = ?', array($functionName));

        if (is_array($function) && count($function)) {
            $this->sendIrcResponseLine($event, $queue, sprintf('%s ( %s )', $function['name'], $function['parameterString']));
            if ($function['description']) {
                $this->sendIrcResponseLine($event, $queue, $function['description']);
            }
        } else {
            $this->sendIrcResponseLine($event, $queue, sprintf("The PHP function '%s' cannot be found", $functionName));
        }
    }

    /**
     * Handle the help command
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleCommandHelp(Event $event, Queue $queue)
    {
        $this->sendIrcResponse($event, $queue, $this->getHelpLines());
    }

    /**
     * Handle errors
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleCommandError(Event $event, Queue $queue)
    {
        $this->sendIrcResponse($event, $queue, $this->getErrorLines());
    }

    public function getSuccessLines()
    {
        //TODO: extract response generation from handleCommand() & update testHandleCommandSuccessfully()
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
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @return bool
     */
    protected function validateParams(Event $event)
    {
        return (count($event->getCustomParams())>0) ? true : false;
    }

    /**
     * Send an array of response lines back to IRC
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
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
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     * @param string $ircResponseLine
     */
    protected function sendIrcResponseLine(Event $event, Queue $queue, $ircResponseLine)
    {
        $queue->ircPrivmsg($event->getSource(), $ircResponseLine);
    }
}
