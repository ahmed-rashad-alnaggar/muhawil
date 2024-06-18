<?php

namespace Alnaggar\Muhawil\Dumpers;

use Alnaggar\Muhawil\Exceptions\InvalidTranslationsException;

class PhpFileDumper extends FileDumper
{
    /**
     * Check translations validity.
     * 
     * @param array $translations
     * @throws \Alnaggar\Muhawil\Exceptions\InvalidTranslationsException
     * @return void
     */
    protected function validateTranslations(array $translations) : void
    {
        foreach ($translations as $key => $value) {
            if ($key === '') {
                throw new InvalidTranslationsException('Translation keys must not be empty.');
            }

            if (is_array($value)) {
                $this->validateTranslations($value);
            } elseif (! is_string($value)) {
                throw new InvalidTranslationsException('Translations must be nested or non-nested associative arrays of strings.');
            }
        }
    }

    /**
     * Formats translations into a storable PHP representation.
     *
     * @param array $translations
     * @param array $arguments
     * @return string
     */
    public function format(array $translations, array $arguments = []) : string
    {
        return "<?php" . PHP_EOL . PHP_EOL . "return " . var_export($translations, true) . ";" . PHP_EOL;
    }
}
