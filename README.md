# photo-gallery-server
Backend for Photo Gallery


## Getting Started on dev

Create secrets:
```
mkdir secrets
printf "my-database-password" >> secrets/db-password.txt
chmod 600 secrets/db-password.txt
```

Build and run containers:
```
docker compose -f compose.yaml -f compose.override.yaml -f compose.dev.yaml up -d --build
```

Recreate database for dev env:
```
docker compose exec server composer test-db-recreate
```

Recreate database for dev env + load fixtures:
```
docker compose exec server composer test-load-fixtures
```

Navigate to http://localhost:8080 to open phpmyadmin.
Login with:
- user: root
- password: contents of secrets/password.txt

Inspect the database.

## Testing

More information about running tests can be found at [Testing](docs/testing/Testing.md).

## AI

Anthropic AI SDK is integrated in Photo Gallery Server to generate text-by-image. 

More information about can be found at [AI](docs/ai/AI.md).


## Start server on Production

Create secrets:
```
mkdir secrets
vi secrets/db-password-prod.txt
vi secrets/anthropic-api-key.txt
```

Remove new lines from secrets: 
```
sed -i -z 's/\n*$//' secrets/db-password-prod.txt
sed -i -z 's/\n*$//' secrets/anthropic-api-key.txt
```

Change file permissions:
```
chmod 600 secrets/*.txt
```

Generate a `.env.prod` file:
```
vi .env.prod
```
Fill: `DATABASE_URL`, `CORS_ALLOW_ORIGIN`, and `ANTHROPIC_MODEL`.


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

### Harden file permissions for secrets

Find for www-data the UID 
```
docker compose -f compose.yaml -f compose.prod.yaml exec server id www-data
```
Usually it's 33.

```
chown 33:33 secrets/db-password-prod.txt
chmod 400 secrets/db-password-prod.txt
```
