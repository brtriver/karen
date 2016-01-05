PHP_BIN:=$(shell which php)
CURL_BIN:=$(shell which curl)
SINCE:=v0.1
UNTIL:=HEAD

setup: composer.phar php-cs-fixer.phar

php-cs-fixer.phar:
	$(CURL_BIN) http://get.sensiolabs.org/php-cs-fixer.phar -o php-cs-fixer.phar

composer.phar:
	$(PHP_BIN) -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"

install:
	$(PHP_BIN) composer.phar install

server:
	$(PHP_BIN) -S localhost:8888 -t ./web

fixer:
	$(PHP_BIN) php-cs-fixer.phar fix --level=psr2 src

changelog:
	git log --pretty=format:" * %h %s" $(SINCE)..$(UNTIL) -- src tests
