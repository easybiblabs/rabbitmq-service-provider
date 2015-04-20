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

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Silex\Application;

/**
 * Proxy for a RabbitMq Producer object to be loaded lazily
 */
class LazyProducer
{
    /**
     * @var callable
     */
    protected $accessor;

    /**
     * @var bool
     */
    protected $resolved;

    /**
     * @var ProducerInterface
     */
    protected $producer;

    /**
     * @param callable $accessor Must return a ProducerInterface
     */
    public function __construct($accessor)
    {
        $this->accessor = $accessor;
        $this->resolved = false;
        $this->producer = null;
    }

    public function __call($name, $arguments)
    {
        if (!$this->resolved) {
            $accessor = $this->accessor;
            $this->producer = $accessor();
            if (!($this->producer instanceof ProducerInterface)) {
                throw new \UnexpectedValueException(
                    "Expected a RabbitMq\\ProducerInterface, got a ".get_class($this->producer)
                );
            }
            $this->resolved = true;
        }
        return call_user_func_array([$this->producer, $name], $arguments);
    }

    public static function init(Application $app, $name)
    {
        return new static(
            function () use ($app, $name) {
                return $app['rabbit.producer'][$name];
            }
        );
    }
}
