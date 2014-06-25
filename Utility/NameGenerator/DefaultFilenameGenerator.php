<?php
    namespace Exploring\FileUtilityBundle\Utility\NameGenerator;

    use Symfony\Component\HttpFoundation\File\File;

    class DefaultFilenameGenerator implements FilenameGeneratorInterface
    {
        const MASK_SUFFIX = "masked";
        const TEMP_PREFIX = "_t_";

        /**
         * {@inheritdoc}
         */
        public function generateRandom(File $file, $temp = false)
        {
            $ext = $file->guessExtension();

            return ($temp ? self::TEMP_PREFIX : "") . md5($file->getFilename() . time() . rand(1, 9999)) . '.' . $ext;
        }

        /**
         * {@inheritdoc}
         */
        public function createMasked($filename)
        {
            $filename = $this->stripTempPrefix($filename);

            $dotIndex = strrpos($filename, '.');

            if ($dotIndex !== false) {
                return substr($filename, 0, $dotIndex) . '_' . self::MASK_SUFFIX . '.' . substr(
                    $filename,
                    $dotIndex + 1
                );
            } else {
                return $filename . '_' . self::MASK_SUFFIX;
            }
        }

        /**
         * {@inheritdoc}
         */
        public function createScaled($filename, $width, $height)
        {
            $filename = $this->stripTempPrefix($filename);

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

        private function stripTempPrefix($filename)
        {
            return strpos($filename, self::TEMP_PREFIX) === 0 ? $filename = substr(
                $filename,
                strlen(self::TEMP_PREFIX)
            ) : $filename;
        }
    }