<?php

namespace J4k\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * @see     https://vk.com/dev/fields
 *
 * @package J4k\OAuth2\Client\Provider
 */
class User implements ResourceOwnerInterface
{
    // ========== Interface ==========

    /**
     * @type array
     */
    protected $response;

    /**
     * User constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }
    /**
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
    /**
     * @return integer
     */
    public function getId()
    {
        return (int)$this->response['uid'];
    }
}
