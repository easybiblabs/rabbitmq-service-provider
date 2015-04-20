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

namespace EasyBib\Silex\RabbitMq\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetupFabricCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('rabbitmq:setup-fabric')
            ->setDescription('Sets up the Rabbit MQ fabric')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable Debugging')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (defined('AMQP_DEBUG') === false) {
            define('AMQP_DEBUG', (bool) $input->getOption('debug'));
        }
        $output->writeln('Setting up the Rabbit MQ fabric');

        $parts = array_merge(
            $this->getSilexApplication()['rabbit.consumer'],
            $this->getSilexApplication()['rabbit.producer']
        );
        foreach ($parts as $baseAmqp) {
            $baseAmqp->setupFabric();
        }
    }
}
