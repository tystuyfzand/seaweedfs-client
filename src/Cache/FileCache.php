<?php

namespace SeaweedFS\Cache;

/**
 * A basic cache that uses the Filesystem for storage.
 *
 * @package SeaweedFS\Cache
 */
class FileCache implements CacheInterface {
    private $baseDir;

    public function __construct($baseDir) {
        $this->baseDir = $baseDir;
    }

    public function has($key) {
        return file_exists($this->baseDir . '/' . md5($key));
    }

    public function get($key, $default = null) {
        if (!$this->has($key)) {
            return $default;
        }

        return @unserialize(file_get_contents($this->baseDir . '/' . md5($key))) ?: $default;
    }

    public function put($key, $value, $minutes = 0) {
        file_put_contents($this->baseDir . '/' . md5($key), serialize($value));
    }

    public function remove($key) {
        return unlink($this->baseDir . '/' . md5($key));
    }
}