# Indigo Queue

[![Build Status](https://img.shields.io/travis/indigophp/queue/develop.svg?style=flat-square)](https://travis-ci.org/indigophp/queue)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/indigophp/queue.svg?style=flat-square)](https://scrutinizer-ci.com/g/indigophp/queue)
[![Packagist Version](https://img.shields.io/packagist/v/indigophp/queue.svg?style=flat-square)](https://packagist.org/packages/indigophp/queue)
[![Total Downloads](https://img.shields.io/packagist/dt/indigophp/queue.svg?style=flat-square)](https://packagist.org/packages/indigophp/queue)
[![Quality Score](https://img.shields.io/scrutinizer/g/indigophp/queue.svg?style=flat-square)](https://scrutinizer-ci.com/g/indigophp/queue)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Dependency Status](http://www.versioneye.com/user/projects/53cd7ce82254268535000153/badge.svg?style=flat)](http://www.versioneye.com/user/projects/53cd7ce82254268535000153)

**Indigo Queue is a backend agnostic message queue implementation with a work queue implementation on top of it.**


## Install

Via Composer

``` json
{
    "require": {
        "indigophp/queue": "@stable"
    }
}
```


## Usage

First of all you have decide which MQ do you want to use. Currently supported MQs:

* [Beanstalkd](http://kr.github.io/beanstalkd/)
* [RabbitMQ](http://www.rabbitmq.com/)
* [IronMQ](http://www.iron.io/)

There is also a special implementation, where to message is not sent to a queue, but processed immediately.

### Adapter

Adapter handles the communication between the backend and the Queue/Worker class.


### Message

A message contains all relevant data you want to store and get back later. It also holds some other data which is only important if you get your message back. (For example: id, attempts)


### Queue

You use the Queue class to push messages to a queue.

See [Queue example](examples/Queue.php).


### Worker

You usually set up a console application for your workers.

See [Worker example](examples/Worker.php).


### Special adapter: DirectAdapter

This adapter does what you think: Processes the pushed message immediately. Delayed message implementations are not possible since only one message can be in the queue at a time.


## Testing

``` bash
$ codecept run
```


## Contributing

Please see [CONTRIBUTING](https://github.com/indigophp/queue/blob/develop/CONTRIBUTING.md) for details.


## Credits

- [Márk Sági-Kazár](https://github.com/sagikazarmark)
- [All Contributors](https://github.com/indigophp/queue/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/indigophp/queue/blob/develop/LICENSE) for more information.
