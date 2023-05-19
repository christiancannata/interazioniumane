<?php
/**
 * @author      Floris de Leeuw
 * @copyright   (C)Copyright 2020 wpcloudplugins.com
 */

namespace Box\Model\Zip;

interface ZipInterface
{
    public function __construct($options = null);

    public function mapBoxToClass($aData);

    public function getDownloadUrl();

    public function getStatusUrl();

    public function getExpiresAt();

    public function getNameConflicts();
}
