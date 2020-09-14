# filter-strings-php

[![Build Status](https://travis-ci.org/traderinteractive/filter-strings-php.svg?branch=master)](https://travis-ci.org/traderinteractive/filter-strings-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/traderinteractive/filter-strings-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/traderinteractive/filter-strings-php/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/traderinteractive/filter-strings-php/badge.svg?branch=master)](https://coveralls.io/github/traderinteractive/filter-strings-php?branch=master)

[![Latest Stable Version](https://poser.pugx.org/traderinteractive/filter-strings/v/stable)](https://packagist.org/packages/traderinteractive/filter-strings)
[![Latest Unstable Version](https://poser.pugx.org/traderinteractive/filter-strings/v/unstable)](https://packagist.org/packages/traderinteractive/filter-strings)
[![License](https://poser.pugx.org/traderinteractive/filter-strings/license)](https://packagist.org/packages/traderinteractive/filter-strings)

[![Total Downloads](https://poser.pugx.org/traderinteractive/filter-strings/downloads)](https://packagist.org/packages/traderinteractive/filter-strings)
[![Daily Downloads](https://poser.pugx.org/traderinteractive/filter-strings/d/daily)](https://packagist.org/packages/traderinteractive/filter-strings)
[![Monthly Downloads](https://poser.pugx.org/traderinteractive/filter-strings/d/monthly)](https://packagist.org/packages/traderinteractive/filter-strings)

A filtering implementation for verifying the contents of strings and some common formats of strings.

## Requirements

Requires PHP 7.0 or newer and uses composer to install further PHP dependencies.  See the [composer specification](composer.json) for more details.

## Installation

filter-strings-php can be installed for use in your project using [composer](http://getcomposer.org).
The recommended way of using this library in your project is to add a `composer.json` file to your project.  The following contents would add filter-strings-php as a dependency:

```sh
composer require traderinteractive/filter-strings
```

### Functionality

#### Strings::filter

This filter verifies that the argument is a string.  The second parameter can be set to `true` to allow
null values through without an error (they will stay null and not get converted to false).  The last parameters specify the length bounds of the
string. The default bounds are 1+, so an empty string fails by default.

The following checks that `$value` is a non-empty string.

```php
\TraderInteractive\Filter\Strings::filter($value);
```

#### Strings::concat
This filter concatenates the given $value, $prefix and $suffix and returns the resulting string.
```php
$value = \TraderInteractive\Filter\Strings::concat('middle', 'begining_', '_end');
assert($value === 'begining_middle_end');
```
#### Strings::translate
This filter will accept a string value and return its translated value found in the given $valueMap.
```php
$value = \TraderInteractive\Filter\Strings::translate('active', ['inactive' => 'X', 'active' => 'A']);
assert($value === 'A');
```

#### Strings::explode

This filter is essentially a wrapper around the built-in [`explode`](http://www.php.net/explode) method
with the value first in order to work with the `Filterer`.  It also defaults to using `,` as a delimiter.  For example:

```php
$value = \TraderInteractive\Filter\Strings::explode('abc,def,ghi');
assert($value === ['abc', 'def', 'ghi']);
```
This filter can also be given padding settings to ensure the result has a specific number of elements. For example:
```php
$value = Strings::explode('a-b-c', '-', 5, '', Strings::EXPLODE_PAD_LEFT);
assert($value === [null, null, 'a', 'b', 'c']);
```
#### Strings::compress
This filter trims and remove superfluous whitespace from a given string.
```php
$value = \TraderInteractive\Filter\Strings::compress(' a string    with lots of    whitespace   ');
assert($value === 'a string with lots of whitespace');
```
This filter can also replace vertical whitespace such as newlines with single spaces.
```php
$value = \TraderInteractive\Filter\Strings::compress(" a string\nwith lots\nof    \nnewlines\n   ", true);
assert($value === 'a string with lots of newlines');
```

#### Strings::redact

This filter will remove specified words from a string or, optionally, replace each letter of the words with a replacement character.

The second argument specifies the words that should be replaced and can either be an array of strings or a callable that returns an array of strings.

The third argument specifies the replacement character. If empty, the words will be removed entirely. If a string with more than one character is provided, only the first character will be used.

```php
$value = \TraderInteractive\Filter\Strings::redact('a string with some unwanted words', ['unwanted', 'words'], '*');
assert($value === 'a string with some ******** *****');
```

#### Strings::stripTags

This filter will strip HTML, XML, and PHP tags from a string. This filter also accepts null values, which will be returned as null.

The second, optional argument specifies a replacement string for the removed HTML and XML tags. PHP tags will be stripped without a replacement.

```php
\TraderInteractive\Filter\Strings::stripTags('<div>a string with<br/>tags</div>', ' ');
assert($value === ' a string with tags ');
```

#### Url::filter

This filter verifies that the argument is a URL string according to
[RFC2396](http://www.faqs.org/rfcs/rfc2396). The second parameter can be set to `true` to allow
null values through without an error (they will stay null and not get converted to false).

The following checks that `$value` is a URL.

```php
\TraderInteractive\Filter\Url::filter($value);
```

#### Email::filter

This filter verifies that the argument is an email.

The following checks that `$value` is an email.

```php
\TraderInteractive\Filter\Email::filter($value);
```

#### Json::validate

This filter verifies that the value is in a valid JSON format.

The second parameter can be set to `true` to allow null values through without an error.

The third parameter determines the maximum recursion depth that is allowed.

The following checks that `$value` is a valid JSON string.

```php
\TraderInteractive\Filter\Json::validate($value);
```

#### Json::parse

This filter parses a valid JSON string into an array, int, double, or bool. Invalid JSON will throw an error.

The second parameter can be set to `true` to allow null values through without an error.

The third parameter determines the maximum recursion depth that is allowed.

The following checks that `$value` is a valid JSON string and parses it into an array.

```php
$value = '{ "string": "value", "array": [1, 2, 3] }';
\TraderInteractive\Filter\Json::parse($value);
assert($value === ['string' => 'value', 'array' => [1, 2, 3]]);
```

#### XmlFilter::filter

This filter ensures the given string is valid XML.

```php
$value = "<root><outer><inner>value</inner></outer></root>";
$filtered = \TraderInteractive\Filter\XmlFilter::filter($value);
assert($value === $filtered);
```

#### XmlFilter::extract

This filter accepts an XML string and an xpath. It will return the single element found at the xpath.

```php
$value = <<<XML
<?xml version="1.0"?>
<books> 
    <book id="bk101">
        <author>Gambardella, Matthew</author>
        <title>XML Developers Guide</title>
        <genre>Computer</genre>
        <price>44.95</price>
        <publish_date>2000-10-01</publish_date>
        <description>An in-depth look at creating applications with XML.</description>
    </book>
    <book id="bk102">
        <author>Ralls, Kim</author>
        <title>Midnight Rain</title>
        <genre>Fantasy</genre>
        <price>5.95</price>
        <publish_date>2000-12-16</publish_date>
        <description>A former architect battles corporate zombies</description>
    </book>
</books>
XML;
$xpath = '//book[@id="bk102"]';

$filtered = \TraderInteractive\Filter\XmlFilter::extract($value, $xpath);
$expected = <<<XML
<book id="bk102">
    <author>Ralls, Kim</author>
    <title>Midnight Rain</title>
    <genre>Fantasy</genre>
    <price>5.95</price>
    <publish_date>2000-12-16</publish_date>
    <description>A former architect battles corporate zombies</description>
</book>
XML;
assert($filtered === $expected);
```

#### XmlFilter::validate

This filter accepts an XML string and a filepath to an XSD. It ensures the given XML is valid using the given XSD and returns the original XML.

```php
$value = <<<XML
<?xml version="1.0"?>
<books> 
    <book id="bk101">
        <author>Gambardella, Matthew</author>
        <title>XML Developers Guide</title>
        <genre>Computer</genre>
        <price>44.95</price>
        <publish_date>2000-10-01</publish_date>
        <description>An in-depth look at creating applications with XML.</description>
    </book>
    <book id="bk102">
        <author>Ralls, Kim</author>
        <title>Midnight Rain</title>
        <genre>Fantasy</genre>
        <price>5.95</price>
        <publish_date>2000-12-16</publish_date>
        <description>A former architect battles corporate zombies</description>
    </book>
</books>
XML;
$xsdFilePath = 'books.xsd';
$filtered = \TraderInteractive\Filter\XmlFilter::validate($value, $xsdFilePath);
assert($filtered === $value);
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
