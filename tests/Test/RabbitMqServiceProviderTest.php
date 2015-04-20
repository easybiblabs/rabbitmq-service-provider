<?php

namespace EasyBib\Silex\RabbitMq\Test;

use EasyBib\Silex\RabbitMq\RabbitMqServiceProvider;
use EasyBib\Silex\RabbitMq\EventForwardListener;
use fiunchinho\Silex\Provider\RabbitServiceProviderTest;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RabbitMqServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testSubscriber()
    {
        $app = new Application();
        $app['dispatcher'] = new EventDispatcher;

        $app->register(new RabbitMqServiceProvider(), [
            'rabbit.connections' => $this->givenSomeValidConnections(),
            'rabbit.producers' => [
                'a_producer' => [
                    'connection'        => 'default',
                    'exchange_options'  => ['name' => 'a_exchange', 'type' => 'topic']
                ],
            ],
            'rabbit.forward.events' => [
                'foo.event',
                'bar.event',
            ],
            'rabbit.forward.producer' => 'a_producer',
        ]);

        $app->boot();

        $this->assertInstanceOf(EventForwardListener::class, $app['rabbit.listener']);
        $this->assertSame($app['rabbit.listener'], $app['dispatcher']->getListeners('foo.event')[0][0]);
        $this->assertSame($app['rabbit.listener'], $app['dispatcher']->getListeners('bar.event')[0][0]);
    }

    private function givenSomeValidConnections()
    {
        return [
            'default' => [
                'host' => 'localhost',
                'port' => 5672,
                'user' => 'guest',
                'password' => 'guest',
                'vhost' => '/'
            ],
            'another' => [
                'host' => 'localhost',
                'port' => 5672,
                'user' => 'guest',
                'password' => 'guest',
                'vhost' => '/'
            ]
        ];
    }
}
