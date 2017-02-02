<?php

namespace LaravelPSRedis;

use PSRedis\Client as PSRedisClient;
use PSRedis\HAClient;
use PSRedis\MasterDiscovery;
use PSRedis\MasterDiscovery\BackoffStrategy\Incremental;

class Driver
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor
     *
     * @param array $config
     *
     * @internal param string $rootConfigPath
     */
    public function __construct($config)
    {
        $this->config = $config;
    }


    /**
     * Get the config values for the redis database.
     *
     * @return array
     */
    public function getConfig()
    {
        $masterDiscovery = new MasterDiscovery($this->getSettings('nodeSetName'));

        /** @var array $backOffConfig */
        $backOffConfig = $this->getSettings('backoff-strategy');

        /** @var Incremental $incrementalBackOff */
        $incrementalBackOff = new Incremental(
            $backOffConfig['wait-time'],
            $backOffConfig['increment']
        );
        $incrementalBackOff->setMaxAttempts($backOffConfig['max-attempts']);
        $masterDiscovery->setBackoffStrategy($incrementalBackOff);

        foreach ($this->getSettings('masters') as $client) {
            $sentinel = new PSRedisClient($client['host'], $client['port']);
            $masterDiscovery->addSentinel($sentinel);
        }

        $HAClient = new HAClient($masterDiscovery);

        return [
            'cluster' => $this->getSettings('cluster'),
            'default' => [
                'host'     => $HAClient->getIpAddress(),
                'port'     => $HAClient->getPort(),
                'password' => $this->getSettings('password', null),
                'database' => $this->getSettings('database', 0),
            ]
        ];
    }


    /**
     * Get settings
     *
     * @param $name
     * @param $default
     *
     * @return mixed
     */
    protected function getSettings($name, $default = null)
    {
        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }
}
