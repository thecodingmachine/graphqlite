---
id: file-uploads
title: File uploads
sidebar_label: File uploads
---

GraphQL does not support natively the notion of file uploads, but an extension to the GraphQL protocol was proposed
to add support for [multipart requests](https://github.com/jaydenseric/graphql-multipart-request-spec).

GraphQL-Controllers supports this extension through the use of the [Ecodev/graphql-upload third party library](https://github.com/Ecodev/graphql-upload).

Usage
-----

In order to use this, you must first be sure that the ecodev/graphql-upload PSR-15 middleware is part of your middleware pipe.

Note: if you are using our Symfony bundle, this part is taken care of.

Now, you can simply type-hint against the PSR-7 `UploadedFileInterface`:


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

Of course, you need to use a GraphQL client that is compatible with multipart requests.

The [jaydenseric/graphql-multipart-request-spec project contains a list of compatible clients](https://github.com/jaydenseric/graphql-multipart-request-spec#client)
