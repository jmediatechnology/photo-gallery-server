# Manual API Testing

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
