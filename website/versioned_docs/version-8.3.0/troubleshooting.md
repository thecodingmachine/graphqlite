---
id: troubleshooting
title: Troubleshooting
sidebar_label: Troubleshooting
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


**Error: Cached type in registry is not the type returned by type mapper.**

**Schema must contain unique named types but contains multiple types named "X".**

You are most likely running GraphQLite inside a long-lived / coroutine PHP runtime (Swoole, RoadRunner, FrankenPHP) where
a single worker serves many requests. These errors appear only on the **first request(s) after a cold worker boot** —
every subsequent request on that worker is fine.

In a long-lived runtime the in-memory `TypeRegistry` and type caches persist across requests, which is exactly what makes
them fast. But the *first* schema build is expensive and does real filesystem I/O (class discovery, PSR-16 file-cache
reads, docblock/annotation reading). Under coroutine concurrency — for example a client firing two introspection queries
at once, or simply two concurrent first requests — the runtime can yield between a type cache **miss** check and the
subsequent cache **write**. Two builders then construct duplicate type instances for the same GraphQL type name: the
first error is GraphQLite's `RecursiveTypeMapper` tripping its identity invariant, and the second is webonyx/graphql-php
rejecting the duplicate named types when assembling the schema.

The fix is to **single-flight the first schema build** so that the registry is fully warm before any concurrent request
can race it. The simplest approach is to warm the schema once at worker bootstrap, before serving traffic — build it and
force the full type map to resolve while the worker is still single-threaded (for instance in an `OnWorkerStart` /
bootstrap hook):

```php
$schema = $factory->createSchema();
$schema->getTypeMap();   // forces every lazy type to resolve while single-threaded
```

Calling `$schema->assertValid()` instead also fully populates the registry. Once the registry is warm, every later
request reuses it and the race window never opens again.

If you cannot warm at bootstrap, guard the first build with a lock so that only one coroutine/worker builds the schema
while the others wait, then all reuse the warm registry.

Note that a per-request cache check is **not** sufficient on its own: the build yields between the check and the write,
so the type cache is only authoritative once the first build has completed end to end without interruption. That is why
the fix lives at the application/runtime boundary (warm or lock) rather than in a per-call cache re-check. 
