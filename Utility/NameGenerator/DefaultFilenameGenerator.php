<?php
    namespace Exploring\FileUtilityBundle\Utility\NameGenerator;

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class DefaultFilenameGenerator implements FilenameGeneratorInterface
    {
        private $maskSuffix = "masked";

        /**
         * {@inheritdoc}
         */
        public function generateRandom(UploadedFile $file)
        {
            $ext = $file->guessExtension();

            return md5($file->getClientOriginalName() . time() . rand(1, 9999)) . '.' . $ext;
        }

        /**
         * {@inheritdoc}
         */
        public function createMasked($filename)
        {
            $dotIndex = strrpos($filename, '.');

            if ($dotIndex !== false) {
                return substr($filename, 0, $dotIndex) . '_' . $this->maskSuffix . '.' . substr(
                    $filename,
                    $dotIndex + 1
                );
            } else {
                return $filename . '_' . $this->maskSuffix;
            }
        }

        /**
         * {@inheritdoc}
         */
        public function createScaled($filename, $width, $height)
        {
            $dotIndex = strrpos($filename, '.');

            if ($dotIndex !== false) {
                return sprintf(
                    "%s_%dx%d.%s",
                    substr($filename, 0, $dotIndex),
                    $width,
                    $height,
                    substr($filename, $dotIndex + 1)
                );
            } else {
                return sprintf("%s_%dx%d", $filename, $width, $height);
            }
        }
    }