# PHP Value Objects

[![master branch build status](https://api.travis-ci.org/gws/php-valueobjects.png?branch=master)](http://travis-ci.org/gws/php-valueobjects)

## Overview

[Wikipedia](https://en.wikipedia.org/wiki/Value_object) has a pretty good
general explanation of value objects.

- `Vo\DateRange` for date ranges
- `Vo\DateTimeRange` for date and time ranges
- `Vo\Ip` for IPv4 and IPv6 addresses
- `Vo\Mac` for MAC addresses
- `Vo\Money` for financial math and formatting using the `intl` extension

Check the tests to get some ideas on how to use these classes.

## Documentation

http://gws.github.com/php-valueobjects

## Development

There is a `Dockerfile` in the `docker` directory which can be used to build a
version of PHP with the required extensions in order to run tests. If you have a
Dockerized `composer`, you need to pass the `--ignore-platform-reqs` option to
`update`, `install` etc. in order to skip the extension checks.
