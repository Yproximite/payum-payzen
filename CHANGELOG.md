# Changelog

## 1.1.0

* [fix] Declare `php-http/message-factory` as a runtime requirement: `Api::__construct` takes a `Http\Message\MessageFactory`, and neither `payum/core` nor an HTTPlug client adapter pulls that package in ([#5](https://github.com/Yproximite/payum-payzen/pull/5))
* [chore] Stop versioning `composer.lock`: this is a library, the consuming project's lock is the one that matters ([#5](https://github.com/Yproximite/payum-payzen/pull/5))
* [chore] Replace `php-http/guzzle6-adapter` with `php-http/guzzle7-adapter` and drop the `twig/twig` dev requirement, which this gateway never uses directly ([#5](https://github.com/Yproximite/payum-payzen/pull/5))
* [chore] Drop `archive.exclude` entries pointing at files this repository no longer has ([#5](https://github.com/Yproximite/payum-payzen/pull/5))

No PHP API changed: `Api::__construct` keeps its signature and no behaviour was altered.

## 1.0.1

* [doc] Improve documentation
* [feat] Add static method `getAvailableEndpoints`

## 1.0.0

Initial release! :tada:
