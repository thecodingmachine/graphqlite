---
id: validation
title: Validation
sidebar_label: User input validation
---

GraphQLite does not handle user input validation by itself. It is out of its scope.

However, it can integrate with your favorite framework validation mechanism. The way you validate user input will 
therefore depend on the framework you are using.

## Validating user input with Laravel

If you are using Laravel, jump directly to the [GraphQLite Laravel package advanced documentation](laravel-package-advanced.md#support-for-laravel-validation-rules)
to learn how to use the Laravel validation with GraphQLite.

## Validating user input with Symfony validator

GraphQLite provides a bridge to use the [Symfony validator](https://symfony.com/doc/current/validation.html) directly in your application.

- If you are using Symfony and the Symfony GraphQLite bundle, the bridge is available out of the box
- If you are using another framework, the "Symfony validator" component can be used in standalone mode. If you want to 
  add it to your project, you can require the *thecodingmachine/graphqlite-symfony-validator-bridge* package:
  ```bash
  $ composer require thecodingmachine/graphqlite-symfony-validator-bridge
  ```

### Using the Symfony validator bridge

Usually, when you use the Symfony validator component, you put annotations in your entities and you validate those entities
using the `Validator` object.

**UserController.php**
```php
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TheCodingMachine\Graphqlite\Validator\ValidationFailedException

class UserController
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @Mutation
     */
    public function createUser(string $email, string $password): User
    {
        $user = new User($email, $password);

        // Let's validate the user
        $errors = $this->validator->validate($user);

        // Throw an appropriate GraphQL exception if validation errors are encountered
        ValidationFailedException::throwException($errors);

        // No errors? Let's continue and save the user
        // ...
    }
}
```

Validation rules are added directly to the object in the domain model:

**User.php**
```php
use Symfony\Component\Validator\Constraints as Assert;

class User
{
    /**
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     */
    private $email;

    /**
     * The NotCompromisedPassword assertion asks the "HaveIBeenPawned" service if your password has already leaked or not.
     * @Assert\NotCompromisedPassword
     */
    private $password;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    // ...
}
```

If a validation fails, GraphQLite will return the failed validations in the "errors" section of the JSON response:

```json
{
  "errors": [
    {
      "message": "The email '\"foo@thisdomaindoesnotexistatall.com\"' is not a valid email.",
      "extensions": {
        "code": "bf447c1c-0266-4e10-9c6c-573df282e413",
        "field": "email",
        "category": "Validate"
      }
    }
  ]
}
```


### Using the validator directly on a query / mutation / factory ...

If the data entered by the user is mapped to an object, please use the "validator" instance directly as explained in 
the last chapter. It is a best practice to put your validation layer as close as possible to your domain model.

If the data entered by the user is **not** mapped to an object, you can directly annotate your query, mutation, factory...

<div class="alert alert-warning">You generally don't want to do this. It is a best practice to put your validation constraints
on your domain objects. Only use this technique if you want to validate user input and user input will not be stored
in a domain object.</div>

Use the `@Assertion` annotation to validate directly the user input.

```php
use Symfony\Component\Validator\Constraints as Assert;
use TheCodingMachine\Graphqlite\Validator\Annotations\Assertion;

/**
 * @Query
 * @Assertion(for="email", constraint=@Assert\Email())
 */
public function findByMail(string $email): User
{
    // ...
}
```

Notice that the "constraint" parameter contains an annotation (it is an annotation wrapped in an annotation).

You can also pass an array to the `constraint` parameter:

```php
@Assertion(for="email", constraint={@Assert\NotBlank(), @Assert\Email()})
```
