# Running Code Coverage Report

To run a code coverage report:
```
rm var/reports/coverage/clover.xml
docker compose exec server php -d xdebug.mode=coverage vendor/bin/phpunit -d memory_limit=-1 --coverage-clover "var/reports/coverage/clover.xml"
```

Run code coverage percentage:
```
docker compose exec server php bin/coverage-percentage.php ./var/reports/coverage/clover.xml
```

Run code coverage to see summary:
```
docker compose exec server php bin/coverage-percentage.php ./var/reports/coverage/clover.xml  --metric=all
```


# Load Code Coverage Report in an IDE

In PHPStorm: View → Tool Windows → Coverage → import from file: `var/reports/coverage/clover.xml`.
