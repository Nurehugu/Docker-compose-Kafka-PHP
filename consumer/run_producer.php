<?php

const KAFKA_PARTITION = 0;
const KAFKA_TOPIC_TEST = 'work-permit-updated-external';
const BROKERS = 'c2-185-102-122-69.elastic.cloud.croc.ru:9095';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/vendor/autoload.php';

$logger = new Logger('producer');
$logger->pushHandler(new StreamHandler(__DIR__ . '/data/logs/producer.log'));
$logger->debug('Running producer...');

$conf = new RdKafka\Conf();
//$conf->set('debug','all');

$kafka = new RdKafka\Producer($conf);
$kafka->setLogLevel(LOG_DEBUG);
$kafka->addBrokers(BROKERS);

$topic = $kafka->newTopic(KAFKA_TOPIC_TEST);

$data = json_encode([
  "@class"=>"ru.croc.whswd.api.impl.event.WorkPermitEvent",
  "sourceSystemId"=>"BREALIT",
  "workPermitExternalId"=>"brealit-001"
]);

$message = sprintf($data);
//$logger->debug(sprintf('Producing: %s', $message));
$topic->produce(KAFKA_PARTITION, 0, $message);
$kafka->poll(0);

$result = $kafka->flush(10000);
if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
    var_dump('No ERROR');
} else {
  var_dump('Error in broker when flushing: ' . $result);
}

if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
    throw new \RuntimeException('Was unable to flush, messages might be lost!');
}
