Image manipulation
===

The bundle support basic image manipulations: `SCALE`, `CROP` and `MASK`. There are two implementations: `gd` and `imagick`. 

You can configure which implementation you would like to use in `config.yml`. See [full reference](Resources/doc/reference.md).

1. Create thumbnail image:
---

```php
$imageProcessor = $this->get('exploring_file_utility.imageprocessor');

$file = ...; // instance of UploadedFile

// this will resize the width to 120 pixels, keeping the ratio
$result = $imageProcessor->scale($file, 'subdirectory', 120);

$imageProcessor->commit();
```

2. Create both small and large version of image:
---

```php
$imageProcessor = $this->get('exploring_file_utility.imageprocessor');

$file = ...; // instance of UploadedFile

// this will resize the width to 120 pixels, keeping the ratio
// save the thumb to `subdirectory`
$thumb = $imageProcessor->scale($file, 'subdirectory', 120, 0, false, true);

// this will resize the width to 1024 pixels, keeping the ratio
// also going to save that to `subdirectory`
$large = $imageProcessor->scale($file, 'subdirectory', 1024);

$imageProcessor->commit();
```

Notice the call:

```php
$thumb = $imageProcessor->scale($file, 'subdirectory', 120, 0, false, true);
```

The last argument `true` means `keepSourceFile`.
Since you are creating multiple images out the single one (`$file` variable) it is essential to set this to `true` in order to keep file source intact.


3. Scale the image by it's larger edge (e.g. width for landscape images)
---

```php
$imageProcessor = $this->get('exploring_file_utility.imageprocessor');

$file = ...; // instance of UploadedFile

// if the orientation of the image is landscape it will resize the image using width,
// otherwise it will use the height
$thumb = $imageProcessor->scaleLargeEdge($file, 'subdirectory', 400);

$imageProcessor->commit();
```