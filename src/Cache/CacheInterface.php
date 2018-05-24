<?php

namespace SeaweedFS\Cache;

/**
 * A base Cache interface that can be implemented as many different caches.
 *
 * @package SeaweedFS\Cache
 */
interface CacheInterface {

    /**
     * Check if the cache implementation contains the specified key.
     *
     * @param $key
     * @return bool
     */
    public function has($key);

    /**
     * Get the specified key from the cache implementation.
     *
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Set the value in the cache implementation.
     *
     * @param $key
     * @param $value
     * @param int $minutes
     * @return void
     */
    public function put($key, $value, $minutes = 0);

    /**
     * Remove a value from the cache.
     *
     * @param $key
     * @return void
     */
    public function remove($key);
}