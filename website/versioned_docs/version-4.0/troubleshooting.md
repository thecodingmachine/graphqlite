---
id: version-4.0-troubleshooting
title: Troubleshooting
sidebar_label: Troubleshooting
original_id: troubleshooting
---

**Error: Maximum function nesting level of '100' reached**

Webonyx's GraphQL library tends to use a very deep stack. 
This error does not necessarily mean your code is going into an infinite loop.
Simply try to increase the maximum allowed nesting level in your XDebug conf:

```
xdebug.max_nesting_level=500
```


**Cannot autowire service "_[some input type]_": argument "$..." of method "..." is type-hinted "...", you should configure its value explicitly.**

The message says that Symfony is trying to instantiate an input type as a service. This can happen if you put your
GraphQLite controllers in the Symfony controller namespace (`App\Controller` by default). Symfony will assume that any 
object type-hinted in a method of a controller is a service ([because all controllers are tagged with the "controller.service_arguments" tag](https://symfony.com/doc/current/service_container/3.3-di-changes.html#controllers-are-registered-as-services))

To fix this issue, do not put your GraphQLite controller in the same namespace as the Symfony controllers and
reconfigure your `config/graphqlite.yml` file to point to your new namespace. 
