<?php

namespace Alnaggar\Muhawil\Traits;

trait HasMissingTranslationValueHandler
{
    /**
     * The callback that is responsible for handling missing translation values.
     * 
     * @var callable|null
     */
    protected $handleMissingTranslationValueCallback;

    /**
     * Handle missing translation values.
     * 
     * @param array $translations
     * @return void
     */
    protected function handleMissingTranslationsValues(array &$translations): void
    {
        array_walk_recursive($translations, function (&$value, string $key) {
            if ($value === '' || is_null($value)) {
                if (is_null($this->handleMissingTranslationValueCallback)) {
                    $value = $key;
                } else {
                    $value = call_user_func($this->handleMissingTranslationValueCallback, $key);
                }
            }
        });
    }

    /**
     * Set the callback that is responsible for handling missing translation values.
     * 
     * @param callable|null $callback
     * @return static
     */
    public function setMissingTranslationValueCallback(?callable $callback)
    {
        $this->handleMissingValueCallback = $callback;

        return $this;
    }
}
