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

... or if you prefer YAML:

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

Creating custom image chain step
---

In order to create image chain step you need to implement the interface:

`Exploring\FileUtilityBundle\Service\Image\Chains\Steps\ChainStepInterface`

```php
class FooStep implements ChainStepInterface {

    public function execute(ImageProcessor $processor, FileWrapper $fileWrapper, $saveToAlias, array $arguments = array()){
        # Your logic here
    }

    public function getName(){
        return "some_name"; Your step's name, used in configuration.
    }

}
```

You will also need to to tag this class as your service with `exploring_file_utility.image_chain_step`:

```xml
<parameters>
    <parameter key="foo.step.class">Acme\DemoBundle\FooStep</parameter>
</parameters>

<services>
    <service id="foo.step" class="%foo.step.class%">
        <tag name="exploring_file_utility.image_chain_step"/>
    </service>
</services>
```

.... or if you prefer YAML:

```YAML
parameters:
    foo.step.class: Acme\DemoBundle\FooStep

services:
    foo.step:
        class: %foo.step.class%
        tags:
            -  { name: exploring_file_utility.image_chain_step }
```

After this, you just need to include your step in configuration. For example:

```YAML
exploring_file_utility:
    ....
    chains:
        foo_chain:
            alias: some_alias
            steps:
                some_name: [arguments, go, here]
```