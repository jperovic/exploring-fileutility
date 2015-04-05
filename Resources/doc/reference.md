Configuration Reference
===

```YAML
exploring_file_utility:
    upload_root: %kernel.root_dir%/../web/uploads
    filename_generator: generator.service.name
    image_engine: gd
    gd:
        quality:
            jpeg: 75
            png: 7
    imagick:
            compression: 1
            quality: 86
    chains:
        scale_and_crop:
            alias: alias1
            steps:
                scale: [640, 0]
                crop: [0, 0, 50, 50]
                ...
        ...
```

> Please note: Directory alias concept has been removed as of v2.0. Directories are now being dicovered automatically.

> **`upload_root`**

> Absolute path to main upload directory. You can you %kernel.X% variables as shown in configuration above.

> **`filename_generator`**

> Service which generates filenames and is used when generating both random or scaled names. You can create your own - please read the "Advanced" [document](/Resources/doc/advances.md).

> **`image_engine`**

> Possible (built-in) values: `gd` or `imagick`. Image engine is tasked with doing actual image manipulations on source files.
> You can build your own.

> **`gd` and `imagick` configuration**

> Some basic quality-related configuration entries.

> **`chains`**

> List of all image operation chains. Each chain can have an optional `alias`, which can be overriden when invoking the chain execution.
> Image chain consists of steps being performed upon source file offered. You can use the following built-in steps:

> > **`clip`**

> > Accepts only one argument: path to file mask to apply during the clipping.

> > **`crop`**

> > Accepts four arguments: `x`, `y`, `width` and `height`

> > **`large_edge`**

> > Accepts two argument: `size` and `enlarge` (boolean). The second one specifies if image can be enlarged to `size` if necessary.

> > **`scale`**

> > Accepts two arguments: `width`, `height` and `enlarge` (boolean)

> You can create your own chain steps as well. Please read the "Advanced" [document](/Resources/doc/advances.md).
