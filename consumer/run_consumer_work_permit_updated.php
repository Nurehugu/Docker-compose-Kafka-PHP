<?php

const KAFKA_PARTITION = 0;
const KAFKA_TOPIC_TEST = 'work-permit-updated';
const BROKERS = 'c2-185-102-122-69.elastic.cloud.croc.ru:9095';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/vendor/autoload.php';

$logger = new Logger('consumer');
$logger->pushHandler(new StreamHandler(__DIR__ . '/data/logs/consumer.log'));
$logger->debug('Running consumer...');

$conf = new RdKafka\Conf();

// Set a rebalance callback to log partition assignments (optional)
$conf->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
    switch ($err) {
        case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
            echo "Assign: ";
            var_dump($partitions);
            $kafka->assign($partitions);
            break;

        case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
            echo "Revoke: ";
            var_dump($partitions);
            $kafka->assign(NULL);
            break;

        default:
            throw new \Exception($err);
    }
});

// Configure the group.id. All consumer with the same group.id will consume
// different partitions.
$conf->set('group.id', 'testWorkPermitUpdatedGroupId');

// Initial list of Kafka brokers
$conf->set('metadata.broker.list', BROKERS);

// Set where to start consuming messages when there is no initial offset in
// offset store or the desired offset is out of range.
// 'smallest': start from the beginning
$conf->set('auto.offset.reset', 'smallest');

$consumer = new RdKafka\KafkaConsumer($conf);

// Subscribe to topic 'test'
$consumer->subscribe([KAFKA_TOPIC_TEST]);

echo "Waiting for partition assignment... (make take some time when\n";
echo "quickly re-joining the group after leaving it.)\n";


while (true) {
    $message = $consumer->consume(120*10000);
    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            $logger->info($message->payload);
            var_dump($message->payload);
            break;
        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            $logger->debug('No more messages; will wait for more');
            break;
        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            $logger->warn('Timed out');
            break;
        default:
            $logger->err($message->errstr() . ' - ' . $message->err);
            throw new \Exception($message->errstr(), $message->err);
            break;
    }
}
