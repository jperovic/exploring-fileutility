Configuration Reference
===

```YAML
exploring_file_utility:
    directories:
        alias1: 'relative_name_of_the_directory'
        alias2: 'another_name_of_the_directory'
        alias3: 'foo_directory'
        ...
    upload_root: %kernel.root_dir%/../web/uploads
    filename_generator: ~
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

> **`exploring_file_utility.directories`**

> List of all aliases defined. Value of alias is appended onto `upload_root` path, forming the absolute path.

> **`upload_root`**

> Absolute path to main upload directory. You can you %kernel.X% variables as shown in configuration above.

> **`filename_generator`**

> Service which generates unique filenames. You can define your own - please read the "Advanced" section of [readme document](/README.md).

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

> You can create your own chain steps as well. Please read the "Advanced" section of [readme document](/README.md).
