<?php

namespace Alnaggar\Muhawil\Dumpers;

class PhpFileDumper extends FileDumper
{
    /**
     * Formats translations into a storable PHP representation.
     *
     * @param array $translations
     * @return string
     */
    public function format(array $translations): string
    {
        return "<?php\n\nreturn " . $this->formatArray($translations, 1) . ";\n";
    }

    /**
     * Recursively formats an array into the short PHP array syntax with proper indentation.
     *
     * @param array $array
     * @param int $indentLevel
     * @return string
     */
    protected function formatArray(array $array, int $indentLevel): string
    {
        $indent = str_repeat("\t", $indentLevel);
        $output = "[\n";

        foreach ($array as $key => $value) {
            $formattedKey = "'{$key}'";

            if (is_array($value)) {
                $formattedValue = $this->formatArray($value, $indentLevel + 1);
            } else {
                $escapedValue = str_replace("'", "\\'", $value);
                $formattedValue = "'{$escapedValue}'";
            }

            $output .= "{$indent}{$formattedKey} => {$formattedValue},\n";
        }

        $output .= str_repeat("\t", $indentLevel - 1) . "]";

        return $output;
    }
}
