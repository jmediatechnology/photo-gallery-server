# PHP Mess Detector

Run phpmd:

```
docker compose exec server vendor/bin/phpmd analyze src/ --ruleset phpmd.blocking.xml
docker compose exec server vendor/bin/phpmd analyze --ruleset phpmd.advisory.xml --ignore-violations-on-exit src/
```
