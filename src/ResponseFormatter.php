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

/**
 * Response Formatter.
 *
 * @category Chrismou
 * @package Chrismou\Phergie\Plugin\Php
 */
class ResponseFormatter
{
    public function format(array $function, $color = true)
    {
        return $this->buildParamString($function);
    }

    protected function buildParamString($param)
    {
        return sprintf(
            '%s%s %s%s%s',
            (isset($param['choice'])) ? '[ ' : '',
            $param['type'],
            $param['parameter'],
            isset($param['initializer']) ? sprintf(' = %s', $param['initializer']) : '',
            (isset($param['choice'])) ? ' ]' : ''
        );
    }
}
