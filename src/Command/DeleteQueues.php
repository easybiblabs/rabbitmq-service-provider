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

use Guzzle\Http\Client;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteQueues extends Command
{
    protected function configure()
    {
        $this
            ->setName('rabbitmq:delete-default-queues')
            ->setDefinition([])
            ->setDescription('Deletes all queues created with an empty name on the default connection.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $info = $this->getConnectionInfo();
        $info = array_merge($info, $this->askAuth($output));

        $client = new Client('http://'.$info['host'].':15672');

        $queues = $this->sendQueueRequest($client, $info, 'GET');

        $counter = 0;
        foreach ($queues as $queue) {
            if (preg_match('#amq.gen-([a-zA-Z0-9_-]+)#', $queue['name'])) {
                $this->deleteQueue($client, $info, $queue);
                $counter++;
            }
        }

        $output->writeln("Deleted $counter queues.");
    }

    protected function deleteQueue(Client $client, array $info, array $queue)
    {
        $this->sendQueueRequest($client, $info, 'DELETE', '/'.$queue['name']);
    }

    protected function sendQueueRequest($client, $info, $method, $path = '')
    {
        $request = $client->createRequest(
            $method,
            '/api/queues/'.$info['vhost'].$path,
            ['Content-Type' => 'application/json']
        );
        $request->setAuth($info['user'], $info['password']);

        return $request->send()->json();
    }

    protected function askAuth(OutputInterface $output)
    {
        $dialog = $this->getHelper('dialog');

        return [
            'user' => $dialog->ask(
                $output,
                'RabbitMQ Administrator username: ',
                false
            ),
            'password' => $dialog->askHiddenResponse(
                $output,
                'Password: ',
                false
            ),
        ];
    }

    protected function getConnectionInfo()
    {
        return $this->getSilexApplication()['rabbit.connections']['default'];
    }
}
