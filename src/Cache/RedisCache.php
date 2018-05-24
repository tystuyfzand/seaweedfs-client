<?php

namespace SeaweedFS\Cache;

use Predis\Client as RedisClient;

/**
 * Cache implementation using Predis.
 *
 * @package SeaweedFS\Cache
 */
class RedisCache implements CacheInterface {

    /**
     * @var RedisClient The redis client instance.
     */
    private $client;

    /**
     * Construct a new Redis cache.
     *
     * @param RedisClient $client
     */
    public function __construct(RedisClient $client) {
        $this->client = $client;
    }

    /**
     * Check if the cache implementation contains the specified key.
     *
     * @param $key
     * @return bool
     */
    public function has($key) {
        return !is_null($this->get($key));
    }

    /**
     * Get the specified key from the cache implementation.
     *
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        $value = $this->client->get($key);

        return !is_null($value) ? unserialize($value) : $default;
    }

    /**
     * Set the value in the cache implementation.
     *
     * @param $key
     * @param $value
     * @param int $minutes
     * @return void
     */
    public function put($key, $value, $minutes = 0) {
        if ($minutes == 0) {
            $this->client->set($key, serialize($value));
        } else {
            $this->client->set($key, serialize($value), null, $minutes * 60);
        }
    }

    /**
     * Remove a value from the cache.
     *
     * @param $key
     * @return mixed
     */
    public function remove($key) {
        $this->client->del($key);
    }
}