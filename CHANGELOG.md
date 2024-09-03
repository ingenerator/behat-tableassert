# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased

## 1.3.1 / 2024-09-03

* Support PHP 8.3

## 1.3.0 / 2023-08-09

* Support PHP 8.2
* Drop support for PHP 7.4

## 1.2.5 / 2022-09-16

* Switch to masterminds/html5 for HTML parsing to fix failures when the table contains
  html5 elements e.g. `<time>` - thanks @RoSko

## 1.2.4 / 2022-08-30

* Mark that PHP 8.1.x is also supported - thanks @mxr576

## 1.2.3 / 2021-04-21

* Only support PHP8.0.x and ensure we test against the lowest dependencies too

## 1.2.2 / 2021-02-02

* Officially support php8, drop support for php <7.4 - Thanks @jorissteyn #9 :)
* Switch CI to github actions

## 1.2.1 / 2020-06-07

* Remove development files from distribution packages (@simonschaufi #8)

## 1.2.0 / 2019-04-03

* Ensure compatibility with php7.2

* Drop support for php5

* [FEATURE] Extract parsing html cell text to a standalone method for easier
  customisation of the parsed text value in extension parsers.

## 1.1.1 / 2016-09-05

* [BUGFIX]  Don't fail when HTML contains valid unclosed tags eg <input>
  by parsing as HTML rather than XML. This unfortunately does mean the
  HTML parser is a lot more tolerant than it was and will usually not
  detect invalid HTML markup within the tables.

## 1.1.0 / 2015-10-15

* [FEATURE] Parse multiple colspan columns in HTML tables as `...`
* [FEATURE] Support skipping over presentational rows in HTML tables with data attribute
* [FEATURE] Optionally prefix HTML table cell values with data attribute

## 1.0.0 / 2015-10-09

* [FEATURE] First release - all the features!
