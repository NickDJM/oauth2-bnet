# Battle.net Provider for OAuth 2.0 Client

This package provides Battle.net OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Requirements

The following versions of PHP are supported:

- PHP 8.0
- PHP 8.1
- PHP 8.2
- PHP 8.3

## Installation

To install, use composer:

``` bash
composer require nickdjm/oauth2-bnet
```

## Limitations

Currently, this provider only supports oauth2 authorization to US region accounts.

## Usage

Usage is the same as The League's OAuth client, using `\Nickdjm\OAuth2\Client\Provider\Bnet` as the provider.
