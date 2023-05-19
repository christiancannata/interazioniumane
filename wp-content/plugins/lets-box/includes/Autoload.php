<?php
/**
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

function autoload($className)
{
    $classPath = explode('\\', $className);
    if ('TheLion' != $classPath[0]) {
        return;
    }
    if ('LetsBox' != $classPath[1]) {
        return;
    }
    $classPath = array_slice($classPath, 2, 3);

    $filePath = dirname(__FILE__).'/'.implode('/', $classPath).'.php';
    if (file_exists($filePath)) {
        require_once $filePath;
    }
}

spl_autoload_register(__NAMESPACE__.'\autoload');