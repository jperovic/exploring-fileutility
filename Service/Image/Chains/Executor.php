<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains;

    use Exploring\FileUtilityBundle\Data\ImageDescriptor;
    use Exploring\FileUtilityBundle\Service\Image\Chains\Steps\ChainStepInterface;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessor;
    use Exploring\FileUtilityBundle\Service\Image\ImageProcessorException;
    use Symfony\Component\HttpFoundation\File\File;

    class Executor
    {
        /** @var ImageProcessor */
        private $processor;

        private $registeredChains;

        /** @var ChainStepInterface[] */
        private $chainStepsMapping;

        /**
         * @param array                $registeredChains
         * @param ChainStepInterface[] $chainStepsMapping
         */
        function __construct(array $registeredChains, $chainStepsMapping)
        {
            $this->registeredChains = $registeredChains;

            if ( $chainStepsMapping ) {
                foreach ( $chainStepsMapping as $step ) {
                    $this->chainStepsMapping[$step->getName()] = $step;
                }
            }
        }

        /**
         * @param ImageProcessor $processor
         */
        public function setProcessor($processor)
        {
            $this->processor = $processor;
        }

        /**
         * @param File        $file
         * @param string      $chainName
         * @param string|null $directory
         *
         * @return ImageDescriptor
         * @throws ImageProcessorException
         */
        public function execute(File $file, $chainName, $directory = NULL)
        {
            if ( !array_key_exists($chainName, $this->registeredChains) ) {
                throw new ImageProcessorException("Given chain \"$chainName\" was not defined.");
            }

            $chain = $this->registeredChains[$chainName];

            if ( $directory == NULL ) {
                if ( !$chain['directory'] ) {
                    throw new ImageProcessorException("Directory not specified for chain execution \"$chainName\". Either specify it within configuration or in invocation.");
                }

                $directory = $chain['directory'];
            }

            $stepsDefs = $chain['steps'];

            $wrapper = $this->processor->getFileManager()->save($file, $directory, TRUE, TRUE);

            foreach ( $stepsDefs as $stepName => $stepArgs ) {
                if ( !array_key_exists($stepName, $this->chainStepsMapping) ) {
                    throw new ImageProcessorException("Unknown chain step name \"$stepName\". Did you tag it properly?");
                }

                $wrapper = $this->chainStepsMapping[$stepName]->execute($this->processor, $wrapper, $directory, $stepArgs);
            }

            return $wrapper;
        }
    }