# RR - Recipe Runner CLI tool

[![Build Status](https://img.shields.io/travis/recipe-runner/rr-cli/master.svg?style=flat-square)](https://travis-ci.org/recipe-runner/rr-cli)


## Requires

* PHP +7.2
* Composer +1.8

## Installation

The preferred installation method is [composer](https://getcomposer.org):

```bash
composer require recipe-runner/rr-cli
```

## Usage

Inside the folder `./bin`, there is an executable file called `rr`

```
./bin/rr
```

### Run your first recipe

1. Copy the following YAML code into a file called `example.rr.yml`:

```yaml
name: "Very simple example using IO module"

extra:
  rr:
    packages:
      "recipe-runner/io-module": "1.0.x-dev"
steps:
    - actions:
        - name: "Greeting"
          write: "Hi user. Welcome :)"
        - ask: "How are you? "
          register: "response"
        - write: "The Response was: '{{response['response']}}'"
```

2. Run the recipe:
```
./bin/rr run example.rr.yml
```

## Unit tests

You can run the unit tests with the following command:

```bash
$ cd rr-cli
$ composer test
```

## License

This library is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
