<?php

namespace Nickdjm\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class BnetResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

  /**
   * The raw response
   *
   * @var array
   */
    protected $response;

  /**
   * Creates a new resource owner.
   *
   * @param array $response
   *   The response.
   */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

  /**
   * Get the resource owner subject.
   *
   * @return string|null
   *   The resource owner subject.
   */
    public function getSubject(): string
    {
        return $this->getValueByKey($this->response, 'sub');
    }

  /**
   * Get the resource owner ID.
   *
   * @return string|null
   *   The resource owner ID.
   */
    public function getId(): string
    {
        return $this->getValueByKey($this->response, 'id');
    }

  /**
   * Get the resource owner battle tag.
   *
   * @return string|null
   *   The resource owner battle tag.
   */
    public function getBattleTag(): string
    {
        return $this->getValueByKey($this->response, 'battletag');
    }

  /**
   * Returns the raw resource owner response.
   *
   * @return array
   */
    public function toArray(): array
    {
        return $this->response;
    }
}
