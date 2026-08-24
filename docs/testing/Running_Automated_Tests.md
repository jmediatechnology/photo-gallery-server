# Running Automated Tests

Running automated tests can be accomplished by multiple ways. 


# Running test scripts by composer script

Running test scripts as defined by custom composer scripts.

```
docker compose exec server composer test-execute
```

The composer script mentioned here above will perform database operations on a SQLite in-memory database.

# Running test scripts by phpunit

Run all tests:
```
docker compose exec server vendor/bin/phpunit tests
```

Run all unit tests:
```
docker compose exec server vendor/bin/phpunit tests/Unit
```

Run all integration tests:
```
docker compose exec server vendor/bin/phpunit tests/Integration
```

Run all application/functional tests:
```
docker compose exec server vendor/bin/phpunit tests/Application
```

To run unit tests individually:
```
docker compose exec server vendor/bin/phpunit tests --filter=PhotographTest
```

To run application tests individually:
```
docker compose exec server vendor/bin/phpunit tests/Application --filter=canGetTokenForAdminUser
docker compose exec server vendor/bin/phpunit tests/Application --filter=canGetTokenForAnonymousUser
docker compose exec server vendor/bin/phpunit tests/Application --filter=accessGetsDeniedForSecuredEndpointWithoutToken
docker compose exec server vendor/bin/phpunit tests/Application --filter=doesNotAllowCreatingPhotographWhenUserIsNotAdmin
docker compose exec server vendor/bin/phpunit tests/Application --filter=canCreatePhotographWithUUID
docker compose exec server vendor/bin/phpunit tests/Application --filter=canCreatePhotographWithoutUUID
docker compose exec server vendor/bin/phpunit tests/Application --filter=doesNotAllowCreatingPhotographWhenTitleIsEmpty
docker compose exec server vendor/bin/phpunit tests/Application --filter=doesNotAllowCreatingPhotographWhenTitleIsNotUnique
docker compose exec server vendor/bin/phpunit tests/Application --filter=doesNotAllowCreatingPhotographWhenFileIsNotUploaded
docker compose exec server vendor/bin/phpunit tests/Application --filter=canDeletePhotograph
docker compose exec server vendor/bin/phpunit tests/Application --filter=canGetAllPhotographs
docker compose exec server vendor/bin/phpunit tests/Application --filter=canGetAllPhotographsByTitle
docker compose exec server vendor/bin/phpunit tests/Application --filter=canGetOnePhotograph
docker compose exec server vendor/bin/phpunit tests/Application --filter=canUpdatePhotograph
docker compose exec server vendor/bin/phpunit tests/Application --filter=canUpload
```
