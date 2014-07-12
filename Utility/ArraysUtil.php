<?php
    namespace Exploring\FileUtilityBundle\Utility;

    class ArraysUtil
    {
        /**
         * @param array $array
         * @param array $keyPositions
         * @param array $defaultValues
         *
         * @return array
         */
        public static function transformArrayToAssociative(array $array, $keyPositions, $defaultValues)
        {
            $assoc = array();

            foreach ($keyPositions as $k => $v) {
                $assoc[$v] = array_key_exists($k, $array) ? $array[$k] :
                    (array_key_exists($k, $defaultValues) ? $defaultValues[$k] : null);
            }

            return $assoc;
        }
    }