all: @install

build: @install @buildphar

install:
	composer install

test:
	build/local/test_setup.sh

clean:
	rm -Rf ./public
	rm magepatch.phar
	rm .magedir
	rm .php_cs_cache
	rm box.json

update:
	composer update

phpunit:
	vendor/bin/phpunit -v --debug

phpspec:
	vendor/bin/phpspec run -f pretty -v

update-index:
	@echo "Be sure to double check the timestamp, this repo may be faster"
	rm res/patches.json
	wget http://magepatch.fros.it/patches.json && mv patches.json res/
	echo "Updated patch data"

buildphar:
	composer update --no-dev -o
	box build -vv
	shasum magepatch.phar > magepatch.phar.version

cs-fixer:
	@echo "Displaying code style..."
	vendor/bin/php-cs-fixer fix --diff --dry-run -v

cs-fix:
	@echo "Fixing code style..."
	vendor/bin/php-cs-fixer fix -v