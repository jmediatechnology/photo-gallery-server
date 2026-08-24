# Running Code Coverage Report

To run a code coverage report:
```
rm var/reports/coverage/clover.xml
docker compose exec server php -d xdebug.mode=coverage vendor/bin/phpunit -d memory_limit=-1 --coverage-clover "var/reports/coverage/clover.xml"
```

Load the code coverage report in your IDE, for example, PHPStorm: View → Tool Windows → Coverage.

Import report from file: `var/reports/coverage/clover.xml`.

