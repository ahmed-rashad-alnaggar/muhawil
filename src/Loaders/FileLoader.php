<?php

namespace Alnaggar\Muhawil\Loaders;

use Alnaggar\Muhawil\Exceptions\ResourceLoadException;
use Alnaggar\Muhawil\Exceptions\ResourceNotFoundException;
use Alnaggar\Muhawil\Interfaces\Loader;
use Alnaggar\Muhawil\Traits\HasMissingTranslationValueHandler;

abstract class FileLoader implements Loader
{
    use HasMissingTranslationValueHandler;

    /**
     * Load translations from the file at the specified path.
     * 
     * @param string $path
     * @return array
     */
    public function load(string $path): array
    {
        $file = $this->loadFile($path);

        $translations = $this->parse($file);

        $this->handleMissingTranslationsValues($translations, $path);

        return $translations;
    }

    /**
     * Load file into suitable format.
     * 
     * @param string $path
     * @throws \Alnaggar\Muhawil\Exceptions\ResourceLoadException
     * @throws \Alnaggar\Muhawil\Exceptions\ResourceNotFoundException
     * @return \SplFileObject
     */
    protected function loadFile(string $path)
    {
        if (!is_file($path)) {
            throw new ResourceNotFoundException("File does not exist at path '{$path}'.");
        }

        try {
            return new \SplFileObject($path, 'r');
        } catch (\RuntimeException $e) {
            throw new ResourceLoadException("Cannot open/read the file at '{$path}': {$e->getMessage()}.", $e->getCode(), $e);
        }
    }

    /**
     * Parse the file content into translations.
     * 
     * @param mixed $resource
     * @return array
     */
    abstract protected function parse($resource): array;

    /**
     * Handle missing translation values.
     * 
     * @param array $translations
     * @param string $path
     * @return void
     */
    protected function handleMissingTranslationsValues(array &$translations, string $path): void
    {
        array_walk_recursive($translations, function (&$value, string $key) use ($path) {
            if ($value === '' || is_null($value)) {
                if (is_null($this->handleMissingTranslationValueCallback)) {
                    $value = $key;
                } else {
                    $value = call_user_func($this->handleMissingTranslationValueCallback, $key, $path);
                }
            }
        });
    }
}
