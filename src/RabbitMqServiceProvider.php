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

        $app['rabbit.listener'] = $app->share(function ($app) {
            return new EventForwardListener(
                LazyProducer::init($app, isset($app['rabbit.forward.producer']) ? $app['rabbit.forward.producer'] : null)
            );
        });

        $app['dispatcher']->addListener(ConsoleEvents::INIT, function (ConsoleEvent $event) {
            $console = $event->getApplication();

            $console->addCommands([
                new Command\SetupFabricCommand(),
                new \fiunchinho\Silex\Command\Consumer(),
            ]);

            if (class_exists('Guzzle\Http\Client')) {
                $console->addCommands([
                    new Command\DeleteQueues(),
                ]);
            }
        });
    }

    public function boot(Application $app)
    {
        parent::boot($app);

        if (isset($app['rabbit.forward.events'])) {
            foreach ($app['rabbit.forward.events'] as $event) {
                $app['dispatcher']->addListener($event, [$app['rabbit.listener'], 'onEvent']);
            }
        }
    }
}
