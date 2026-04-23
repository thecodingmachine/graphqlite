---
id: symfony-bundle
title: Getting started with Symfony
sidebar_label: Symfony bundle
---

<div class="alert alert--warning">
    <strong>Be advised!</strong> This documentation will be removed in a future release.  For current and up-to-date Symfony bundle specific documentation, please see the <a href="https://github.com/thecodingmachine/graphqlite-bundle">Github repository</a>.
</div>

The GraphQLite bundle is compatible with **Symfony 4.x** and **Symfony 5.x**.

## Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require thecodingmachine/graphqlite-bundle
```

Now, go to the `config/packages/graphqlite.yaml` file and edit the namespaces to match your application.

```yaml title="config/packages/graphqlite.yaml"
graphqlite:
  namespace:
    # The namespace(s) that will store your GraphQLite controllers.
    # It accept either a string or a list of strings.
    controllers: App\GraphQLController\
    # The namespace(s) that will store your GraphQL types and factories.
    # It accept either a string or a list of strings.
    types:
    - App\Types\
    - App\Entity\
```

More advanced parameters are detailed in the ["advanced configuration" section](#advanced-configuration)

## Applications that don't use Symfony Flex

Open a terminal in your current project directory and run:

```console
$ composer require thecodingmachine/graphqlite-bundle
```

Enable the library by adding it to the list of registered bundles in the `app/AppKernel.php` file:


```php title="app/AppKernel.php"
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

```yaml title="config/routes.yaml"
# Add these 2 lines to config/routes.yaml
graphqlite_bundle:
  resource: '@GraphQLiteBundle/Resources/config/routes.xml'
```

Last but not least, create the configuration file at `config/packages/graphqlite.yaml`:

```yaml title="config/packages/graphqlite.yaml"
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
```

## Advanced configuration

### Customizing error handling

You can add a "debug" section in the `graphqlite.yaml` file to customize the way errors are handled.
By default, GraphQLite configures the underlying Webonyx GraphQL library this way:

- All exceptions that implement the `ClientAware` interface are caught by GraphQLite
- All other exceptions will bubble up and by caught by Symfony error handling mechanism

We found out those settings to be quite convenient but you can override those to your preference.

```yaml title="config/packages/graphqlite.yaml"
graphqlite:
  # ...
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

<div class="alert alert--warning">
  <strong>Do not put your GraphQL controllers in the <code>App\Controller</code> namespace</strong> Symfony applies a particular compiler pass to classes in the <code>App\Controller</code> namespace. This compiler pass will prevent you from using input types. Put your controllers in another namespace. We advise using <code>App\GraphqlController</code>.
</div>

The Symfony bundle come with a set of advanced features that are not described in this install documentation (like providing a login/logout mutation out of the box). Jump to the ["Symfony specific features"](symfony-bundle-advanced.mdx) documentation of GraphQLite if you want to learn more.
