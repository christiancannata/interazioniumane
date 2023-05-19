<?php
/**
 * @author      Floris de Leeuw
 * @copyright   (C)Copyright 2020 wpcloudplugins.com
 */

namespace Box\Model\Zip;

use Box\Model\Model;

class Zip extends Model implements ZipInterface
{
    const URI = 'https://api.box.com/2.0/zip_downloads/';

    protected $type = 'zip';
    protected $downloadUrl;
    protected $statusUrl;
    protected $expiresAt;
    protected $nameConflicts;

    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }

    public function setDownloadUrl($downloadUrl = null)
    {
        $this->downloadUrl = $downloadUrl;

        return $this;
    }

    public function getStatusUrl()
    {
        return $this->statusUrl;
    }

    public function setStatusUrl($statusUrl = null)
    {
        $this->statusUrl = $statusUrl;

        return $this;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    public function setExpiresAt($expiresAt = null)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getNameConflicts()
    {
        return $this->nameConflicts;
    }

    public function setNameConflicts($nameConflicts = null)
    {
        $this->nameConflicts = $nameConflicts;

        return $this;
    }
}
