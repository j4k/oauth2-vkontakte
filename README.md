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
$provider = new Aego\OAuth2\Client\Provider\Vkontakte([
    'clientId'  =>  'b80bb7740288fda1f201890375a60c8f',
    'clientSecret'  =>  'f23ccd066f8236c6f97a2a62d3f9f9f5',
    'redirectUri' => 'https://example.org/oauth-endpoint',
]);
```
