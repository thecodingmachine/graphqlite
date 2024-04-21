PSR-15 Integration Example
==========================

```
composer install
php -S 127.0.0.1:8080
```

```
curl -X POST -d '{"query":"{ hello(name: \"World\") }"}' -H "Content-Type: application/json" http://localhost:8080/graphql
```
