<?php

namespace Nickdjm\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class BnetResourceOwner implements ResourceOwnerInterface {
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
  public function __construct(array $response = []) {
    $this->response = $response;
  }

  /**
   * Get the resource owner ID.
   *
   * @return string|null
   *   The resource owner ID.
   */
  public function getId() {
    return $this->getValueByKey($this->response, 'id');
  }

  /**
   * Get the resource owner battle tag.
   *
   * @return string|null
   *   The resource owner battle tag.
   */
  public function getBattleTag() {
    return $this->getValueByKey($this->response, 'battletag');
  }

  /**
   * Get the resource owner email.
   *
   * @return string|null
   *   The resource owner email.
   */
  public function getEmail() {
    return $this->getValueByKey($this->response, 'email');
  }

  /**
   * Get the resource owner name.
   *
   * @return string|null
   *   The resource owner name.
   */
  public function getName() {
    return $this->getValueByKey($this->response, 'name');
  }

  /**
   * Returns the raw resource owner response.
   *
   * @return array
   */
  public function toArray() {
    return $this->response;
  }
}
