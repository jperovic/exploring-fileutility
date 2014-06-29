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

Then run:

    php composer.phar update

And register the bundle within `AppKernel.php`

```php
$bundles = array(
    ....
    new \Exploring\FileUtilityBundle\ExploringFileUtilityBundle(),
);
```

You are all set.

Configuration
---

The bundle uses several configutaion entries.

```YAML
exploring_file_utility:
    directories:
        alias1: 'relative_name_of_the_directory'
        alias2: 'another_name_of_the_directory'
    filename_generator: ~
    image_engine: ~
    upload_root: %kernel.root_dir%/../web/uploads
```

The idea behind file management **is not to upload the file to some absolute/relative path but to upload it to the directory alias**.
Think of an alias as a symbolic link (or shortcut). During the runtime the value of alias is appended to `upload_root` forming the absolute path.

For example, given the configuration above, if you would like to upload file to `alias1` that file would end up in:

    %kernel.root_dir%/../web/uploads/relative_name_of_the_directory

By default, filenames are generated via built-in `DefaultFilenameGenerator`. You could change that by overring the `filename_generator` parameter with another **service**.

The bundle comes with some common image operations built-in. The default engine is `gd` but you could use `imagick` as well. You could as well set it to point to your own image engine by specifying the **service** name.

The minimalistic configuration would look something like this:

```YAML
exploring_file_utility:
    directories:
        alias1: 'relative_name_of_the_directory'
        alias2: 'another_name_of_the_directory'
        alias3: 'foo_directory'
        ...
    upload_root: %kernel.root_dir%/../web/uploads
```

Using the file manager
---

You need to use service names `exploring_file_utility.manager` in order to access `FileManager`:

```php
$file = ...; // instance of UploadedFile
$fileManager = $this->get('exploring_file_utility.manager');
$fileManager->save($file, 'alias1');
```

FileManager's operations are transaction based. That means that all changes to file-system will be reverted unless you `commit`:

```php
$fileManager->commit();
```

You can `rollback` changes as well:

```php
$fileManager->rollback();
```

Warning: If no `commit` was ever invoked, all changes will be reverted **automatically**.

Using the image processor
---

The `ImageProccessor` service integrates with `FileManager` to perform basic image manipulations.

Currently, supported operations are: `crop`, `clip`, `scale` and `scaleLargeEdge`

Example:

```php
$file = ...; // instance of UploadedFile

$imageProcessor = $this->get('exploring_file_utility.imageprocessor');

// upload the file 'alias1' directory alias and resize to the width of 400 pixels.
$result = $imageProcessor->scale($file, 'alias1', 400, 0);

$imageProcessor->commit();
```

This will the uploaded file and resize it to width of **400 pixels**, keeping the ratio. More examples are given at the bottom of this document.

Recepies
---

1. Simple file save
---

```php
$fileManager = $this->get('exploring_file_utility.manager');

file = ...; // instance of UploadedFile

// Save the file to directory alias named `alias1`. See the configuration above.
$fileWrapper = $fileManager->save($file, 'alias1');

$filename = $fileWrapper->getFile()->getFilename();
$alias = $fileWrapper->getDirectoryAlias();

// write those into database or anything else

// it is essential to call this after the data was written.
$fileManager->commit();
```

2. Delete the file
---

```php
$fileManager = $this->get('exploring_file_utility.manager');

// filename to delete
$filename = 'some_foo_file.jpg'

$fileManager->remove($filename, 'alias1');

$fileManager->commit();
```

3. Delete the old file and upload the new one
---

```php
$fileManager = $this->get('exploring_file_utility.manager');

// filename to delete
$filename = 'some_foo_file.jpg'

// newly uploaded file
$file = ...; // instance of UploadedFile

// remove the old one
$fileManager->remove($filename, 'alias1');

// save the new one
$fileWrapper = $fileManager->save($file, 'alias1');

$newFilename = $fileWrapper->getFile()->getFilename();

// Write the changes, for example, to database

// finally, commit the changes
$fileManager->commit();
```

4. Create thumbnail image:
---

```php
$imageProcessor = $this->get('exploring_file_utility.imageprocessor');

$file = ...; // instance of UploadedFile

// this will resize the width to 120 pixels, keeping the ratio
$result = $imageProcessor->scale($file, 'alias1', 120);

$imageProcessor->commit();
```

5. Create both small and large version of image:
---

```php
$imageProcessor = $this->get('exploring_file_utility.imageprocessor');

$file = ...; // instance of UploadedFile

// this will resize the width to 120 pixels, keeping the ratio
$thumb = $imageProcessor->scale($file, 'alias1', 120, 0, false, true);

// this will resize the width to 1024 pixels, keeping the ratio
$large = $imageProcessor->scale($file, 'alias1', 1024);

$imageProcessor->commit();
```

Notice the call:

```php
$thumb = $imageProcessor->scale($file, 'alias1', 120, 0, false, true);
```

The last argument `true` means `keepOriginal`.
Since you are creating multiple images out the single one (`$file` variable) it is essential to set this to `true` in order to keep original file intact.


6. Scale the image by it's larger edge (e.g. width for landscape images)
---

```php
$imageProcessor = $this->get('exploring_file_utility.imageprocessor');

$file = ...; // instance of UploadedFile

// if the orientation of the image is landscape it will resize the image using width,
// otherwise it will use the height
$thumb = $imageProcessor->scaleLargeEdge($file, 'alias1', 400);

$imageProcessor->commit();
```

Advanced
===

Creating the custom filename generator
---

Using the custom filename generator is possible but it must implement the following interface:

    Exploring\FileUtilityBundle\Utility\NameGenerator\FilenameGeneratorInterface

Class example:

```php
class FooGenerator implements FilenameGeneratorInterface{
    function createMasked($filename)
    {
        // TODO: Implement createMasked() method.
    }

    function createScaled($filename, $width, $height)
    {
        // TODO: Implement createScaled() method.
    }

    function generateRandom(File $file)
    {
        // TODO: Implement generateRandom() method.
    }
}
```

Then, using the service configuration, make the service definition:

```xml
<parameters>
    <parameter key="foo.generator.class">Acme\DemoBundle\FooGenerator</parameter>
</parameters>

<services>
    <service id="foo.generator" class="%foo.generator.class%">
    </service>
</services>
```

... or if you prefere YAML:

```YAML
parameters:
    foo.generator.class: Acme\DemoBundle\FooGenerator

services:
    foo.generator:
        class: %foo.generator.class%
```

Finally, configure the bundle to use this service by providing it's name:

```YAML
exploring_file_utility:
    ....
    filename_generator: foo.generator
    ....
```





