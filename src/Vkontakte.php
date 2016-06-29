<?php

namespace J4k\OAuth2\Client\Provider;

use GuzzleHttp\Exception\BadResponseException;
use League\OAuth2\Client\Grant;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Vkontakte extends AbstractProvider
{
    public $scopes = ['email'];
    public $uidKey = 'user_id';
    public $responseType = 'json';

    public function getBaseAuthorizationUrl()
    {
        return 'https://oauth.vk.com/authorize';
    }
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://oauth.vk.com/access_token';
    }
    public function getAccessToken($grant = 'authorization_code', array $params = [])
    {
        if (is_string($grant)) {
            // PascalCase the grant. E.g: 'authorization_code' becomes 'AuthorizationCode'
            $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $grant)));
            $grant = 'League\\OAuth2\\Client\\Grant\\'.$className;
            if (! class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }
            $grant = new $grant();
        } elseif (! $grant instanceof Grant\AbstractGrant) {
            $message = get_class($grant).' is not an instance of League\OAuth2\Client\Grant\GrantInterface';
            throw new \InvalidArgumentException($message);
        }

        $defaultParams = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => $grant,
        ];

        $requestParams = $grant->prepareRequestParameters($defaultParams, $params);

        try {
            switch (strtoupper($this->getAccessTokenMethod())) {
                case 'GET':
                    // @codeCoverageIgnoreStart
                    // No providers included with this library use get but 3rd parties may
                    $client = $this->getHttpClient();
                    $getUrl = $this->getAccessTokenUrl($requestParams);
                    $request = $client->request('GET', $getUrl, $requestParams);
                    $response = $request->getBody();
                    break;
                    // @codeCoverageIgnoreEnd
                case 'POST':
                    $client = $this->getHttpClient();
                    $postUrl = $this->getAccessTokenUrl($requestParams);
                    $request = $client->request('POST', $postUrl, $requestParams);
                    $response = $request->getBody();
                    break;
                // @codeCoverageIgnoreStart
                default:
                    throw new \InvalidArgumentException('Neither GET nor POST is specified for request');
                // @codeCoverageIgnoreEnd
            }
        } catch (BadResponseException $e) {
            // @codeCoverageIgnoreStart
            $response = $e->getResponse()->getBody();
            // @codeCoverageIgnoreEnd
        }

        switch ($this->responseType) {
            case 'json':
                $result = json_decode($response, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    $result = [];
                }

                break;
            case 'string':
                parse_str($response, $result);
                break;
        }

        if (isset($result['error']) && ! empty($result['error'])) {
            // @codeCoverageIgnoreStart
            throw new IdentityProviderException($result['error_description'], $response->getStatusCode(), $responseBody);
            // @codeCoverageIgnoreEnd
        }

        $result = $this->prepareAccessTokenResponse($result);

        $accessToken = $grant->prepareRequestParameters($result, []);

        // Add email from response
        if (!empty($result['email'])) {
            $accessToken->email = $result['email'];
        }
        return $accessToken;
    }
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $fields = ['email',
            'nickname',
            'screen_name',
            'sex',
            'bdate',
            'city',
            'country',
            'timezone',
            'photo_50',
            'photo_100',
            'photo_200_orig',
            'has_mobile',
            'contacts',
            'education',
            'online',
            'counters',
            'relation',
            'last_seen',
            'status',
            'can_write_private_message',
            'can_see_all_posts',
            'can_see_audio',
            'can_post',
            'universities',
            'schools',
            'verified', ];

        return "https://api.vk.com/method/users.get?user_id={$token->uid}&fields="
            .implode(",", $fields)."&access_token={$token}";
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $response = $response->response[0];

        $user = new User();

        $email = (isset($token->email)) ? $token->email : null;
        $location = (isset($response->country)) ? $response->country : null;
        $description = (isset($response->status)) ? $response->status : null;

        $user->exchangeArray([
            'uid' => $response->uid,
            'nickname' => $response->nickname,
            'name' => $response->screen_name,
            'firstname' => $response->first_name,
            'lastname' => $response->last_name,
            'email' => $email,
            'location' => $location,
            'description' => $description,
            'imageUrl' => $response->photo_200_orig,
        ]);

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        $response = $response->response[0];

        return $response->uid;
    }
    public function userEmail($response, AccessToken $token)
    {
        return (isset($token->email)) ? $token->email : null;
    }
    public function userScreenName($response, AccessToken $token)
    {
        $response = $response->response[0];

        return [$response->first_name, $response->last_name];
    }
}
