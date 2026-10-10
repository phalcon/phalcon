# Phalcon Framework

[![Latest Version][packagist-version-badge]][packagist-version-link]
[![PHP Version][php-version-badge]][packagist-version-link]
[![Total Downloads][packagist-downloads-badge]][packagist-downloads-link]
[![License][license-badge]][license-link]

[![Phalcon CI][phalcon-ci-badge]][phalcon-ci-link]
[![Quality Gate Status][sonar-quality-badge]][sonar-link]
[![Coverage][sonar-coverage-badge]][sonar-link]
[![PDS Skeleton][pds-skeleton-badge]][pds-skeleton-link]

[![Discord][discord-badge]][discord-link]
[![Contributors][contributors-badge]][contributors-link]
[![OpenCollective Backers][oc-backers-badge]][backers-link]
[![OpenCollective Sponsors][oc-sponsors-badge]][sponsors-link]

Phalcon is an open-source, full-stack web framework for PHP, focused on high
performance, low overhead and a clean, expressive API.

> [!IMPORTANT]
> This repository is the **pure PHP** implementation of Phalcon (v6). Unlike
> [cphalcon](https://github.com/phalcon/cphalcon), it is **not** a C extension:
> there is nothing to compile and no PECL/PIE installation required - just add it
> to your project with Composer. Phalcon v6 is currently in **alpha**; APIs may
> change before the stable release.

A big thank you to [our Backers](https://opencollective.com/phalcon#backer); you rock!

## Getting Started

Phalcon is written in plain PHP with portability in mind, so it runs anywhere a
supported PHP runtime is available - GNU/Linux, FreeBSD, macOS and Microsoft
Windows.

## Requirements

* PHP `>= 8.1 < 9.0`
* `ext-fileinfo`, `ext-json`, `ext-mbstring`, `ext-pdo`, `ext-xml`

Optional extensions enable additional adapters and features:

| Extension       | Used by                                                                                      |
|-----------------|----------------------------------------------------------------------------------------------|
| `ext-apcu`      | `Cache\Adapter\Apcu`, `Storage\Adapter\Apcu`                                                 |
| `ext-gd`        | `Image\Adapter\Gd`                                                                           |
| `ext-igbinary`  | `Storage\Serializer\Igbinary`                                                                |
| `ext-imagick`   | `Image\Adapter\Imagick`                                                                      |
| `ext-memcached` | `Cache\Adapter\Libmemcached`, `Session\Adapter\Libmemcached`, `Storage\Adapter\Libmemcached` |
| `ext-openssl`   | `Encryption\Crypt`                                                                           |
| `ext-pcntl`     | `Queue\Consumer\Worker`                                                                      |
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

## Sponsors and backers

These sponsors and backers support Phalcon and Zephir. To join them, become a sponsor or a backer on [Open Collective](https://opencollective.com/phalcon) or [GitHub Sponsors](https://phalcon.io/fund).

<!-- The roster below is generated every day by the "Update backers" workflow from the phalcon/assets roster. Do not edit it by hand. -->
<!-- backers:start -->

### Sponsors

<a href="https://opencollective.com/commercesuite"><img src="https://images.opencollective.com/commercesuite/5c683c0/logo.png" alt="Commercesuite" title="Commercesuite" height="40"></a>
<a href="https://github.com/markofo"><img src="https://avatars.githubusercontent.com/u/59839390?v=4" alt="markofo" title="markofo" height="40"></a>

### Partners

<a href="https://abits.com"><img src="https://assets.phalcon.io/phalcon/images/backers/abits-100x34.svg" alt="Abits" title="Abits" height="40"></a>
<a href="https://www.cloudflare.com/"><img src="https://assets.phalcon.io/phalcon/images/backers/cloudflare.svg" alt="Cloudflare" title="Cloudflare" height="40"></a>
<a href="https://crowdin.com/"><img src="https://assets.phalcon.io/phalcon/images/backers/crowdin.png" alt="Crowdin" title="Crowdin" height="40"></a>
<a href="https://www.digitalocean.com/"><img src="https://assets.phalcon.io/phalcon/images/backers/digitalocean.svg" alt="DigitalOcean" title="DigitalOcean" height="40"></a>
<a href="https://mctekk.com"><img src="https://assets.phalcon.io/phalcon/images/backers/mctekk-149x34.svg" alt="mctekk" title="mctekk" height="40"></a>
<a href="https://odva.pro/"><img src="https://assets.phalcon.io/phalcon/images/backers/odva.svg" alt="odva" title="odva" height="40"></a>

### Supporters

<a href="https://github.com/elstin"><img src="https://avatars.githubusercontent.com/u/38716832?u=d219979f0233713ca897b1ea3dfaae57144d3a77&amp;v=4" alt="Akira Kato" title="Akira Kato" width="60" height="60"></a>
<a href="https://github.com/alrieckert"><img src="https://avatars.githubusercontent.com/u/452786?v=4" alt="Anton Rieckert" title="Anton Rieckert" width="60" height="60"></a>
<a href="https://opencollective.com/barry"><img src="https://images.opencollective.com/barry/avatar.png" alt="Barry Helfrich" title="Barry Helfrich" width="60" height="60"></a>
<a href="https://bd.fyi"><img src="https://images.opencollective.com/borisdelev/7630f7b/avatar.png" alt="Boris Delev" title="Boris Delev" width="60" height="60"></a>
<a href="https://github.com/fvromera"><img src="https://avatars.githubusercontent.com/u/32909196?u=a4a6d765c836be52ab247354399d0ed1a49224fa&amp;v=4" alt="Chess" title="Chess" width="60" height="60"></a>
<a href="https://opencollective.com/guest-163314bf"><img src="https://images.opencollective.com/guest-163314bf/avatar.png" alt="D3" title="D3" width="60" height="60"></a>
<a href="https://github.com/f-do"><img src="https://avatars.githubusercontent.com/u/4299065?u=66d3687ffd970119b19d39694a5ddf87294d0bd5&amp;v=4" alt="Florian" title="Florian" width="60" height="60"></a>
<a href="https://github.com/francoisgrogor"><img src="https://avatars.githubusercontent.com/u/5804565?v=4" alt="francoisgrogor" title="francoisgrogor" width="60" height="60"></a>
<a href="https://github.com/housesigma"><img src="https://avatars.githubusercontent.com/u/50630040?v=4" alt="HouseSigma" title="HouseSigma" width="60" height="60"></a>
<a href="https://www.ultimater.net"><img src="https://images.opencollective.com/ultimater/19fc150/avatar.png" alt="Kevin Yarmak" title="Kevin Yarmak" width="60" height="60"></a>
<a href="https://opencollective.com/info23"><img src="https://images.opencollective.com/info23/eebb146/avatar.png" alt="maGus Informática" title="maGus Informática" width="60" height="60"></a>
<a href="https://github.com/niden"><img src="https://avatars.githubusercontent.com/u/1073784?v=4" alt="Nikolaos Dimopoulos" title="Nikolaos Dimopoulos" width="60" height="60"></a>
<a href="https://github.com/rayanlevert"><img src="https://avatars.githubusercontent.com/u/78140431?u=e9757b8d038f97b4e81c104378657275cc24d150&amp;v=4" alt="Rayan Levert" title="Rayan Levert" width="60" height="60"></a>

### Backers

<a href="https://github.com/elcreator"><img src="https://avatars.githubusercontent.com/u/974975?u=f1bbb9b9c676bc2141bb68fed8f0533a26322632&amp;v=4" alt="Artur Kyryliuk" title="Artur Kyryliuk" width="60" height="60"></a>
<a href="https://github.com/raicabogdan"><img src="https://avatars.githubusercontent.com/u/4399340?v=4" alt="Bogdan Raica" title="Bogdan Raica" width="60" height="60"></a>
<a href="https://opencollective.com/christian-jay-bayno"><img src="https://images.opencollective.com/christian-jay-bayno/c6aab1d/avatar.png" alt="Ceage10" title="Ceage10" width="60" height="60"></a>
<a href="https://github.com/6trading"><img src="https://avatars.githubusercontent.com/u/12135941?u=befb955111226257bb44aec408af3229904d2831&amp;v=4" alt="Chris" title="Chris" width="60" height="60"></a>
<a href="https://github.com/iogates"><img src="https://avatars.githubusercontent.com/u/86652317?v=4" alt="ioGates" title="ioGates" width="60" height="60"></a>
<a href="https://aircode.pl/"><img src="https://images.opencollective.com/aircode/3e9590d/avatar.png" alt="Mateusz Pająk / Aircode" title="Mateusz Pająk / Aircode" width="60" height="60"></a>
<a href="https://github.com/dredasss"><img src="https://avatars.githubusercontent.com/u/38747389?u=ee99a8bb28ee6bedbbea6325d49d4eb99080d421&amp;v=4" alt="Nerijus Alex" title="Nerijus Alex" width="60" height="60"></a>
<a href="https://github.com/tztztztz"><img src="https://avatars.githubusercontent.com/u/7032308?v=4" alt="Tomasz Zadora" title="Tomasz Zadora" width="60" height="60"></a>

<!-- backers:end -->

## Core Team

[Anton](https://github.com/Jeckerson), [Nikolaos](https://github.com/niden)

![Repobeats analytics image](https://repobeats.axiom.co/api/embed/2d73e3d230f4a39aa8e144feb6083f1d2c38faec.svg "Repobeats analytics image")

## Links

### General
* [Contributing to Phalcon](CONTRIBUTING.md)
* [Official Documentation](https://docs.phalcon.io/)
* [Incubator](https://phalcon.io/incubator) - community-driven plugins and classes that extend the framework

### Support
* [Discussions](https://phalcon.io/discussions)
* [Discord](https://phalcon.io/discord)
* [Stack Overflow](https://phalcon.io/so)

### Social Media
* [Telegram](https://phalcon.io/telegram)
* [LinkedIn](https://phalcon.io/linkedin)
* [Facebook](https://phalcon.io/fb)
* [Twitter](https://phalcon.io/t)

## License

Phalcon is open-source software licensed under the MIT License.

Copyright © 2020-present, The Phalcon PHP Framework.

See the [LICENSE](LICENSE) file or [license.phalcon.io](https://license.phalcon.io) for details.

<!-- Badges: package -->
[packagist-version-badge]:   https://img.shields.io/packagist/v/phalcon/phalcon?include_prereleases&style=flat-square
[packagist-version-link]:    https://packagist.org/packages/phalcon/phalcon
[packagist-downloads-badge]: https://img.shields.io/packagist/dt/phalcon/phalcon?style=flat-square
[packagist-downloads-link]:  https://packagist.org/packages/phalcon/phalcon/stats
[php-version-badge]:          https://img.shields.io/packagist/php-v/phalcon/phalcon?style=flat-square
[license-badge]:             https://img.shields.io/github/license/phalcon/phalcon?style=flat-square
[license-link]:              LICENSE

<!-- Badges: quality & build -->
[phalcon-ci-badge]:          https://github.com/phalcon/phalcon/actions/workflows/main.yml/badge.svg?branch=v6.0.x
[phalcon-ci-link]:           https://github.com/phalcon/phalcon/actions/workflows/main.yml
[sonar-quality-badge]:       https://sonarcloud.io/api/project_badges/measure?project=phalcon_phalcon&metric=alert_status
[sonar-coverage-badge]:      https://sonarcloud.io/api/project_badges/measure?project=phalcon_phalcon&metric=coverage
[sonar-link]:                https://sonarcloud.io/summary/new_code?id=phalcon_phalcon
[pds-skeleton-badge]:        https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square
[pds-skeleton-link]:         https://github.com/php-pds/skeleton

<!-- Badges: community -->
[discord-badge]:             https://img.shields.io/discord/310910488152375297?label=Discord&logo=discord&style=flat-square
[discord-link]:              https://phalcon.io/discord
[contributors-badge]:        https://img.shields.io/github/contributors/phalcon/phalcon?style=flat-square
[contributors-link]:         https://github.com/phalcon/phalcon/graphs/contributors
[oc-backers-badge]:          https://img.shields.io/opencollective/backers/phalcon?style=flat-square
[oc-sponsors-badge]:         https://img.shields.io/opencollective/sponsors/phalcon?style=flat-square
[backers-link]:              #backers
[sponsors-link]:             #sponsors