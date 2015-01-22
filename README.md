# PHP function lookup plugin for [Phergie](https://github.com/phergie/phergie-irc-bot-react/)

[Phergie](https://github.com/phergie/phergie-irc-bot-react/) plugin for PHP function lookups.

[![Build Status](https://secure.travis-ci.org/chrismou/phergie-irc-plugin-react-php.png?branch=master)](http://travis-ci.org/chrismou/phergie-irc-plugin-react-php)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "chrismou/phergie-irc-plugin-react-php": "dev-master"
    }
}
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

```php
new \Chrismou\Phergie\Plugin\Php\Plugin(array(
    'dbpath' => __DIR__.'/data/phpdoc.db'
))
```

Or you can pass in a reference to your own sqlite DB. The included DB is generated using [phpdocs-to-db](https://github.com/chrismou/phpdocs-to-db)
which is a work in progress - if you feel you can improve on it feel free to fork, improve and put in a pull request!

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
