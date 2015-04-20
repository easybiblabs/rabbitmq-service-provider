<?php
/**
 * EasyBib Copyright 2014
 * Modifying, copying, of code contained herein that is not specifically
 * authorized by Imagine Easy Solutions LLC ("Company") is strictly prohibited.
 * Violators will be prosecuted.
 *
 * This restriction applies to proprietary code developed by EasyBib. Code from
 * third-parties or open source projects may be subject to other licensing
 * restrictions by their respective owners.
 *
 * Additional terms can be found at http://www.easybib.com/company/terms
 *
 * @license  http://www.easybib.com/company/terms Terms of Service
 * @link     http://www.easybib.com
 */

namespace EasyBib\Silex\RabbitMq;

use Symfony\Component\EventDispatcher\Event;

abstract class SerializableEvent extends Event implements \Serializable
{
    abstract function getProperties();

    public function serialize()
    {
        $properties = $this->getProperties();

        $data = [];
        foreach ($properties as $property) {
            $data[$property] = $this->$property;
        }
        $data['name'] = $this->getName();
        return serialize($data);
    }

    public function unserialize($data)
    {
        $data = unserialize($data);
        $properties = $this->getProperties();

        $this->setName($data['name']);
        foreach ($properties as $property) {
            $this->$property = $data[$property];
        }
    }
}
