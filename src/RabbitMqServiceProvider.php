<?php
/**
 * This file is part of easybib/rabbitmq-service-provider
 *
 * (c) Imagine Easy Solutions, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author   Nils Adermann <naderman@naderman.de>
 * @license  BSD-2-Clause
 * @link     http://www.imagineeasy.com
 */

namespace EasyBib\Silex\RabbitMq;

use fiunchinho\Silex\Provider\RabbitServiceProvider;
use Knp\Console\ConsoleEvents;
use Knp\Console\ConsoleEvent;
use Silex\Application;
use Silex\ServiceProviderInterface;

class RabbitMqServiceProvider extends RabbitServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        if ($app['rabbit.forward.events']) {
            $app['rabbit.subscriber'] = $app->share(function ($app) {
                return new EventForwardSubscriber(
                    $app['rabbit.forward.events'],
                    LazyProducer::init($app, $app['rabbit.forward.producer'])
                );
            });

            $app['dispatcher']->addSubscriber($app['rabbit.subscriber']);
        }

        $app['dispatcher']->addListener(ConsoleEvents::INIT, function (ConsoleEvent $event) {
            $console = $event->getApplication();

            $console->addCommands([
                new Command\SetupFabricCommand(),
                new Command\DeleteQueues(),
                new \fiunchinho\Silex\Command\Consumer(),
            ]);
        });
    }

    public function boot(Application $app)
    {
        parent::boot($app);
    }
}
