<?php
    namespace Exploring\FileUtilityBundle\Utility\NameGenerator;

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class DefaultFilenameGenerator
    {
        private $maskSufix = "masked";

        /**
         * @param UploadedFile $file
         *
         * @return string
         */
        public function generateRandom(UploadedFile $file)
        {
            $ext = $file->guessExtension();

            return md5($file->getClientOriginalName() . time() . rand(1, 9999)) . '.' . $ext;
        }

        /**
         * @param string $filename
         *
         * @return string
         */
        public function createMasked($filename)
        {
            $dotIndex = strrpos($filename, '.');

            if ($dotIndex !== FALSE) {
                return substr($filename, 0, $dotIndex) . '_' . $this->maskSufix . '.' . substr($filename, $dotIndex + 1);
            }
            else {
                return $filename . '_' . $this->maskSufix;
            }
        }

        /**
         * @param string $filename
         * @param int    $width
         * @param int    $height
         *
         * @return string
         */
        public function createScaled($filename, $width, $height)
        {
            $dotIndex = strrpos($filename, '.');

            if ($dotIndex !== FALSE) {
                return sprintf("%s_%dx%d.%s", substr($filename, 0, $dotIndex), $width, $height, substr($filename, $dotIndex + 1));
            }
            else {
                return sprintf("%s_%dx%d", $filename, $width, $height);
            }
        }
    }