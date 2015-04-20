# RabbitMQ Service Provider for Silex

## About

This service provider expands [fiunchinho/rabbitmq-service-provider](https://github.com/fiunchinho/rabbitmq-service-provider)
with further functionality:

- forwarding of serializable events to RabbitMQ
- command to run fabric setup based on [RabbitMqBundle](http://github.com/videlalvaro/RabbitMqBundle)

## Usage

In addition to the standard configuration you can specify a list of Symfony
Event names in `rabbit.forward.events` which will be published on the producer
with the name in `rabbit.forward.producer`. These events must inherit from
`EasyBib\Silex\RabbitMq\SerializeableEvent`.

Example configuration

```php
$app->register(new RabbitMqServiceProvider(), [
    'rabbit.connections' => [
        'default' => [
            'host'      => 'localhost',
            'port'      => 5672,
            'user'      => 'guest',
            'password'  => 'guest',
            'vhost'     => '/'
        ],
        'another' => [
            'host'      => 'another_host',
            'port'      => 5672,
            'user'      => 'guest',
            'password'  => 'guest',
            'vhost'     => '/'
        ]
    ],
    'rabbit.producers' => [
        'first_producer' => [
            'connection'        => 'another',
            'exchange_options'  => ['name' => 'a_exchange', 'type' => 'topic']
        ],
        'second_producer' => [
            'connection'        => 'default',
            'exchange_options'  => ['name' => 'a_exchange', 'type' => 'topic']
        ],
    ],
    'rabbit.consumers' => [
        'a_consumer' => [
            'connection'        => 'default',
            'exchange_options'  => ['name' => 'a_exchange','type' => 'topic'],
            'queue_options'     => ['name' => 'a_queue', 'routing_keys' => ['foo.#']],
            'callback'          => 'your_consumer_service'
        ]
    ],
    'rabbit.forward.events' => [
        'foo.my.event',
        'foo.some.event',
        'bar.the.other',
    ],
    'rabbit.forward.producer' => 'first_producer',
]);
```
