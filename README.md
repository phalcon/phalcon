# Phalcon Framework

[![Phalcon CI][phalcon-ci-badge]][phalcon-ci-link]
[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square)](https://github.com/php-pds/skeleton)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=phalcon_phalcon&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=phalcon_phalcon)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=phalcon_phalcon&metric=coverage)](https://sonarcloud.io/summary/new_code?id=phalcon_phalcon)

[![Discord][discord-badge]](https://phalcon.io/discord)
[![Contributors][contributors-badge]](https://github.com/phalcon/phalcon/graphs/contributors)
[![OpenCollective][oc-backers-badge]](#backers)
[![OpenCollective][oc-sponsors-badge]](#sponsors)

Phalcon is an open source, full-stack web framework for PHP, focused on high
performance, low overhead and a clean, expressive API.

> [!IMPORTANT]
> This repository is the **pure PHP** implementation of Phalcon (v6). Unlike
> [cphalcon](https://github.com/phalcon/cphalcon), it is **not** a C extension:
> there is nothing to compile and no PECL/PIE installation required — just add it
> to your project with Composer. Phalcon v6 is currently in **alpha**; APIs may
> change before the stable release.

A big thank you to [our Backers](https://opencollective.com/phalcon#backer); you rock!

## Getting Started

Phalcon is written in plain PHP with portability in mind, so it runs anywhere a
supported PHP runtime is available — GNU/Linux, FreeBSD, macOS and Microsoft
Windows.

## Requirements

* PHP `>= 8.1 < 9.0`
* `ext-fileinfo`, `ext-json`, `ext-mbstring`, `ext-pdo`, `ext-xml`

Optional extensions enable additional adapters:

| Extension       | Used by                                                                                      |
|-----------------|----------------------------------------------------------------------------------------------|
| `ext-apcu`      | `Cache\Adapter\Apcu`, `Storage\Adapter\Apcu`                                                 |
| `ext-gd`        | `Image\Adapter\Gd`                                                                           |
| `ext-igbinary`  | `Storage\Serializer\Igbinary`                                                                |
| `ext-imagick`   | `Image\Adapter\Imagick`                                                                      |
| `ext-memcached` | `Cache\Adapter\Libmemcached`, `Session\Adapter\Libmemcached`, `Storage\Adapter\Libmemcached` |
| `ext-openssl`   | `Encryption\Crypt`                                                                           |
| `ext-redis`     | `Cache\Adapter\Redis`, `Session\Adapter\Redis`, `Storage\Adapter\Redis`                      |
| `ext-yaml`      | `Config\Adapter\Yaml`                                                                        |

## Installation

Install the framework with [Composer](https://getcomposer.org/):

```bash
composer require phalcon/phalcon
```

While v6 is in alpha you may need to allow pre-release versions:

```bash
composer require phalcon/phalcon:^6.0@alpha
```

For detailed instructions see the [installation](https://docs.phalcon.io/6.0/installation/)
page in the docs.

## Generating API Documentation

API documentation for the docs repository can be generated with the script in
`bin/generate-api-docs.php`:

- Clone the phalcon repository.
- Check out the tag you would like to generate docs for.
- Run `php bin/generate-api-docs.php`.
- The generated `*.md` files contain the documentation, ready for publishing to
  the Phalcon [docs](https://github.com/phalcon/docs) repository.

## Testing

Tests run with [PHPUnit](https://phpunit.de/):

```bash
composer test-unit          # unit tests
composer test-db-mysql      # MySQL database tests
composer test-db-pgsql      # PostgreSQL database tests
composer test-db-sqlite     # SQLite database tests
composer test-all           # everything
```

Static analysis and coding standards:

```bash
composer analyze            # PHPStan
composer cs                 # PHP_CodeSniffer (PSR-12)
```

## Links

### General
* [Contributing to Phalcon](CONTRIBUTING.md)
* [Official Documentation](https://docs.phalcon.io/)
* [Incubator](https://phalcon.io/incubator) - Community driven plugins and classes extending the framework

### Support
* [Discussions](https://phalcon.io/discussions)
* [Discord](https://phalcon.io/discord)
* [Stack Overflow](https://phalcon.io/so)

### Social Media
* [Telegram](https://phalcon.io/telegram)
* [LinkedIn](https://phalcon.io/linkedin)
* [Facebook](https://phalcon.io/fb)
* [Twitter](https://phalcon.io/t)

## Sponsors

Become a sponsor and get your logo on our README on GitHub with a link to your site. [[Become a sponsor](https://opencollective.com/phalcon#sponsor)]

<a href="https://opencollective.com/phalcon/#contributors">
<img src="https://opencollective.com/phalcon/tiers/sponsors.svg?avatarHeight=48&width=800" alt="OpenCollective Contributors">
</a>

## Backers

Support us with a monthly donation and help us continue our activities. [[Become a backer](https://opencollective.com/phalcon#backer)]

<a href="https://opencollective.com/phalcon/#contributors">
<img src="https://opencollective.com/phalcon/tiers/backers.svg?avatarHeight=48&width=800&height=200" alt="OpenCollective Contributors">
</a>

![Alt](https://repobeats.axiom.co/api/embed/2d73e3d230f4a39aa8e144feb6083f1d2c38faec.svg "Repobeats analytics image")

## License

Phalcon is open source software licensed under the MIT License.

Copyright © 2020-present, The Phalcon PHP Framework.

See the [LICENSE](LICENSE) file for more.

<!-- External links should be here -->
[phalcon-ci-badge]:    https://github.com/phalcon/phalcon/actions/workflows/main.yml/badge.svg?branch=v6.0.x
[phalcon-ci-link]:     https://github.com/phalcon/phalcon/actions/workflows/main.yml

[discord-badge]:       https://img.shields.io/discord/310910488152375297?label=Discord&logo=discord&style=flat-square
[contributors-badge]:  https://img.shields.io/github/contributors/phalcon/phalcon?style=flat-square
[oc-backers-badge]:    https://img.shields.io/opencollective/backers/phalcon?style=flat-square
[oc-sponsors-badge]:   https://img.shields.io/opencollective/sponsors/phalcon?style=flat-square