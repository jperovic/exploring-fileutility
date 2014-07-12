<?php
    namespace Exploring\FileUtilityBundle\Service\Image\Chains;

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
         * @param array               $registeredChains
         * @param ChainStepInterface[] $chainStepsMapping
         */
        function __construct(array $registeredChains, $chainStepsMapping)
        {
            $this->registeredChains = $registeredChains;

            if ($chainStepsMapping) {
                foreach ($chainStepsMapping as $step) {
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

        public function execute(File $file, $chainName, $saveToAlias = null)
        {
            if (!array_key_exists($chainName, $this->registeredChains)) {
                throw new ImageProcessorException(sprintf('Given chain "%s" was not defined.', $chainName));
            }

            $chain = $this->registeredChains[$chainName];

            if ($saveToAlias == null) {
                if (!$chain['alias']) {
                    throw new ImageProcessorException(sprintf(
                        'Directory alias not specified for chain execution "%s". Either specify it within configuration or in invocation.',
                        $chainName
                    ));
                }

                $saveToAlias = $chain['alias'];
            }

            $stepsDefs = $chain['steps'];

            $wrapper = $this->processor->getFileManager()->save($file, $saveToAlias, true, true);

            foreach ($stepsDefs as $stepName => $stepArgs) {
                if (!array_key_exists($stepName, $this->chainStepsMapping)) {
                    throw new ImageProcessorException(sprintf('Unknown chain step name "%s". Did you tag it properly?', $stepName));
                }

                $wrapper = $this->chainStepsMapping[$stepName]->execute($this->processor, $wrapper, $saveToAlias, $stepArgs);
            }

            return $wrapper;
        }
    }