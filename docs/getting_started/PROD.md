# Getting started on prod

Create secrets:
```
mkdir secrets
touch secrets/db-password.txt
nano secrets/db-password-prod.txt
nano secrets/anthropic-api-key.txt
```

Remove new lines from secrets:
```
sed -i -z 's/\n*$//' secrets/db-password-prod.txt
sed -i -z 's/\n*$//' secrets/anthropic-api-key.txt
```

Generate a `.env.prod` file:
```
vi .env.prod
```
Fill: `DATABASE_URL`, `CORS_ALLOW_ORIGIN`, and `ANTHROPIC_MODEL`.


Pick `IMAGE_DESCRIPTION_PROVIDER`.
For a production environment `anthropic` provides the best performance. 

Build and run containers:
```
docker compose -f compose.yaml -f compose.prod.yaml up -d --build
```

Generate SSL keys:
```
docker compose exec server php bin/console lexik:jwt:generate-keypair --skip-if-exists
```

Initializing db schema (run only once):
```
docker compose -f compose.yaml -f compose.prod.yaml exec server php bin/console doctrine:schema:create
```

Create a user:
```
docker compose exec server php bin/console app:user:create
```

Delete a user:
```
docker compose exec server php bin/console app:user:delete
```

## Harden file permissions for secrets

Find for www-data the UID
```
docker compose -f compose.yaml -f compose.prod.yaml exec server id www-data
```
Just make sure it's 33; it usually is.

Change owner to www-data, and set file permission to read-only for the owner.
```
chown 33:33 secrets/*.txt
chmod 400 secrets/*.txt
```
