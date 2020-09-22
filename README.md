# KAFKA

## Consumer

---
### Composer install
---
```
docker-compose up -d
docker-compose run --rm app-kafka-consummer composer install
```

---
### Run consumer
---
```
docker-compose run --rm --entrypoint php app-kafka-consumer run_consumer_low_level.php
```
OR
```
docker-compose run --rm --entrypoint php app-kafka-consumer run_consumer_high_level.php
```

---
### Log
---
```
tail data/logs/*.log
```


---
## Producer
---

---
### Composer install
---
```
docker-compose up -d
docker-compose run --rm app-kafka-produser composer install
```
---
### Run producer
---
```
docker-compose run --rm --entrypoint php app-kafka-produser run_producer.php
```
---
### Log
---
```
tail data/logs/*.log
```
