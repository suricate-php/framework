phpstan:
	./vendor/bin/phpstan analyse --autoload-file=vendor/autoload.php src --level max
phpmd:
	./vendor/bin/phpmd src ansi cleancode, codesize, controversial, design, naming, unusedcode
test:
	./vendor/bin/phpunit
