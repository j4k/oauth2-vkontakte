<?php

namespace J4k\OAuth2\Client\Test\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Token\AccessToken;
use Mockery as m;
use \J4k\OAuth2\Client\Provider\Vkontakte as Provider;
use \J4k\OAuth2\Client\Provider\User;

class VkontakteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @type Provider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Provider([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        static::assertArrayHasKey('client_id', $query);
        static::assertArrayHasKey('redirect_uri', $query);
        static::assertArrayHasKey('state', $query);
        static::assertArrayHasKey('scope', $query);
        static::assertArrayHasKey('response_type', $query);
        static::assertArrayHasKey('approval_prompt', $query);
        static::assertNotNull($this->provider->getState());
    }

    public function testUrlAccessToken()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);

        static::assertEquals('/access_token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        /**
         * @type Client|\Mockery\Mock $client
         * @type Response|\Mockery\Mock $response
         * @type AccessToken $token
         */

        $response = m::mock(new Response);
        $response->shouldReceive('getBody')->times(1)->andReturn('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1, "email": "mock_email"}');

        $client = m::mock(new Client);
        $client->shouldReceive('setBaseUrl')->times(1);
        $client->shouldReceive('post->send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        static::assertEquals('mock_access_token', $token->getToken());
        static::assertLessThanOrEqual(time() + 3600, $token->getExpires());
        static::assertGreaterThanOrEqual(time(), $token->getExpires());
        static::assertEquals('mock_refresh_token', $token->getRefreshToken());
        static::assertEquals('1', $token->uid);
        static::assertEquals('mock_email', $token->email);
    }

    public function testScopes()
    {
        static::assertEquals(['email'], $this->provider->scopes);
    }

    public function testUserData()
    {
        /**
         * @type Client|\Mockery\Mock $client
         * @type Response|\Mockery\Mock $postResponse
         * @type Response|\Mockery\Mock $getResponse
         */

        $postResponse = m::mock(new Response);
        $postResponse->shouldReceive('getBody')->times(1)->andReturn('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "uid": 1, "email": "mock_email"}');

        $getResponse = m::mock(new Response);
        $getResponse->shouldReceive('getBody')->times(4)->andReturn('{"response": [{"uid": 12345, "nickname": "mock_nickname", "screen_name": "mock_name", "first_name": "mock_first_name", "last_name": "mock_last_name", "country": "UK", "status": "mock_status", "photo_200_orig": "mock_image_url"}]}');

        $client = m::mock(new Client);
        $client->shouldReceive('setBaseUrl')->times(5);
        $client->shouldReceive('post->send')->times(1)->andReturn($postResponse);
        $client->shouldReceive('get->send')->times(4)->andReturn($getResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        /** @type User $user */
        $user = $this->provider->getResourceOwner($token);

        static::assertEquals(12345, $this->provider->userUid($getResponse, $token));
        static::assertEquals(['mock_first_name', 'mock_last_name'], $this->provider->userScreenName($getResponse, $token));
        static::assertEquals('mock_email', $this->provider->userEmail($getResponse, $token));
        static::assertEquals('mock_email', $user->email);
    }
}
