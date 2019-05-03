---
id: migrating
title: Migrating
sidebar_label: Migrating
---

## Migrating from v3.0 to v4.0

If you are a "regular" GraphQLite user, migration to v4 should be straightforward:

- Annotations are untouched so you do not need to edit your code (TODO: deprecate "id" parameter in annotations)
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

