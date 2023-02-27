<?php
namespace Box;

/**
 * @internal
 */
function autoload($name)
{
    // If the name doesn't start with "Box\", then its not once of our classes.
    if (\substr_compare($name, "Box\\", 0, 3) !== 0) return;

    // Take the "Box\" prefix off.
    $stem = \substr($name, 3);

    // Convert "\" and "_" to path separators.
    $pathified_stem = \str_replace(array("\\", "_"), '/', $stem);

    $path = __DIR__ . "/" . $pathified_stem . ".php";
    if (\is_file($path)) {
        require_once $path;
    }
}

\spl_autoload_register('Box\autoload');
