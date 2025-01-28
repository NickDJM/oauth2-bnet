<?php

namespace Nickdjm\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use Nickdjm\OAuth2\Client\Provider\Exception\BnetIdentityProviderException;

class Bnet extends AbstractProvider
{
    use BearerAuthorizationTrait;

  /**
   * The Battle.net region to query against.
   *
   * @var string
   */
    protected $region = 'us';

  /**
   * The default host.
   *
   * @var string
   */
    public $host = 'https://oauth.battle.net';

  /**
   * The API domain.
   *
   * @todo: This should be set based on the region.
   *
   * @var string
   */
    public $apiDomain = 'https://us.api.blizzard.com';

  /**
   * Get the authorization URL to begin the OAuth flow.
   *
   * @return string
   *   The authorization URL.
   */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->host . '/authorize';
    }

  /**
   * Get the access token URL to retrieve the token.
   *
   * @param array $params
   *   The parameters.
   * @return string
   *   The access token URL.
   */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->host . '/token';
    }

  /**
   * Get the provider URL to retrieve user details.
   *
   * @param AccessToken $token
   *   The access token.
   * @return string
   *   The provider URL.
   */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->host . '/userinfo';
    }


  /**
   * Get the default scopes used by this provider.
   *
   * This should not be a complete list of all scopes, but the minimum
   * required for the provider.
   *
   * @return array
   *   The default scopes.
   */
    protected function getDefaultScopes(): array
    {
        return ['openid'];
    }

  /**
   * Check a provider response for errors.
   *
   * @param ResponseInterface $response
   *   The response.
   * @param array|string $data
   *   The parsed response data.
   *
   * @throws IdentityProviderException
   *
   * @return void
   */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw BnetIdentityProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw BnetIdentityProviderException::oauthException($response, $data);
        }
    }

  /**
   * Generate a user object from a successful user details request.
   *
   * @param array $response
   *   The response.
   * @param AccessToken $token
   *   The access token.
   *
   * @return ResourceOwnerInterface
   *   The resource owner.
   */
    protected function createResourceOwner(array $response, AccessToken $token): BnetResourceOwner
    {
        return new BnetResourceOwner($response);
    }
}
