project := $(OPENXPORT_PROJECT)

build_tools_directory=build/tools
composer=$(shell ls $(build_tools_directory)/composer_fresh.phar 2> /dev/null)
composer_lts=$(shell ls $(build_tools_directory)/composer_lts.phar 2> /dev/null)
version=$(shell git tag --sort=committerdate | tail -1)

all: init

# Remove all temporary build files
.PHONY: clean
clean:
	rm -rf build/ vendor/

# Installs composer from web if not already installed
.PHONY: composer
composer:
ifeq (, $(composer))
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p $(build_tools_directory)
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar $(build_tools_directory)/composer_fresh.phar
endif

# Installs composer LTS version from web if not already installed.
# TODO Switch from pinning specific version to LTS pinning see
#   https://github.com/composer/composer/issues/10682
.PHONY: composer_lts
composer_lts:
ifeq (, $(composer_lts))
	@echo "No composer LTS command available, downloading a copy from the web"
	mkdir -p $(build_tools_directory)
	curl -sS https://getcomposer.org/installer | php -- --version 2.2.11
	mv composer.phar $(build_tools_directory)/composer_lts.phar
endif

# Initialize project. Run this before any other target.
.PHONY: init
init: composer
	rm $(build_tools_directory)/composer.phar || true
	ln $(build_tools_directory)/composer_fresh.phar $(build_tools_directory)/composer.phar
	php $(build_tools_directory)/composer.phar install --prefer-dist --no-dev --ignore-platform-req=php

# Update dependencies and make dev tools available for development
.PHONY: update
update:
	git submodule update --init --recursive
	php $(build_tools_directory)/composer.phar update --prefer-dist --ignore-platform-req=php

# Switch to PHP 5.6 mode. In case you need to build for PHP 5.6
# Requires podman for linting based on https://github.com/dbfx/github-phplint
# WARNING this will change the composer.json file
.PHONY: php56_mode
php56_mode: composer_lts
	git checkout composer.json composer.lock
	rm $(build_tools_directory)/composer.phar
	ln $(build_tools_directory)/composer_lts.phar $(build_tools_directory)/composer.phar
	php $(build_tools_directory)/composer.phar update --prefer-dist --ignore-platform-req=php

	podman run --rm --name php56 -v "$(PWD)":"$(PWD)" -w "$(PWD)" docker.io/phpdockerio/php56-cli sh -c "! (find . -type f -name \"*.php\" -not -path \"./tests/*\" $1 -exec php -l -n {} \; | grep -v \"No syntax errors detected\")"

# Switch to PHP 8 mode
# TODO broken for now due to https://github.com/composer/composer/issues/10702
# WARNING this will change the composer.json file
.PHONY: php81_mode
php81_mode: composer
	git checkout composer.json composer.lock
	rm $(build_tools_directory)/composer.phar || true
	ln $(build_tools_directory)/composer_fresh.phar $(build_tools_directory)/composer.phar
	php $(build_tools_directory)/composer.phar update --prefer-dist --ignore-platform-req=php

	# Lint for installed PHP version (should be 8.1)
	sh -c "! (find . -type f -name \"*.php\" -not -path \"./build/*\" $1 -exec php -l -n {} \; | grep -v \"No syntax errors detected\")" || true

# Linting with PHP-CS
.PHONY: lint
lint:
	# Make devtools available again
	php $(build_tools_directory)/composer.phar install --prefer-dist --ignore-platform-req=php

	# Lint with CodeSniffer
	vendor/bin/phpcs src/

# Run Unit tests
.PHONY: unit_test
unit_test:
	php $(build_tools_directory)/composer.phar install --prefer-dist --ignore-platform-req=php
	vendor/bin/phpunit -c tests/phpunit.xml --testdox

# Switch to Graylog PHP 5.6 mode. In case you need to build for PHP 5.6 and include graylog
# WARNING this will change the composer.json file
.PHONY: graylog_php56_mode
graylog_php56_mode:
	make php56_mode
	php $(build_tools_directory)/composer.phar require paragonie/constant_time_encoding:'<2' psr/log:'<2'
	php $(build_tools_directory)/composer.phar update --prefer-dist --ignore-platform-req=php

# Switch to Graylog PHP 8.1 mode. In case you need to build for PHP 8.1 and include graylog
# WARNING this will change the composer.json file
.PHONY: graylog_php81_mode
graylog_php81_mode:
	make php81_mode
	php $(build_tools_directory)/composer.phar require graylog2/gelf-php
	php $(build_tools_directory)/composer.phar update --prefer-dist --ignore-platform-req=php

# Make it possible to run legacy unit tests
# WARNING this will change composer.json and lock file!
.PHONY: integration_php56_mode
integration_php56_mode:
	make php56_mode
	composer require --dev phpunit/phpunit:^5 phpunit/php-timer:^1 doctrine/instantiator:1.0.5 symfony/yaml:^3 phpdocumentor/reflection-docblock:^3 --ignore-platform-req=php -W
	php $(build_tools_directory)/composer.phar update --prefer-dist --ignore-platform-req=php

.PHONY: zip
zip:
# In case of project build: use a predefined config
ifeq (integration,$(project))
	cp tests/resources/plugin_config.php config/config.php
else ifeq (webcom, $(project))
	rm config/config.php || true
endif
	php $(build_tools_directory)/composer.phar install --prefer-dist --no-dev --ignore-platform-req=php
	php $(build_tools_directory)/composer.phar archive -f zip --dir=build/archives --file=jmap-squirrelmail-$(version).zip
# In case of project build: rename and put jmap folder to root level
ifneq (, $(project))
	mkdir -p build/tmp/jmap
	unzip -q build/archives/jmap-squirrelmail-$(version).zip -d build/tmp/jmap
	cd build/tmp && zip -qmr jmap-squirrelmail-$(version)-$(project).zip jmap/ && mv jmap-squirrelmail-$(version)-$(project).zip ../archives
endif

.PHONY: fulltest
fulltest: lint unit_test
