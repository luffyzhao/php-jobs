<?php


namespace Job;


class Credentials
{
    /**
     * 地址格式
     */
    const ADDRESS_FORMAT = '%s:%d';

    /**
     * 队列连接驱动 host
     * @var string
     * @author luffyzhao@vip.126.com
     */
    private $host;

    /**
     * 队列连接驱动 port
     * @var int
     * @author luffyzhao@vip.126.com
     */
    private $port;

    /**
     * 队列连接驱动 password
     * @var string
     * @author luffyzhao@vip.126.com
     */
    private $password;

    /**
     * 队列连接驱动 连接超时时间
     * @var int
     * @author luffyzhao@vip.126.com
     */
    private $connectionTimeout;

    /**
     * 队列操作超时时间
     * @var int
     * @author luffyzhao@vip.126.com
     */
    private $responseTimeout;

    /**
     * @var string 队列名称
     * @author luffyzhao@vip.126.com
     */
    private $queue;

    /**
     * Credentials constructor.
     * @param $host
     * @param $port
     * @param null|string $password
     * @param int $connectionTimeout
     * @param int $responseTimeout
     * @param string $queue
     * @author luffyzhao@vip.126.com
     */
    public function __construct(
        string $host,
        int $port,
        ?string $password = null,
        string $queue = 'default',
        int $connectionTimeout = 5,
        int $responseTimeout = 5
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->connectionTimeout = $connectionTimeout;
        $this->responseTimeout = $responseTimeout;
        $this->queue = $queue;
    }

    /**
     * @return string
     * @author luffyzhao@vip.126.com
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     * @author luffyzhao@vip.126.com
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     * @author luffyzhao@vip.126.com
     */
    public function getAddress(): string
    {
        return sprintf(self::ADDRESS_FORMAT, $this->host, $this->port);
    }

    /**
     * @return string|null
     * @author luffyzhao@vip.126.com
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return bool
     * @author luffyzhao@vip.126.com
     */
    public function havePassword(): bool
    {
        return !empty($this->password);
    }

    /**
     * @return int
     * @author luffyzhao@vip.126.com
     */
    public function getConnectionTimeout(): int
    {
        return $this->connectionTimeout;
    }

    /**
     * @return int
     * @author luffyzhao@vip.126.com
     */
    public function getResponseTimeout(): int
    {
        return $this->responseTimeout;
    }

    /**
     * @return string
     * @author luffyzhao@vip.126.com
     */
    public function getQueue(): string
    {
        return $this->queue;
    }
}