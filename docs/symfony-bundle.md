---
id: symfony-bundle
title: Getting started with Symfony
sidebar_label: Symfony bundle
---

The GraphQLite bundle is compatible with Symfony 4+.
From your Symfony 4+ project, run the following command:

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require thecodingmachine/graphqlite-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require thecodingmachine/graphqlite-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new TheCodingMachine\GraphQLite\Bundle\GraphQLiteBundle,
        );

        // ...
    }

    // ...
}
```

### Step 3 (optional): Create a configuration file

Create a configuration file in `config/packages/graphqlite.yaml`:

```yaml
graphqlite:
    namespace:
      controllers: App\Controller\
      types: 
      - App\Types\
      - App\Models\
    debug:
      # Include exception messages in output when an error arises
      INCLUDE_DEBUG_MESSAGE: false
      # Include stacktrace in output when an error arises
      INCLUDE_TRACE: false
      # Exceptions are not caught by the engine and propagated to Symfony
      RETHROW_INTERNAL_EXCEPTIONS: false
      # Exceptions that do not implement ClientAware interface are 
      # not caught by the engine and propagated to Symfony.
      RETHROW_UNSAFE_EXCEPTIONS: true
```

The 'graphqlite.namespace.controllers' configuration variable is the namespace(s) that will store your GraphQLite controllers.
The 'graphqlite.namespace.types' configuration variable is the namespace(s) that will store your GraphQL types and factories.

For both 'graphqlite.namespace.controllers' and 'graphqlite.namespace.types', you can pass a string (the namespace to target)
or an array of strings if you have several namespaces to track.

## Configure the bundle

Using `config/packages/graphqlite.yaml`, you can configure how exceptions will be handled.

By default (with no configuration), the `RETHROW_UNSAFE_EXCEPTIONS` mode will be used: any exception will be propagated
to Symfony, unless it implements the `GraphQL\Error\ClientAware` exception.

The debug parameters (`INCLUDE_DEBUG_MESSAGE`, `INCLUDE_TRACE`, `RETHROW_INTERNAL_EXCEPTIONS` and `RETHROW_UNSAFE_EXCEPTIONS`),
are detailed in the [documentation of the Webonix GraphQL-PHP library that is used by "GraphQLite"](https://webonyx.github.io/graphql-php/error-handling/).
