<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

if (version_compare(PHP_VERSION, '8.3.0') < 0) {
    // TYPO3 brings this dependency.
    // It is detected as shadow dependency due to usage of `Override` attributes.
    // But code still works prior PHP 8.3, the attribute is just ignored, so we won't add it as a dependency to our project.
    $config->ignoreErrorsOnPackage('symfony/polyfill-php83', [ErrorType::SHADOW_DEPENDENCY]);
}

return $config
    // We added it to suggests.
    // This is not a hard dependency, it is only needed to convert existing XML to PHP files.
    ->ignoreErrorsOnExtension('ext-simplexml', [ErrorType::SHADOW_DEPENDENCY])
;
