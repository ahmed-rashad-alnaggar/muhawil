<?php

namespace Alnaggar\Muhawil\Dumpers;

use Alnaggar\Muhawil\Exceptions\InvalidTranslationsException;
use Alnaggar\Muhawil\Exceptions\ResourceDumpException;
use Alnaggar\Muhawil\Exceptions\ResourceNotFoundException;
use Alnaggar\Muhawil\Interfaces\Dumper;

abstract class FileDumper implements Dumper
{
    /**
     * Dump translations into the file at the specified path.
     * 
     * @param array $translations
     * @param string $path
     * @param array $arguments
     * @return static
     */
    public function dump(array $translations, string $path, array $arguments = [])
    {
        $this->ensurePathExists($path);
        $this->validateTranslations($translations);

        $content = $this->format($translations, $arguments);

        $this->dumpFile($content, $path);

        return $this;
    }

    /**
     * Ensure that the path of the specified file to dump the translations into exists.
     * 
     * @param string $path
     * @throws \Alnaggar\Muhawil\Exceptions\ResourceNotFoundException
     * @return void
     */
    protected function ensurePathExists(string $path) : void
    {
        $directory = dirname($path);

        $isDirExists = false;
        $eMessage = "Unable to create directory '{$directory}' to dump the file into";

        try {
            $isDirExists = is_dir($directory) || @mkdir($directory, 0777, true);
        } catch (\Exception $e) {
            $eMessage .= ": {$e->getMessage()}";
        } finally {
            if (! $isDirExists) {
                throw new ResourceNotFoundException($eMessage);
            }
        }
    }

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

            if (! is_string($value)) {
                throw new InvalidTranslationsException('Translation must be a non-nested associative array of strings.');
            }
        }
    }

    /**
     * Formats the translations into their storable format.
     * 
     * @param array $translations
     * @param array $arguments
     * @return string
     */
    abstract public function format(array $translations, array $arguments = []) : string;

    /**
     * Dump translations formated string into the specified file.
     * 
     * @param string $content
     * @param string $path
     * @throws \Alnaggar\Muhawil\Exceptions\ResourceDumpException
     * @return void
     */
    protected function dumpFile(string $content, string $path) : void
    {
        $isDumped = false;
        $eMessage = "Cannot write to the file located at '{$path}'";

        try {
            $isDumped = file_put_contents($path, $content);
        } catch (\Exception $e) {
            $eMessage .= ": {$e->getMessage()}";
        } finally {
            if (! $isDumped) {
                throw new ResourceDumpException($eMessage);
            }
        }
    }
}
