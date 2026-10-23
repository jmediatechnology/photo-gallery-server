# photo-gallery-server

Backend for Photo Gallery.

## AI

Photo Gallery Server can generate image descriptions.
Image description generation happens through an image description provider. 

The provider is chosen by setting the environment variable: `IMAGE_DESCRIPTION_PROVIDER`.
It supports: 

- `anthropic`: Anthropic API (production)
- `ollama`: local vision model via Ollama (optional in dev, see above)
- `fake`: random text, no network (dev default)

More information about can be found at [AI](docs/ai/AI.md).

## Getting started on dev

Create secrets:
```
mkdir secrets
printf "my-database-password" >> secrets/db-password.txt
chmod 600 secrets/db-password.txt
```

Continue guide with:
- [fake image description generator](docs/getting_started/FAKE_IMAGE_DESCRIPTION_GENERATOR.md).
- [dockerized ollama service](docs/getting_started/DOCKERIZED_OLLAMA_SERVICE.md).
- [host native ollama service](docs/getting_started/HOST_NATIVE_OLLAMA_SERVICE.md).

## Database

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

## Start server on Production

Getting started on production guide:
- [getting started on prod](docs/getting_started/PROD.md).
