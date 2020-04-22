.PHONY: dependencies, unit-tests, integration-tests, test-coverage, style-check, dependency-check, static-analysis, style-fix, repl
build:
	@make dependencies && make dependency-check && make static-analysis && make style-check && make unit-tests && make integration-tests

dependencies:
	@composer install

unit-tests:
	@vendor/bin/phpunit --bootstrap=./tests/bootstrap.php --testsuite Unit

integration-tests:
	@vendor/bin/phpunit --bootstrap=./tests/bootstrap.php --testsuite Integration

test-coverage:
	@vendor/bin/phpunit --coverage-html ./coverage

style-check:
	@vendor/bin/phpcs --standard=PSR12 ./src/* ./tests/*

dependency-check:
	@vendor/bin/composer-require-checker check -vvv ./composer.json

static-analysis:
	@vendor/bin/phpstan analyze && ./vendor/bin/psalm --show-info=false

style-fix:
	@vendor/bin/phpcbf --standard=PSR12 ./src ./tests

repl:
	@vendor/bin/psysh ./bootstrap/repl.php