# photo-gallery-server
Backend for Photo Gallery


## Getting Started on dev

Run:
```
printf "your-password" >> secrets/password.txt
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

Go to http://localhost:8080 to open phpmyadmin.
Login with:
- user: root
- password: your content of db/password.txt

Inspect the database.

## Testing

More information about running tests can be found at [Testing](docs/testing/Testing.md).

## AI

Anthropic AI SDK is integrated in Photo Gallery Server to generate text-by-image. 

More information about can be found at [AI](docs/ai/AI.md).


## Start server on Production

Run:
```
export DB_PASSWORD_PROD="some-test-value"
docker compose -f compose.yaml -f compose_prod.yaml up -d --build
```

Create a user:
```
docker compose exec server php bin/console app:user:create
```

Delete a user:
```
docker compose exec server php bin/console app:user:delete
```
