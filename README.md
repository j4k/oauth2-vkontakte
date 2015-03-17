# Vkontakte OAuth2 client provider

[![Build Status](https://travis-ci.org/j4k/oauth2-vkontakte.svg?branch=master)](https://travis-ci.org/j4k/oauth2-vkontakte)
[![Latest Stable Version](https://img.shields.io/packagist/v/j4k/oauth2-vkontakte.svg)](https://packagist.org/packages/j4k/oauth2-vkontakte)
[![License](https://img.shields.io/packagist/l/j4k/oauth2-vkontakte.svg)](https://packagist.org/packages/j4k/oauth2-vkontakte)

This package provides [Vkontakte](https://vk.com) integration for [OAuth2 Client](https://github.com/thephpleague/oauth2-client) by the League.

## Installation

```sh
composer require j4k/oauth2-vkontakte
```

## Usage

```php
$provider = new J4k\OAuth2\Client\Provider\Vkontakte([
    'clientId' => '1234567',
    'clientSecret' => 's0meRe4lLySEcRetC0De',
    'redirectUri' => 'https://example.org/oauth-endpoint',
]);
```
