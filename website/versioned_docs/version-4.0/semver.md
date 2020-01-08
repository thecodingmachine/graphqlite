---
id: version-4.0-semver
title: Our backward compatibility promise
sidebar_label: Semantic versioning
original_id: semver
---

Ensuring smooth upgrades of your project is a priority. That's why we promise you backward compatibility (BC) for all 
minor GraphQLite releases. You probably recognize this strategy as [Semantic Versioning](https://semver.org/). In short, 
Semantic Versioning means that only major releases (such as 4.0, 5.0 etc.) are allowed to break backward compatibility.
Minor releases (such as 4.0, 4.1 etc.) may introduce new features, but must do so without breaking the existing API of 
that release branch (4.x in the previous example).

But sometimes, a new feature is not quite "dry" and we need a bit of time to find the perfect API.
In such cases, we prefer to gather feedback from real-world usage, adapt the API, or remove it altogether.
Doing so is not possible with a no BC-break approach.

To avoid being bound to our backward compatibility promise, such features can be marked as **unstable** or **experimental** 
and their classes and methods are marked with the `@unstable` or `@experimental` tag.

`@unstable` or `@experimental` classes / methods will **not break** in a patch release, but *may be broken* in a minor version.

As a rule of thumb:

- If you are a GraphQLite user (using GraphQLite mainly through its annotations), we guarantee strict semantic versioning
- If you are extending GraphQLite features (if you are developing custom annotations, or if you are developing a GraphQlite integration 
  with a framework...), be sure to check the tags.

Said otherwise:

- If you are a GraphQLite user, in your `composer.json`, target a major version:
  ```json
  {
    "require": {
      "thecodingmachine/graphqlite": "^4"
    }
  }
  ```
- If you are extending the GraphQLite ecosystem, in your `composer.json`, target a minor version:
  ```json
  {
    "require": {
      "thecodingmachine/graphqlite": "~4.1.0"
    }
  }
  ```

Finally, classes / methods annotated with the `@internal` annotation are not meant to be used in your code or third-party library.
They are meant for GraphQLite internal usage and they may break anytime. Do not use those directly.
