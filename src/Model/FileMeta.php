<?php

namespace SeaweedFS\Model;

/**
 * SeaweedFS File Metadata
 * Mime Type, Size, and Modified time.
 *
 * @package SeaweedFS\Model
 */
class FileMeta {
    /**
     * @var string The file's mime type.
     */
    public $mimeType;

    /**
     * @var int The file's size.
     */
    public $size;

    /**
     * @var \DateTime The file modification time
     */
    public $modified;

    /**
     * FileMeta constructor.
     *
     * @param $mimeType
     * @param $size
     * @param $modified
     */
    public function __construct($mimeType, $size, $modified) {
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->modified = $modified;
    }
}