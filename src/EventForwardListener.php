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

use Symfony\Component\EventDispatcher\Event;

class EventForwardListener
{
    protected $producer;

    public function __construct(LazyProducer $producer)
    {
        $this->producer = $producer;
    }

    public function onEvent(Event $event)
    {
        if (!($event instanceof SerializableEvent)) {
            throw new \RuntimeException("Could not serialize event for RabbitMQ: ".$event->getName());
        }

        // publish on rabbitmq
        $this->producer->publish(serialize($event), $event->getName());
    }
}
