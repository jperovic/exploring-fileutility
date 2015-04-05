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

The last argument `true` means `keepSourceFile`.
Since you are creating multiple images out the single one (`$file` variable) it is essential to set this to `true` in order to keep file source intact.


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