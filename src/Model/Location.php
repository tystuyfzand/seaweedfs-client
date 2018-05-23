<?php

namespace SeaweedFS\Model;

/**
 * A model for a Seaweedfs Location, as defined by:
 * - url
 * - publicUrl
 *
 * File extends this model, and Volume contains multiple Locations.
 *
 * @package SeaweedFS\Model
 */
class Location {
    /**
     * @var string The location's url.
     */
    public $url;

    /**
     * @var string The location's public url.
     */
    public $publicUrl;

    /**
     * Location constructor.
     *
     * @param $data
     */
    public function __construct($data) {
        $this->url = $data->url;
        $this->publicUrl = $data->publicUrl;
    }

    /**
     * Get the volume location's url.
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Get the volume location's public url.
     *
     * @return string
     */
    public function getPublicUrl() {
        return $this->publicUrl;
    }
}