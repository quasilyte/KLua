.PHONY: test
test: test-php test-kphp test-examples
	@echo "everything is OK"

.PHONY: test-php
test-php:
	php -d opcache.enable_cli=true -d opcache.preload=preload.php ./vendor/bin/phpunit tests

.PHONY: test-kphp
test-kphp:
	./vendor/bin/ktest phpunit tests

.PHONY: test-examples
test-examples:
	./vendor/bin/ktest compare --preload preload.php ./examples/simple.php
	./vendor/bin/ktest compare --preload preload.php ./examples/phpfunc.php
	./vendor/bin/ktest compare --preload preload.php ./examples/override_print.php
	./vendor/bin/ktest compare --preload preload.php ./examples/limited_stdlib.php
	./vendor/bin/ktest compare --preload preload.php ./examples/plugin_sandbox.php
