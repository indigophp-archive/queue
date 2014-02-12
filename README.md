# Indigo Queue

[![Build Status](https://travis-ci.org/indigophp/queue.png?branch=develop)](https://travis-ci.org/indigophp/queue)
[![Code Coverage](https://scrutinizer-ci.com/g/indigophp/queue/badges/coverage.png?s=81febd5af1f6e48a370b7753f4c81416d981e924)](https://scrutinizer-ci.com/g/indigophp/queue/)
[![Latest Stable Version](https://poser.pugx.org/indigophp/queue/v/stable.png)](https://packagist.org/packages/indigophp/queue)
[![Total Downloads](https://poser.pugx.org/indigophp/queue/downloads.png)](https://packagist.org/packages/indigophp/queue)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/indigophp/queue/badges/quality-score.png?s=83208d2af7fe392c2942a17fd1f2641fb0f9032d)](https://scrutinizer-ci.com/g/indigophp/queue/)
[![License](https://poser.pugx.org/indigophp/queue/license.png)](https://packagist.org/packages/indigophp/queue)

**Indigo Queue manages your queues and processes the jobs you put onto them.**


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

There is also a special implementation, where to job is not sent to a queue, but executed immediately.

### Connector

Connector does the communication between the server and the Queue/Worker class.


### Queue

You use the Queue class to push jobs to a queue. You can also push a job with a delay.

See [Queue example](examples/Queue.php).


### Worker

You usually set up a console application for your workers.

See [Worker example](examples/Worker.php).


### Job

See [examples](examples);


### Special connector: DirectConnector

This connector does what you think: Executes the pushed job immediately. You can also push a delayed job, BUT BE CAREFUL: this means that your application will sleep for a certain time, so use it wisely.


## Testing

``` bash
$ phpunit
```


## Contributing

Please see [CONTRIBUTING](https://github.com/indigophp/queue/blob/develop/CONTRIBUTING.md) for details.


## Credits

- [Márk Sági-Kazár](https://github.com/sagikazarmark)
- [All Contributors](https://github.com/indigophp/queue/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/indigophp/queue/blob/develop/LICENSE) for more information.