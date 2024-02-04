all: phpcbf phpcs phpcsfixer phpstan phpunit

phpcbf:
	php ./vendor/bin/phpcbf -p --standard=./phpcs.xml

phpcs:
	php ./vendor/bin/phpcs -s -p --standard=./phpcs.xml

phpcsfixer:
	php ./vendor/bin/php-cs-fixer fix -v --show-progress dots --using-cache no --config=".php-cs-fixer.php"

phpstan:
	php ./vendor/bin/phpstan analyse -c "./phpstan.neon"

phpunit:
	php ./vendor/bin/phpunit -c ./phpunit.xml
