<?php

namespace SeaweedFS\Cache;

/**
 * Cache implementation using Memcached.
 *
 * @package SeaweedFS\Cache
 */
class MemcachedCache implements CacheInterface {

    /**
     * @var \Memcached The Memcached client.
     */
    private $memcached;

    /**
     * Construct a new Memcached cache interface.
     *
     * @param \Memcached $memcached
     */
    public function __construct(\Memcached $memcached) {
        $this->memcached = $memcached;
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
        $value = $this->memcached->get($key);

        if ($this->memcached->getResultCode() == 0) {
            return $value;
        }

        return $default;
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
        $this->memcached->set($key, serialize($value), $minutes * 60);
    }

    /**
     * Remove a value from the cache.
     *
     * @param $key
     * @return void
     */
    public function remove($key) {
        $this->memcached->delete($key);
    }
}