## php 队列生产和消费

目前只完成 [beanstalk](./src/Factory/PheanstalkFactory.php) 和 [redis](./src/Factory/RedisFactory.php) 

### 服务器要求

- PHP >= 7.1.3
- ext-pcntl
- ext-pdo

### 例子

- [生产](examples/test.php)
