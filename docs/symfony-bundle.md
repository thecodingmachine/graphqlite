---
id: symfony-bundle
title: Getting started with Symfony
sidebar_label: Symfony bundle
---

The GraphQLite bundle is compatible with **Symfony 4.x** and **Symfony 5.x**.

<div class="alert alert-warning">
    The Symfony Flex recipe is not yet available.
</div>

## Installation

Open a terminal in your current project directory and run:

```console
$ composer require thecodingmachine/graphqlite-bundle
```

Enable the library by adding it to the list of registered bundles in the `app/AppKernel.php` file:

**app/AppKernel.php**
```php
<?php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // other bundles...
            new TheCodingMachine\GraphQLite\Bundle\GraphQLiteBundle,
        );
    }
}
```

Now, enable the "graphql/" route by editing the `config/routes.yaml` file:

**config/routes.yaml**
```yaml
# Add these 2 lines to config/routes.yaml
graphqlite_bundle:
  resource: '@GraphqliteBundle/Resources/config/routes.xml'
```

Last but not least, create the configuration file at `config/packages/graphqlite.yaml`:

**config/packages/graphqlite.yaml**
```yaml
graphqlite:
    namespace:
      # The namespace(s) that will store your GraphQLite controllers.
      # It accept either a string or a list of strings.
      controllers: App\GraphqlController\
      # The namespace(s) that will store your GraphQL types and factories.
      # It accept either a string or a list of strings.
      types: 
      - App\Types\
      - App\Entity\
    debug:
      # Include exception messages in output when an error arises.
      INCLUDE_DEBUG_MESSAGE: false
      # Include stacktrace in output when an error arises.
      INCLUDE_TRACE: false
      # Exceptions are not caught by the engine and propagated to Symfony.
      RETHROW_INTERNAL_EXCEPTIONS: false
      # Exceptions that do not implement ClientAware interface are 
      # not caught by the engine and propagated to Symfony.
      RETHROW_UNSAFE_EXCEPTIONS: true
```

The debug parameters are detailed in the [documentation of the Webonyx GraphQL library](https://webonyx.github.io/graphql-php/error-handling/)
which is used internally by GraphQLite.

<div class="alert alert-warning"><strong>Do not put your GraphQL controllers in the <code>App\Controller</code> namespace</strong>
Symfony applies a particular compiler pass to classes in the <code>App\Controller</code> namespace. This compiler pass will prevent you
from using input types. Put your controllers in another namespace. We advise using <code>App\GraphqlController</code>.
</div>

The Symfony bundle come with a set of advanced features that are not described in this install documentation (like providing a login/logout mutation out of the box).
Jump to the ["Symfony specific features"](symfony-bundle-advanced.md) documentation of GraphQLite if you want to learn more.
