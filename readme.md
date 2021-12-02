# LarapackRiskified

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require adscomltd/larapack_riskified
```

## Setup

1. Publish config files

```bash
php artisan vendor:publish --provider="Adscom\LarapackRiskified\LarapackRiskifiedServiceProvider" --tag=config
```

2. Publish database migrations

```bash
php artisan vendor:publish --provider="Adscom\LarapackRiskified\LarapackRiskifiedServiceProvider" 
--tag=migrations
```

## Usage

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email aamatevosyan@edu.hse.ru instead of using the issue tracker.

## Credits

- [Armen Matevosyan][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/adscomltd/larapack_riskified.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/adscomltd/larapack_riskified.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/adscomltd/larapack_riskified/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/adscomltd/larapack_riskified
[link-downloads]: https://packagist.org/packages/adscomltd/larapack_riskified
[link-travis]: https://travis-ci.org/adscomltd/larapack_riskified
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/adscom
[link-contributors]: ../../contributors
