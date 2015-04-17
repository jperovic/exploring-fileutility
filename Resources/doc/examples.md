Examples
===

1. Simple file save
---

```php
$fileManager = $this->get('exploring_file_utility.manager');

file = ...; // instance of UploadedFile

// Save the file to subdirectory named `foo`.
$fileDescriptor = $fileManager->save($file, 'foo');

$filename = $fileDescriptor->getFileName(); // filename of newly uploaded file
$directory = $fileDescriptor->getDirectory(); // directory in which file was uploaded, in this case "foo"

// persist those into database or something similar

// it is essential to call this after the data was written.
$fileManager->commit();
```

2. Delete the file
---

```php
$fileManager = $this->get('exploring_file_utility.manager');

// filename to delete
$filename = 'some_foo_file.jpg'

// Will remove `/path/to/symfonyapp/web/uploads/foo/some_foo_file.jpg`
$fileManager->remove($filename, 'foo');

// Again, commit is essential
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
$fileManager->remove($filename, 'foo');

// save the new one
$fileWrapper = $fileManager->save($file, 'foo');

$newFilename = $fileWrapper->getFilename();

// Write the changes, for example, to database

// finally, commit the changes
$fileManager->commit();
```

4. Using constants instead of string for directory names
---

> Notice: This is Symfony2 cache warmup feature. It is not available without using cache warm-up.

> Notice: This feature has been added in v2.0.

If you have created the directory and ran cache warm up, `FileManager` has become aware of your directory.

```php
use Exploring\FileUtilityBundle\Service\Cache\FileUtilityDirectory;

$fileManager = $this->get('exploring_file_utility.manager');

file = ...; // instance of UploadedFile

// Save the file to subdirectory named `foo`.
$fileDescriptor = $fileManager->save($file, FileUtilityDirectory::FOO);

// it is essential to call this after the data was written.
$fileManager->commit();
```

Constanst are generated during the cache warm up process. For each subdirectory of `upload_root` a contant will be defined. Naming convention is simple:

| Directory name | Constant |
| ------------- |-------------|
| foo_dir | FOO_DIR |
| mySubDirectory | MY_SUB_DIRECTORY |
| superSecret007Stuff | SUPER_SECRET_007_STUFF |

Using constants will greatly improve code readability and testing.




