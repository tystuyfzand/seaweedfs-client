<?php

namespace SeaweedFS\Model;

/**
 * Represents a Seaweedfs File
 *
 * @package SeaweedFS\Model
 */
class File extends Location {
    /**
     * @var string The file id.
     */
    public $fid;

    /**
     * @var int The file size.
     */
    public $size;

    /**
     * @var string The request scheme for building file urls.
     */
    public $scheme;

    /**
     * File constructor.
     * @param $obj
     * @param string $scheme
     */
    public function __construct($obj, $scheme = 'http') {
        parent::__construct($obj);

        $this->fid = $obj->fid;
        $this->scheme = $scheme;
    }

    /**
     * Build a URL to this file.
     *
     * @return string
     */
    public function getFileUrl() {
        return $this->scheme . '://' . $this->url . '/' . $this->fid;
    }

    /**
     * Return the information of this File as an array.
     *
     * @return array
     */
    public function toArray() {
        return [
            'url' => $this->url,
            'fid' => $this->fid
        ];
    }
}