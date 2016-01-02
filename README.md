# PHP function lookup plugin for [Phergie](https://github.com/phergie/phergie-irc-bot-react/)

[Phergie](https://github.com/phergie/phergie-irc-bot-react/) plugin for PHP function lookups.

[![Build Status](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-php/badges/build.png?b=master)](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-php/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-php/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-php/?branch=master)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```
composer require chrismou/phergie-irc-plugin-react-php
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

```php
new \Chrismou\Phergie\Plugin\Php\Plugin
```

Or you can use your own sqlite DB, by passing in something like array('dbpath'=>'path/to/your/db'). 
The included DB is generated using [phpdocs-to-db](https://github.com/chrismou/phpdocs-to-db) which is a work in progress - if you feel you can 
improve on it feel free to fork, improve and put in a pull request!

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
