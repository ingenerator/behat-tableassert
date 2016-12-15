# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased

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
