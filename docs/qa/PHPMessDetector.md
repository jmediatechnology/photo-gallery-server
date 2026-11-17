# PHP Mess Detector

PHPMD is used to detect code smells and possible errors.

https://phpmd.org/

PHPMD gets its metrics from PDepend, and that PDepend keeps its own cache. 
Before using phpmd run: 

```
docker compose exec server rm -rf /tmp/pdepend
```


Run phpmd:

```
docker compose exec server vendor/bin/phpmd analyze src/ --ruleset phpmd.blocking.xml
docker compose exec server vendor/bin/phpmd analyze --ruleset phpmd.advisory.xml --ignore-violations-on-exit src/
```
