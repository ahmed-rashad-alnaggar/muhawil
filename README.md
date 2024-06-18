# Muhawil - PHP Package For Loading And Dumping Translations

![I Stand With Palestine Badge](./images/PalestineBadge.svg)

![I Stand With Palestine Banner](./images/PalestineBanner.svg)

Muhawil is a PHP package designed to simplify the process of loading and dumping translations effortlessly. The name "muhawil" is derived from the Arabic word **مُحول**, meaning transformer or adapter.

The package natively supports 6 types of files:

- [JSON](#json)
- [MO (Machine Object)](#mo)
- [PHP](#php)
- [PO (Portable Object)](#po)
- [XLIFF (XML Localization Interchange File Format)](#xliff)
- [YAML](#yaml)

## Installation

Install the package using Composer:

```bash
composer require alnaggar/muhawil
```

## File Loaders

All files loaders extends `Alnaggar\Muhawil\Loaders\FileLoader` class, which provides the method `load (string $path)` to load the transaltions from the provided file at that path.

```php
# Example of loading translations from a PHP file.

use Alnaggar\Muhawil\Loaders\PhpFileLoader;

$loader = new PhpFileLoader;

$loader->load('path/to/translations/file.php');
```

## File Dumpers

All files dumpers extends `Alnaggar\Muhawil\Dumpers\FileDumper` class, which provides the method `dump (array $translations, string $path, array $arguments [])` to dump the transaltions into the provided file at that path with the usage of the passed arguments if needed.

```php
// Example of dumping translations into a PHP file.

use Alnaggar\Muhawil\Dumpers\PhpFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new PhpFileDumper;

$dumper->dump($translations, 'path/to/translations/file.php');
```

## JSON

Load JSON translations using `Alnaggar\Muhawil\Loaders\JsonFileLoader` class.

Dump JSON translations using `Alnaggar\Muhawil\Dumpers\JsonFileDumper` class, with an optional `flags` argument to be passed to the `json_encode` function.

```php
// Example of dumping translations into a JSON file.

use Alnaggar\Muhawil\Dumpers\JsonFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new JsonFileDumper;

$dumper->dump($translations, 'path/to/translations/file.json', ['flags' => JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE]);
```

## MO

Load MO translations using `Alnaggar\Muhawil\Loaders\MoFileLoader` class.

Dump MO translations using `Alnaggar\Muhawil\Dumpers\MoFileDumper` class, with an optional `metadata` argument to be included in the MO file like `Language` and `Plural-Forms`.

```php
// Example of dumping translations into an MO file.

use Alnaggar\Muhawil\Dumpers\MoFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new MoFileDumper;

$dumper->dump($translations, 'path/to/translations/file.mo', ['metadata' => [
    'Language' => 'ar'
    'Plural-Forms' => 'nplurals=6; plural=n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n % 100 >= 3 && n % 100 <= 10 ? 3 : n % 100 >= 11 ? 4 : 5;'
]]);
```

## PHP

Load PHP translations using `Alnaggar\Muhawil\Loaders\PhpFileLoader` class.

Dump PHP translations using `Alnaggar\Muhawil\Dumpers\PhpFileDumper` class.

## PO

Load PO translations using `Alnaggar\Muhawil\Loaders\PoFileLoader` class.

Dump PO translations using `Alnaggar\Muhawil\Dumpers\PoFileDumper` class, with an optional `metadata` argument to be included in the PO file header like `Language` and `Plural-Forms`.

```php
// Example of dumping translations into a PO file.

use Alnaggar\Muhawil\Dumpers\PoFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new PoFileDumper;

$dumper->dump($translations, 'path/to/translations/file.po', ['metadata' => [
    'Language' => 'ar'
    'Plural-Forms' => 'nplurals=6; plural=n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n % 100 >= 3 && n % 100 <= 10 ? 3 : n % 100 >= 11 ? 4 : 5;'
]]);
```

## XLIFF

Load XLIFF translations using `Alnaggar\Muhawil\Loaders\XliffFileLoader` class.

Dump XLIFF translations using `Alnaggar\Muhawil\Dumpers\XliffFileDumper` class, with 2 required arguments `source_locale` and `target_locale` and one optional argument `legacy` which indicates to dump translation into XLIFF 1.2 version rather than 2.0 version.

```php
// Example of dumping translations into an XLIFF file.

use Alnaggar\Muhawil\Dumpers\XliffFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new XliffFileDumper;

$dumper->dump($translations, 'path/to/translations/file.xliff', ['source_locale' => 'en', 'target_locale' => 'ar', 'legacy' => true]);
```

## YAML

Load YAML translations using `Alnaggar\Muhawil\Loaders\YamlFileLoader` class.

Dump YAML translations using `Alnaggar\Muhawil\Dumpers\YamlFileDumper` class, with an optional argument `dry` which indicates to determine and generate anchors and aliases for similar **mappings**.

```php
// Example of dumping translations into a YAML file.

use Alnaggar\Muhawil\Dumpers\YamlFileDumper;

$translations = [
    'welcome_message' => 'Welcome to our application!',
    'greeting' => 'Hello, world!',
    'farewell' => 'Goodbye, see you soon!',
    'errors' => [
      'not_found' => 'The requested resource was not found.',
      'internal_error' => 'An internal server error occurred. Please try again later.'
    ],
    'common_actions' => [
      'save' => 'Save',
      'cancel' => 'Cancel',
      'delete' => 'Delete'
    ],
    'user' => [
      'profile' => 'Profile',
      'settings' => 'Settings',
      'logout' => 'Logout',
      'actions' => [
        'create' => 'Create',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete'
      ]
    ]
];

$dumper = new YamlFileDumper;

$dumper->dump($translations, 'path/to/translations/file.yaml', ['dry' => true]);
```

Both the loader and the dumper supports only simple YAML structure which is mappings, nested-mappings and scalar values.

## Contributing

If you find any issues or have suggestions for improvements, feel free to open an issue or submit a pull request on the GitHub repository.

## Credits

- Palestine banner and badge by [Safouene1](https://github.com/Safouene1/support-palestine-banner).

## License

Muhawil is open-sourced software licensed under the [MIT license](LICENSE).
