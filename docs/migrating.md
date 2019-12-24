---
id: migrating
title: Migrating
sidebar_label: Migrating
---

## Migrating from v3.0 to v4.0

If you are a "regular" GraphQLite user, migration to v4 should be straightforward:

- Annotations are mostly untouched. The only annotation that is changed is the `@SourceField` annotation.
    - Check your code for every places where you use the `@SourceField` annotation:
    - The "id" attribute has been remove (`@SourceField(id=true)`). Instead, use `@SourceField(outputType="ID")`
    - The "logged", "right" and "failWith" attributes have been removed (`@SourceField(logged=true)`).
      Instead, use the annotations attribute with the same annotations you use for the `@Field` annotation: 
      `@SourceField(annotations={@Logged, @FailWith(null)})`
    - If you use magic property and were creating a getter for every magic property (to put a `@Field` annotation on it),
      you can now replace this getter with a `@MagicField` annotation.
- In GraphQLite v3, the default was to hide a field from the schema if a user has no access to it.
  In GraphQLite v4, the default is to still show this field, but to throw an error if the user makes a query on it
  (this way, the schema is the same for all users). If you want the old mode, use the new
  [`@HideIfUnauthorized` annotation](annotations_reference.md#hideifunauthorized-annotation)
- If you are using the Symfony bundle, the Laravel package or the Universal module, you must also upgrade those to 4.0.
  These package will take care of the wiring for you. Apart for upgrading the packages, you have nothing to do.
- If you are relying on the `SchemaFactory` to bootstrap GraphQLite, you have nothing to do.

On the other hand, if you are a power user and if you are wiring GraphQLite services yourself (without using the 
`SchemaFactory`) or if you implemented custom "TypeMappers", you will need to adapt your code:

- The `FieldsBuilderFactory` is gone. Directly instantiate `FieldsBuilder` in v4.
- The `CompositeTypeMapper` class has no more constructor arguments. Use the `addTypeMapper` method to register 
  type mappers in it.
- The `FieldsBuilder` now accept an extra argument: the `RootTypeMapper` that you need to instantiate accordingly. Take
  a look at the `SchemaFactory` class for an example of proper configuration.
- The `HydratorInterface` and all implementations are gone. When returning an input object from a TypeMapper, the object
  must now implement the `ResolvableMutableInputInterface` (an input object type that contains its own resolver)

Note: we strongly recommend to use the Symfony bundle, the Laravel package, the Universal module or the SchemaManager
to bootstrap GraphQLite. Wiring directly GraphQLite classes (like the `FieldsBuilder`) into your container is not recommended,
as the signature of the constructor of those classes may vary from one minor release to another.
Use the `SchemaManager` instead.
