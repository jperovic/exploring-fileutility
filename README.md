FileUtilityBundle
=============

This bundle aims to help management of files that were sent thought the form.

Symfony2 does a great job with converting raw file data into UploadedFile object and the goal of this bundle is to further extend it.

Installation (via composer)
---

To install FileUtilityBundle with Composer just add the following to your composer.json file:

    {
        // ...
        require: {
            "exploring/fileutility": "dev-master"
        }
    }

> Please replace the "dev-master" with some concrete release.

Then run:

    php composer.phar update

And register the bundle within `AppKernel.php`

```php
$bundles = array(
    ....
    new Exploring\FileUtilityBundle\ExploringFileUtilityBundle(),
);
```

You are all set.

Configuration
---

The bundle uses several configutaion entries. However, the minimalistic configuation should look like:

```YAML
exploring_file_utility:
    upload_root: %kernel.root_dir%/../web/uploads
```

All file are uploaded to a specific subdirectory of `upload_root` path. You can have as many subdirectories as you like.

You can read full [configuration reference here](Resources/doc/reference.md).

> Notice: Concept of directory "alias" has been removed in v2.0. Directories are now auto discovered during the warm up process. If you instantiate FileManager manually, you can still set available directories.

> Advice: Either keep your upload root diretory out of web public directory or ensure proper access rights to file stored there in order to avoid execution of files uploaded.

Basic usage
---

You need to use service names `exploring_file_utility.manager` in order to gain access to `FileManager`:

```php
$file = ...; // instance of UploadedFile
$fileManager = $this->get('exploring_file_utility.manager');
$fileDescriptor = $fileManager->save($file, 'foo');
$fileManager->commit();
```

The example above takes some arbitrary file and, given the configuration above, uploads it to `/path/to/symfonyapp/web/uploads/foo`.

FileManager's operations are transaction based. That means that any changes made to the file-system will be reverted back unless you call`commit()`:

You can invoke `rollback()` to revert any changes as well at any time:

```php
$fileManager->rollback();
```

Examples
---

Various examples, both simple and advances, can be found in [example document](Resources/doc/examples.md)

Image manipulations
---

This bundle comes with some image manipulations operations built-in. Complete documentation on those can be found [here](Resources/doc/images.md)