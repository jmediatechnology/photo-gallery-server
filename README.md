# photo-gallery-server
Backend for Photo Gallery


## Getting Started on dev

Run:
```
echo "your-password" >> db/password.txt
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

### Manual Browser testing

Navigate in the Browser to: http://localhost:9000/images/42da7891-7572-4489-ad22-5ad095845685.jpg
You should see a Picasso image.

### Running the automated tests

Best way to run all tests (it performs cache clear and schema create):
```
docker compose exec server composer test-execute
docker compose exec server composer test-execute-dev
```

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

To run a code coverage report:
```
rm var/reports/coverage/clover.xml
docker compose exec server php -d xdebug.mode=coverage vendor/bin/phpunit -d memory_limit=-1 --coverage-clover "var/reports/coverage/clover.xml"
```

Load the code coverage report in your IDE, for example, PHPStorm: View → Tool Windows → Coverage.
Import report from file: var/reports/coverage/clover.xml.

### Manual API testing

Trying to access a secured api endpoint will fail:
```
curl -X GET http://localhost:9000/photographs -H "Content-Type: application/json"
```
Response: JWT Token not found. 


Get token for anonymous user:
```
curl -X GET http://localhost:9000/api/login/anonymous -H "Content-Type: application/json"
```
Response: {"token":"<token>"}


Get token for admin user:
```
curl -X POST http://localhost:9000/api/login_check -H "Content-Type: application/json" -d '{"username": "admin", "password": "admin" }'
```
Response: {"token":"<token>"}

Store it as a variable: 
```
AWESOME_TOKEN=<token>
```

Get all photographs by CURL + token:
```
curl -X GET http://localhost:9000/photographs -H "Content-Type: application/json" -H "Authorization: Bearer $AWESOME_TOKEN"
```
Response: all photographs in json.


Create photographs by CURL (only for admin):
```
curl -X POST http://localhost:9000/photographs -H "Content-Type: multipart/form-data" -H "Authorization: Bearer $AWESOME_TOKEN" -F "title=some title" -F "description=some description" -F "0=@./fixtures/images/portrait-of-dora-maar.jpg" 
```
Response: photograph data in json.


## Start server on Production

Run:
```
docker compose -f compose.yaml -f compose.prod.yaml up -d --build
```
