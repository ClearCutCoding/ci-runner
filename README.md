# CI Runner

## Installation

`composer require clearcutcoding/symfony-ci-runner --dev`

## Configuration

- Create config in the root of your php project named `ci-runner.config.yaml`
- Set which CI processes to run.  Some have their own root config file to identify which directories to run against.  For others, set the directories here (in brackets it shows possible values).

```
rector: true [true | false]
phpcsfixer: true [true | false]
lintyaml: config src  [list of dirs | false]
linttwig: src [list of dirs | false]
phpcs: true [true | false]
phpunit: true [true | false]
phpmd: src tests [list of dirs | false]
phpstan: true [true | false]
psalm: true [true | false]
```

The following need to have config files created:

```
rector -> rector.php
phpcsfixer -> .php-cs-fixer.php
phpcs -> phpcs.xml
phpunit -> phpunit.xml
phpstan -> phpstan.neon
psalm -> psalm.xml
```
### Dev

- Run `vendor/bin/ci-runner` from inside the project root directory

### Build

- Run `vendor/bin/ci-runner --no-mods` to ensure no file-changing processes will run.

