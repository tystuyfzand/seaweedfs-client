<?php

namespace SeaweedFS\Model;

/**
 * Represents a Volume Server
 *
 * @package SeaweedFS\Model
 */
class Volume {
    /**
     * @var array Volume locations
     */
    public $locations = [];

    /**
     * Construct a Volume object from a base data object.
     *
     * @param $data
     */
    public function __construct($data) {
        foreach ($data->locations as $location) {
            $this->locations[] = new Location($location);
        }
    }

    /**
     * Pick a random volume location of the available locations.
     *
     * @return mixed
     */
    public function getLocation() {
        return $this->locations[array_rand($this->locations)];
    }

    /**
     * Get the location's public url
     * @return null
     */
    public function getPublicUrl() {
        $location = $this->getLocation();

        if (!$location) {
            throw new \IllegalArgumentException('Unable to find a location to return.');
        }

        return $location->publicUrl;
    }
}