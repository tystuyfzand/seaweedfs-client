SeaweedFS PHP Client
====================

A basic but functional PHP client for [seaweedfs](https://github.com/chrislusf/seaweedfs)

Usage
-----

Create an instance of the `SeaweedFS\SeaweedFS` class, optionally specifying `scheme` and `cache` for http/https and Volume lookup caching.

Example
-------

```php
<?php
$cache = new \SeaweedFS\Cache\FileCache('./cache');

$client = new SeaweedFS\SeaweedFS('127.0.0.1:9333', 'http', $cache);

// Upload a file and get the returned object (SeaweedFS\Models\File)
$file = $client->upload('test1234', 'test.txt');

// Update a file
$client->upload('Testing1234', 'test.txt', $file);

// Retrieve the file contents
$stream = $client->get($file->fid);

echo stream_get_contents($stream) . PHP_EOL;

// Delete a file
$client->delete($file->fid);

// Get a file's URL
echo "URL: " . $file->getFileUrl() . PHP_EOL;

// URLs can also be retrieved manually
$volume = $client->lookup($file->fid);

echo "URL (manual): " . $client->buildVolumeUrl($volume->getPublicUrl(), $file->fid) . PHP_EOL;
```

Other packages
--------------

* Flysystem-SeaweedFS
* Laravel-SeaweedFS