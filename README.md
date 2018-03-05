# filter-strings-php

A filtering implementation for verifying correct data and performing typical modifications to data.

[![Build Status](https://travis-ci.org/traderinteractive/filter-strings-php.svg?branch=master)](https://travis-ci.org/traderinteractive/filter-strings-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/traderinteractive/filter-strings-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/traderinteractive/filter-strings-php/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/traderinteractive/filter-strings-php/badge.svg?branch=master)](https://coveralls.io/github/traderinteractive/filter-strings-php?branch=master)

[![Latest Stable Version](https://poser.pugx.org/traderinteractive/filter-strings/v/stable)](https://packagist.org/packages/traderinteractive/filter-strings)
[![Latest Unstable Version](https://poser.pugx.org/traderinteractive/filter-strings/v/unstable)](https://packagist.org/packages/traderinteractive/filter-strings)
[![License](https://poser.pugx.org/traderinteractive/filter-strings/license)](https://packagist.org/packages/traderinteractive/filter-strings)

[![Total Downloads](https://poser.pugx.org/traderinteractive/filter-strings/downloads)](https://packagist.org/packages/traderinteractive/filter-strings)
[![Daily Downloads](https://poser.pugx.org/traderinteractive/filter-strings/d/daily)](https://packagist.org/packages/traderinteractive/filter-strings)
[![Monthly Downloads](https://poser.pugx.org/traderinteractive/filter-strings/d/monthly)](https://packagist.org/packages/traderinteractive/filter-strings)

## Features

TO DO

## Example

TO DO

## Composer

To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`traderinteractive/filter-strings` to your project's `composer.json` file such as:

```sh
composer require traderinteractive/filter-strings
```

## Documentation

TO DO

#### Specification

TODO

#### Strings::filter
Aliased in the filterer as `string`, this filter verifies that the argument is a string.  The second parameter can be set to `true` to allow
null values through without an error (they will stay null and not get converted to false).  The last parameters specify the length bounds of the
string. The default bounds are 1+, so an empty string fails by default.

The following checks that `$value` is a non-empty string.
```php
\TraderInteractive\Filter\Strings::filter($value);
```

#### Strings::explode
Aliased in the filterer as `explode`, this filter is essentially a wrapper around the built-in [`explode`](http://www.php.net/explode) method
with the value first in order to work with the `Filterer`.  It also defaults to using `,` as a delimiter.  For example:
```php
$value = \TraderInteractive\Filter\Strings::explode('abc,def,ghi');
assert($value === ['abc', 'def', 'ghi']);
```

#### Url::filter
Aliased in the filterer as `url`, this filter verifies that the argument is a URL string according to
[RFC2396](http://www.faqs.org/rfcs/rfc2396). The second parameter can be set to `true` to allow
null values through without an error (they will stay null and not get converted to false).

The following checks that `$value` is a URL.
```php
\TraderInteractive\Filter\Url::filter($value);
```

#### Email::filter
Aliased in the filterer as `email`, this filter verifies that the argument is an email.

The following checks that `$value` is an email.
```php
\TraderInteractive\Filter\Email::filter($value);
```

## Contact

Developers may be contacted at:

 * [Pull Requests](https://github.com/traderinteractive/filter-strings-php/pulls)
 * [Issues](https://github.com/traderinteractive/filter-strings-php/issues)

## Project Build

With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```bash
./vendor/bin/phpcs
./vendor/bin/phpunit
```

For more information on our build process, read through out our [Contribution Guidelines](CONTRIBUTING.md).
