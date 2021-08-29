---
id: implementing-security
title: Connecting GraphQLite to your framework's security module
sidebar_label: Connecting security to your framework
---

<div class="alert alert-info">
    This step is NOT necessary for users using GraphQLite through the Symfony Bundle or the Laravel package
</div>

GraphQLite needs to know if a user is logged or not, and what rights it has.
But this is specific of the framework you use.

To plug GraphQLite to your framework's security mechanism, you will have to provide two classes implementing: 

* `TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface`
* `TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface`

Those two interfaces act as adapters between GraphQLite and your framework:

```php
interface AuthenticationServiceInterface
{
    /**
     * Returns true if the "current" user is logged
     */
    public function isLogged(): bool;

    /**
     * Returns an object representing the current logged user.
     * Can return null if the user is not logged.
     */
    public function getUser(): ?object;
}
``` 

```php
interface AuthorizationServiceInterface
{
    /**
     * Returns true if the "current" user has access to the right "$right"
     *
     * @param mixed $subject The scope this right applies on. $subject is typically an object or a FQCN. Set $subject to "null" if the right is global.
     */
    public function isAllowed(string $right, $subject = null): bool;
}
```

You need to write classes that implement these interfaces. Then, you must register those classes with GraphQLite.
It you are [using the `SchemaFactory`](other_frameworks.md), you can register your classes using:

```php
// Configure an authentication service (to resolve the @Logged annotations).
$schemaFactory->setAuthenticationService($myAuthenticationService);
// Configure an authorization service (to resolve the @Right annotations).
$schemaFactory->setAuthorizationService($myAuthorizationService);
```

