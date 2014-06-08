<?php
    namespace Exploring\FileUtilityBundle\Service\File;

    class TransactionEntry
    {
        const UPLOAD = "upload";

        const REMOVE = "remove";

        /** @var string */
        private $action;

        /** @var mixed */
        private $data;

        function __construct($action, $data)
        {
            $this->action = $action;
            $this->data = $data;
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
        public function getData()
        {
            return $this->data;
        }
    }