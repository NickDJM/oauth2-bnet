<?php

namespace Nickdjm\OAuth2\Client\Test\Provider;

use Nickdjm\OAuth2\Client\Provider\Exception\BnetIdentityProviderException;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Mockery as m;
use PHPUnit\Framework\TestCase;

use function http_build_query;
use function json_encode;
use function uniqid;

class BnetTest extends TestCase
{
    use QueryBuilderTrait;

    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new \Nickdjm\OAuth2\Client\Provider\Bnet(
            [
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            ]
        );
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }


    public function testScopes(): void
    {
        $scopeSeparator = ',';
        $options = ['scope' => [uniqid(), uniqid()]];
        $query = ['scope' => implode($scopeSeparator, $options['scope'])];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = $this->buildQueryString($query);

        $this->assertStringContainsString($encodedScope, $url);
    }

    public function testGetAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl(): void
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/token', $uri['path']);
    }

    public function testGetAccessToken(): void
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')
                  ->andReturn($this->createStream(
                      '{"access_token":"mock_access_token", "scope":"openid", "token_type":"bearer"}'
                  ));
        $response->shouldReceive('getHeader')
                 ->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')
                 ->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData(): void
    {
        $subject = rand(1000, 9999);
        $userId = rand(1000, 9999);
        $battleTag = uniqid();

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')
                     ->andReturn($this->createStream(http_build_query([
                         'access_token' => 'mock_access_token',
                         'expires' => 3600,
                         'refresh_token' => 'mock_refresh_token',
                     ])));
        $postResponse->shouldReceive('getHeader')
                     ->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->shouldReceive('getStatusCode')
                     ->andReturn(200);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')
                     ->andReturn($this->createStream(json_encode([
                         "sub" => $subject,
                         "id" => $userId,
                         "battletag" => $battleTag,
                     ])));
        $userResponse->shouldReceive('getHeader')
                     ->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')
                     ->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($subject, $user->getSubject());
        $this->assertEquals($subject, $user->toArray()['sub']);
        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['id']);
        $this->assertEquals($battleTag, $user->getBattleTag());
        $this->assertEquals($battleTag, $user->toArray()['battletag']);
    }

    public function testExceptionThrownWhenErrorObjectReceived(): void
    {
        $status = rand(400, 600);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')
                     ->andReturn($this->createStream(json_encode([
                         'message' => 'Validation Failed',
                         'errors' => [
                             ['resource' => 'Issue', 'field' => 'title', 'code' => 'missing_field'],
                         ],
                     ])));
        $postResponse->shouldReceive('getHeader')
                     ->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')
                     ->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $this->expectException(BnetIdentityProviderException::class);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testExceptionThrownWhenOAuthErrorReceived(): void
    {
        $status = 200;
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')
                     ->andReturn($this->createStream(json_encode([
                         "error" => "bad_verification_code",
                         "error_description" => "The code passed is incorrect or expired.",
                         "error_uri" => "https =>//oauth.battle.net#bad-verification-code",
                     ])));
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $this->expectException(BnetIdentityProviderException::class);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    private function createStream(string $body): \Psr\Http\Message\StreamInterface
    {
        $stream = m::mock('Psr\Http\Message\StreamInterface');
        $stream->shouldReceive('__toString')
            ->andReturn($body);

        return $stream;
    }
}
