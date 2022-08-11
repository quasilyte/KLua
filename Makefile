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
	./vendor/bin/ktest compare --preload preload.php ./examples/1_simple.php
	./vendor/bin/ktest compare --preload preload.php ./examples/2_phpfunc.php
	./vendor/bin/ktest compare --preload preload.php ./examples/3_override_print.php
	./vendor/bin/ktest compare --preload preload.php ./examples/4_limited_stdlib.php
	./vendor/bin/ktest compare --preload preload.php ./examples/5_plugin_sandbox.php
	./vendor/bin/ktest compare --preload preload.php ./examples/6_phpfunc_table.php
	./vendor/bin/ktest compare --preload preload.php ./examples/7_userdata.php
	./vendor/bin/ktest compare --preload preload.php ./examples/8_memory_limit.php
