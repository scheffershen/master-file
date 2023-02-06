.PHONY: install
install:
	php bin/console doctrine:database:create
	php bin/console doctrine:schema:update --force
	php bin/console doctrine:fixtures:load
	chown www-data:www-data -R var data
	@echo "Site is installed"

.PHONY: install-test
install-test:
	php bin/console doctrine:database:create --env=test
	php bin/console doctrine:schema:update --force --env=test
	php bin/console doctrine:fixtures:load --env=test --group TEST
	chown www-data:www-data -R var data
	@echo "Site is installed"

.PHONY: up
up:
	php bin/console app:maintenance:lock on
	svn up config/ src/ templates/ translations/ public/
	php bin/console cache:clear --no-warmup --env=prod
	php bin/console doctrine:schema:update --force
	chown www-data:www-data -R var
	php bin/console app:maintenance:lock off
	@echo "Site is updated"

.PHONY: doctrine
doctrine:
	php bin/console doctrine:schema:update --force

.PHONY: doctrine-test
doctrine-test:
	php bin/console doctrine:schema:update --env=test --force

.PHONY: fixtures
fixtures-dev:
	php bin/console doctrine:fixtures:load --group DEV

.PHONY: fixtures-test
fixtures-test:
	php bin/console doctrine:fixtures:load --env=test --group TEST

.PHONY: entity
entity:
	php bin/console make:entity		

.PHONY: crud
crud:
	php bin/console make:crud		

.PHONY: off
off:
	php bin/console app:maintenance:lock off

.PHONY: on
on:
	php bin/console app:maintenance:lock on		

.PHONY: cache
cache:
	php bin/console cache:clear

phpunit:
	php vendor/bin/phpunit

test-application:
	php vendor/bin/phpunit --group application

test-integration:
	php vendor/bin/phpunit --group integration

test-unit:
	php vendor/bin/phpunit --group unit

behat:
	php vendor/bin/behat