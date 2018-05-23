<?php

namespace SeaweedFS;

use GuzzleHttp\Client;
use SeaweedFS\Cache\CacheInterface;
use SeaweedFS\Model\File;
use SeaweedFS\Model\FileMeta;
use SeaweedFS\Model\Volume;

class SeaweedFS {
    const DIR_ASSIGN = '/dir/assign';
    const DIR_LOOKUP = '/dir/lookup';

    /**
     * @var string A master server address.
     */
    protected $master;

    /**
     * @var string The API and File access scheme.
     */
    protected $scheme;

    /**
     * @var Client The preconfigured Guzzle client.
     */
    protected $client;

    /**
     * @var CacheInterface The cache interface for meta/volumes
     */
    protected $cache;

    /**
     * Construct a new SeaweedFS client.
     *
     * @param $master
     * @param string $scheme
     * @param CacheInterface|null $cache
     */
    public function __construct($master, $scheme = 'http', $cache = null) {
        $this->master = $master;
        $this->scheme = $scheme;
        $this->cache = $cache;

        $this->client = new Client();
    }

    /**
     * Get a volume and file id from the master server.
     *
     * @param int $count
     * @return File
     * @throws SeaweedFSException
     */
    public function assign($count = 1) {
        $res = $this->client->get($this->buildMasterUrl(self::DIR_ASSIGN), [
            'query' => [ 'count' => $count ]
        ]);

        if ($res->getStatusCode() != 200) {
            throw new SeaweedFSException('Unexpected response when assigning file: ' . $res->getStatusCode());
        }

        $body = json_decode((string) $res->getBody());

        return new File($body, $this->scheme);
    }

    /**
     * Lookup a volume or file on the master server.
     *
     * @param $id
     * @return Volume
     * @throws SeaweedFSException
     */
    public function lookup($id) {
        if ($pos = strpos($id, ',')) {
            $id = substr($id, 0, $pos);
        }

        $cacheKey = 'volume_' . $id;

        if ($this->cache && $this->cache->has($cacheKey)) {
            $val = $this->cache->get($cacheKey);

            if (!$val instanceof Volume) {
                $val = new Volume($val);
            }

            return $val;
        }

        $res = $this->client->get($this->buildMasterUrl(self::DIR_LOOKUP), [
            'query' => [ 'volumeId' => $id ]
        ]);

        if ($res->getStatusCode() != 200) {
            throw new SeaweedFSException('Unexpected response when looking up volume: ' . $res->getStatusCode());
        }

        $body = json_decode((string) $res->getBody());

        if ($this->cache) {
            $this->cache->put($cacheKey, $body);
        }

        return new Volume($body);
    }

    /**
     * Upload/update a file.
     *
     * If a file (provided by assign) does not exist, one will be created.
     *
     * @param resource|string $data The file data, either a string or resource.
     * @param string $filename
     * @param File|null $file A file object to update.
     * @return File
     * @throws SeaweedFSException
     */
    public function upload($data, $filename = 'file.txt', $file = null) {
        if (!$file) {
            $file = $this->assign();
        }

        if (empty($file->url) || empty($file->fid)) {
            throw new SeaweedFSException('File must contain a url and fid');
        }

        $res = $this->client->post($this->buildVolumeUrl($file->publicUrl, $file->fid), [
            'multipart' => [
                [
                    'name'     => 'file',
                    'filename' => $filename,
                    'contents' => $data
                ]
            ]
        ]);

        if ($res->getStatusCode() != 201) {
            throw new SeaweedFSException('Unexpected response when storing file: ' . $res->getStatusCode());
        }

        $body = json_decode((string) $res->getBody());

        $file->size = $body->size;

        if ($this->cache) {
            $this->cache->remove('file_meta_' . $file->fid);
        }

        return $file;
    }

    /**
     * Fetch a file from a volume.
     *
     * @param $fid
     * @param null $ext
     * @return resource
     * @throws SeaweedFSException
     */
    public function get($fid, $ext = null) {
        $volume = $this->lookup($fid);

        if (!$volume) {
            return null;
        }

        $path = $fid;

        if ($ext) {
            $path = $path . '.' . $ext;
        }

        $res = $this->client->get($this->buildVolumeUrl($volume->getPublicUrl(), $path));

        if ($res->getStatusCode() != 200) {
            throw new SeaweedFSException('Unexpected response when retrieving file: ' . $res->getStatusCode());
        }

        return $res->getBody()->detach();
    }

    /**
     * Check if the specified file exists.
     *
     * @param $fid
     * @return bool
     */
    public function has($fid) {
        try {
            return $this->meta($fid) != null;
        } catch (SeaweedFSException $e) {
            return false;
        }
    }

    /**
     * Get a file's information (type, size, filename)
     *
     * @param $fid
     * @return FileMeta
     * @throws SeaweedFSException
     */
    public function meta($fid) {
        $cacheKey = 'file_meta_' . $fid;

        if ($this->cache && $this->cache->has($cacheKey)) {
            $val = $this->cache->get($cacheKey);

            if (!$val instanceof FileMeta) {
                $val = new FileMeta($val);
            }

            return $val;
        }

        $volume = $this->lookup($fid);

        if (!$volume) {
            return null;
        }

        $res = $this->client->head($this->buildVolumeUrl($volume->publicUrl, $fid));

        if ($res->getStatusCode() != 200) {
            return null;
        }

        $meta = new FileMeta(
            $res->getHeaderLine('Content-Type'),
            $res->getHeaderLine('Content-Length'),
            new \DateTime($res->getHeaderLine('Last-Modified'))
        );

        if ($this->cache) {
            $this->cache->put($cacheKey, $meta);
        }

        return $meta;
    }

    /**
     * Delete the specified file.
     *
     * @param $fid
     * @return bool
     * @throws SeaweedFSException
     */
    public function delete($fid) {
        $volume = $this->lookup($fid);

        if (!$volume) {
            throw new SeaweedFSException('Unable to find volume for ' . $fid);
        }

        $res = $this->client->delete($this->buildVolumeUrl($volume->publicUrl, $fid));

        if ($res->getStatusCode() != 200) {
            throw new SeaweedFSException('Unexpected response when deleting file: ' . $res->getStatusCode());
        }

        if ($this->cache && $this->cache->has('file_meta_' . $fid)) {
            $this->cache->remove('file_meta_' . $fid);
        }

        return true;
    }

    /**
     * Build a URL to a master server path.
     *
     * @param $path
     * @return string
     */
    public function buildMasterUrl($path = null) {
        return sprintf('%s://%s/%s', $this->scheme, $this->master, $path ? ltrim($path, '/') : '');
    }

    /**
     * Build a URL to a volume server path.
     *
     * @param $host
     * @param null $path
     * @return string
     */
    public function buildVolumeUrl($host, $path = null) {
        return sprintf('%s://%s/%s', $this->scheme, $host, $path ? ltrim($path, '/') : '');
    }
}