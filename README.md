# CI Runner

- Create config in the root of your php project named `ci-runner.config.yaml`
- Set which CI processes to run

```
phpcsfixer: true
lintyaml: true
linttwig: true
phpcs: true
phpunit: true
phpmd: true
phpstan: true
psalm: true
```

- Run `vendor/bin/ci-runner` from inside the project root directory

