<?php

namespace J4k\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\GenericResourceOwner;

class User extends GenericResourceOwner
{
    public $email;
    public $location;
    public $description;
}
