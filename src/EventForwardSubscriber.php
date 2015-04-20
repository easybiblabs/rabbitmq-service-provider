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

use InfoLit\Event\SerializableEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventForwardSubscriber implements EventSubscriberInterface
{
    protected $events;
    protected $producer;

    public function __construct(array $events, LazyProducer $producer)
    {
        $this->events = $events;
        $this->producer = $producer;
    }

    public static function getSubscribedEvents()
    {
        $subscribe = [];
        foreach ($this->events as $event) {
            $subscribe[$event] = ['onEvent', 0];
        }

        return $subscribe;
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
