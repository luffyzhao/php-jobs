<?php
namespace Job\Queue;

use Interop\Amqp\AmqpQueue;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue as RabbitMQQueueBase;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob;

class RabbitMQQueue extends RabbitMQQueueBase
{
    /** {@inheritdoc} */
    public function popForTimeout($queueName = null, $timeout = 0)
    {
        try {
            /** @var AmqpQueue $queue */
            [$queue] = $this->declareEverything($queueName);

            $consumer = $this->context->createConsumer($queue);

            if ($message = $consumer->receive($timeout)) {
                return new RabbitMQJob($this->container, $this, $consumer, $message);
            }
        } catch (\Throwable $exception) {
            $this->reportConnectionError('pop', $exception);

            return;
        }
    }
}