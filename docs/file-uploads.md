---
id: file-uploads
title: File uploads
sidebar_label: File uploads
---

GraphQL does not support natively the notion of file uploads, but an extension to the GraphQL protocol was proposed
to add support for [multipart requests](https://github.com/jaydenseric/graphql-multipart-request-spec).

## Installation

GraphQLite supports this extension through the use of the [Ecodev/graphql-upload](https://github.com/Ecodev/graphql-upload) library.

You must start by installing this package:

```console
$ composer require ecodev/graphql-upload
```

## If you are using the Symfony bundle

If you are using our Symfony bundle, the file upload middleware is managed by the bundle. You have nothing to do
and can start using it right away.

## If you are using a PSR-15 compatible framework

In order to use this, you must first be sure that the `ecodev/graphql-upload` PSR-15 middleware is part of your middleware pipe.

Simply add `GraphQL\Upload\UploadMiddleware` to your middleware pipe.

## If you are using another framework not compatible with PSR-15

Please check the Ecodev/graphql-upload library [documentation](https://github.com/Ecodev/graphql-upload)
for more information on how to integrate it in your framework.

## Usage

To handle an uploaded file, you type-hint against the PSR-7 `UploadedFileInterface`:

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
class MyController
{
    #[Mutation]
    public function saveDocument(string $name, UploadedFileInterface $file): Document
    {
        // Some code that saves the document.
        $file->moveTo($someDir);
    }
}
```
<!--PHP 7+-->
```php
class MyController
{
    /**
     * @Mutation
     */
    public function saveDocument(string $name, UploadedFileInterface $file): Document
    {
        // Some code that saves the document.
        $file->moveTo($someDir);
    }
}
```
<!--END_DOCUSAURUS_CODE_TABS-->

Of course, you need to use a GraphQL client that is compatible with multipart requests. See [jaydenseric/graphql-multipart-request-spec](https://github.com/jaydenseric/graphql-multipart-request-spec#client) for a list of compatible clients.

The GraphQL client must send the file using the Upload type.

```
mutation upload($file: Upload!) {
    upload(file: $file)
}
```

