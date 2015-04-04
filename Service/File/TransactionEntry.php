<?php
    namespace Exploring\FileUtilityBundle\Service\File;

    class TransactionEntry
    {
        const UPLOAD = "upload";

        const REMOVE = "remove";

        /** @var string */
        private $action;

        /** @var mixed */
        private $payload;

        /**
         * @param string $action
         * @param mixed $payload
         */
        function __construct($action, $payload)
        {
            $this->action = $action;
            $this->payload = $payload;
        }

        /**
         * @return string
         */
        public function getAction()
        {
            return $this->action;
        }

        /**
         * @return mixed
         */
        public function getPayload()
        {
            return $this->payload;
        }
    }