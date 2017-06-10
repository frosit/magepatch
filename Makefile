all: @install

install:
	composer install

test:
	build/local/test_setup.sh
	@cscheck

clean:
	rm -Rf ./public
	rm magepatch.phar
	rm .magedir

update:
	composer update

phpunit:
	vendor/bin/phpunit

cscheck:
	php vendor/bin/phpcs --colors --standard=PSR2 --extensions=php src/

csfix:
	php vendor/bin/phpcbf --colors --standard=PSR2 --extensions=php src/

update-index:
	rm res/patches.json
	wget http://magepatch.gdprproof.com/patches.json && mv patches.json res/
	echo "Updated patch data"

buildphar:
	box build -vv
	shasum magepatch.phar > magepatch.phar.version

