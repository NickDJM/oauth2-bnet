<?php

namespace Nickdjm\OAuth2\Client\Provider\Exception;

use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class BnetIdentityProviderException extends IdentityProviderException
{
  /**
   * Creates a client exception from the response.
   *
   * @param ResponseInterface $response
   *   The response.
   * @param [type] $data
   *   The parsed response data.
   * @return IdentityProviderException
   *   The identity provider exception.
   */
    public static function clientException(ResponseInterface $response, $data)
    {
        return static::fromResponse($response, $data['message'] ?? json_encode($data));
    }

    /**
     * Creates oauth exception from response.
     *
     * @param ResponseInterface $response
     *   The response.
     * @param array $data
     *   The Parsed response data.
     *
     * @return IdentityProviderException
     *   The identity provider exception.
     */
    public static function oauthException(ResponseInterface $response, array $data): IdentityProviderException
    {
        return static::fromResponse(
            $response,
            isset($data['error']) ? $data['error'] : $response->getReasonPhrase()
        );
    }

  /**
   * Creates an identity exception from the response.
   *
   * @param ResponseInterface $response
   *   The response.
   * @param string $message
   *   The message.
   * @return IdentityProviderException
   *   The identity provider exception.
   */
    protected static function fromResponse(ResponseInterface $response, $message = null)
    {
        return new static($message, $response->getStatusCode(), (string) $response->getBody());
    }
}
