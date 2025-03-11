<?php

namespace Alnaggar\Muhawil\Dumpers;

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
     * @return static
     */
    public function dump(array $translations, string $path)
    {
        $this->ensurePathExists($path);

        $content = $this->format($translations, ...array_slice(func_get_args(), 2));

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
    protected function ensurePathExists(string $path): void
    {
        $directory = dirname($path);

        $isDirExists = false;
        $eMessage = "Unable to create directory '{$directory}' to dump the file into";

        try {
            $isDirExists = is_dir($directory) || @mkdir($directory, 0777, true);
        } catch (\Exception $e) {
            $eMessage .= ": {$e->getMessage()}";
        } finally {
            if (!$isDirExists) {
                throw new ResourceNotFoundException($eMessage);
            }
        }
    }

    /**
     * Formats the translations into their storable format.
     * 
     * @param array $translations
     * @return string
     */
    abstract public function format(array $translations): string;

    /**
     * Dump translations formated string into the specified file.
     * 
     * @param string $content
     * @param string $path
     * @throws \Alnaggar\Muhawil\Exceptions\ResourceDumpException
     * @return void
     */
    protected function dumpFile(string $content, string $path): void
    {
        $isDumped = false;
        $eMessage = "Cannot write to the file located at '{$path}'";

        try {
            $isDumped = file_put_contents($path, $content);
        } catch (\Exception $e) {
            $eMessage .= ": {$e->getMessage()}";
        } finally {
            if ($isDumped === false) {
                throw new ResourceDumpException($eMessage);
            }
        }
    }
}
