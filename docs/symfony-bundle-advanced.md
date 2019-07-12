---
id: symfony-bundle-advanced
title: Symfony bundle: advanced usage
sidebar_label: Symfony specific features
---

The Symfony bundle comes with a number of features to ease the integration of GraphQLite in Symfony.


## Injecting the Request

You can inject the Symfony Request object in any query/mutation/field.

Most of the time, getting the request object is irrelevant. Indeed, it is GraphQLite's job to parse this request and
manage it for you. Sometimes yet, fetching the request can be needed. In those cases, simply type-hint on the request
in any parameter of your query/mutation/field.

```php
use Symfony\Component\HttpFoundation\Request;

/**
 * @Query
 */
public function getUser(int $id, Request $request): User
{
    // The $request object contains the Symfony Request.
}
```
