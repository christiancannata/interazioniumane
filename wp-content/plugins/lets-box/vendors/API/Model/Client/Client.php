<?php

/**
 * @author      Chance Garcia
 * @copyright   (C)Copyright 2013 chancegarcia.com
 */

namespace Box\Model\Client;

use Box\Exception\Exception;
use Box\Model\Collaboration\Collaboration;
use Box\Model\Connection\Connection;
use Box\Model\Connection\ConnectionInterface;
use Box\Model\Connection\Token\Token;
use Box\Model\Connection\Token\TokenInterface;
use Box\Model\Event\Event;
use Box\Model\File\File;
use Box\Model\File\FileInterface;
use Box\Model\Folder\Folder;
use Box\Model\Folder\FolderInterface;
use Box\Model\Group\Group;
use Box\Model\Group\GroupInterface;
use Box\Model\Model;
use Box\Model\User\User;
use Box\Model\User\UserInterface;
use Box\Model\Zip\Zip;

/**
 * Class Client.
 */
class Client extends Model
{
    public const AUTH_URI = 'https://account.box.com/api/oauth2/authorize';
    public const TOKEN_URI = 'https://api.box.com/oauth2/token';
    public const REVOKE_URI = 'https://api.box.com/oauth2/revoke';
    public const SEARCH_URI = 'https://api.box.com/2.0/search';
    public $hasnewToken = false;

    protected $state;

    /**
     * @var Connection|ConnectionInterface
     */
    protected $connection;

    /**
     * @var array of folder items indexed by the folder ID
     *
     * @internal should just be an array of any folder known/retrieved by the client. does not need to be recursive since folders know their parents and items
     */
    protected $folders;
    protected $files;

    /**
     * @var array of collaborations
     */
    protected $collaborations;

    /**
     * @var Folder
     */
    protected $root;

    /**
     * @var Token|TokenInterface
     */
    protected $token;
    protected $authorizationCode;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $deviceId;
    protected $deviceName;

    /**
     * allow for class injection by using an interface for these classes.
     */
    protected $folderClass = 'Box\Model\Folder\Folder';
    protected $fileClass = 'Box\Model\File\File';
    protected $connectionClass = 'Box\Model\Connection\Connection';
    protected $tokenClass = 'Box\Model\Connection\Token\Token';
    protected $collaborationClass = 'Box\Model\Collaboration\Collaboration';
    protected $zipClass = 'Box\Model\Zip\Zip';
    protected $userClass = 'Box\Model\User\User';
    protected $groupClass = 'Box\Model\Group\Group';
    protected $eventClass = 'Box\Model\Event\Event';

    /**
     * @param mixed $options
     *
     * @return File|FileInterface
     */
    public function getNewEvent($options = null)
    {
        return $this->getNewClass('Event', $options);
    }

    /**
     * @param mixed $options
     *
     * @return FileInterface|File
     */
    public function getNewFile($options = null)
    {
        return $this->getNewClass('File', $options);
    }

    /**
     * @param mixed $options
     *
     * @return FolderInterface|Folder
     */
    public function getNewFolder($options = null)
    {
        return $this->getNewClass('Folder', $options);
    }

    /**
     * @param mixed $options
     *
     * @return \Box\Model\User\User|\Box\Model\User\UserInterface
     */
    public function getNewUser($options = null)
    {
        return $this->getNewClass('User', $options);
    }

    /**
     * @param mixed $options
     *
     * @return \Box\Model\Group\Group|\Box\Model\Group\GroupInterface
     */
    public function getNewGroup($options = null)
    {
        return $this->getNewClass('Group', $options);
    }

    /**
     * @param mixed $options
     *
     * @return \Box\Model\Collaboration\Collaboration|\Box\Model\Collaboration\CollaborationInterface
     */
    public function getNewCollaboration($options = null)
    {
        return $this->getNewClass('Collaboration', $options);
    }

    /**
     * @param mixed $options
     *
     * @return \Box\Model\Zip\Zip|\Box\Model\Zip\ZipInterface
     */
    public function getNewZip($options = null)
    {
        return $this->getNewClass('Zip', $options);
    }

    /**
     * @param int  $id       use 0 for returning all folders
     * @param bool $retrieve if no folder is found, attempt to retrieve from box
     *
     * @return null|array|Folder returns null if no such folder exists and retrieve is false
     */
    public function getFolder($id = 0, $retrieve = true)
    {
        $folders = $this->getFolders($retrieve);

        if (0 == $id) {
            return $folders;
        }

        if (!array_key_exists($id, $folders)) {
            if (!$retrieve) {
                return null;
            }
            $folder = $this->getFolderFromBox($id);
            $this->addFolder($folder);
        }

        return $folders[$id];
    }

    public function addFolder($folder)
    {
        $folders = $this->getFolders();
        array_push($folders, $folder);
        $this->setFolders($folders);

        return $this;
    }

    public function getFolders($retrieve = true)
    {
        if (!$retrieve) {
            return $this->folders;
        }

        $root = $this->getRoot();
        if (null === $root) {
            $root = $this->getFolderFromBox();
            $this->setRoot($root);
        }

        // not sure if I should add recursive parsing of folder/items. stubbing out for now.
        return null;
    }

    /**
     * get membership list of a given group. if limit or offset is numeric, only retrieve specific list page;.
     *
     * @param null $group
     * @param null $limit  leave null to get all; if limit is null but offset is numeric, limit will default to 100
     * @param null $offset leave null to get all; if limit is null but offset is numeric, limit will default to 100
     *
     * @return array returns an array of User objects that are in the group membership
     * @return array returns an array of User objects that are in the group membership
     *
     * @throws \Box\Exception\Exception
     */
    public function getGroupMembershipList($group = null, $limit = null, $offset = null)
    {
        if (is_numeric($group) && is_int($group)) {
            $groupId = $group;
            $group = $this->getNewGroup();
            $group->setId($groupId);
        }

        if (!$group instanceof GroupInterface) {
            throw new Exception('Group object expected', Exception::INVALID_INPUT);
        }

        $members = [];
        $entries = [];

        if (is_numeric($limit) || is_numeric($offset)) {
            if (!is_numeric($limit)) {
                $limit = 100;
            }

            $uri = $group->getMembershipListUri($limit, $offset);

            $data = $this->query($uri);

            $entries = $data['entries'];
        } else {
            $limit = 100;
            $offset = 0;

            $uri = $group->getMembershipListUri($limit, $offset);

            $data = $this->query($uri);

            $totalMembers = $data['total_count'];

            $entries = $data['entries'];

            $currentTotal = count($entries);

            while ($currentTotal < $totalMembers) {
                if (0 != $offset) {
                    $nextPage = $group->getMembershipListUri($limit, $offset);
                    $data = $this->query($nextPage);
                    $moreEntries = $data['entries'];
                    $entries = array_merge($entries, $moreEntries);

                    $currentTotal = count($entries);
                }

                $offset += $limit;
            }
        }

        foreach ($entries as $entry) {
            $userData = $entry['user'];
            $user = $this->getNewUser();
            $user->mapBoxToClass($userData);
            $members[] = $user;
        }

        return $members;
    }

    public function getEvents($position = 'now', $type = 'all', $limit = 100)
    {
        $uri = Event::URI;

        $uri .= '?stream_type='.$type.'&limit='.$limit;

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $data = $connection->query($uri.'&stream_position='.$position);

        $jsonData = json_decode($data['body'], true, 512, JSON_BIGINT_AS_STRING);

        if (null === $jsonData) {
            $data['error'] = 'unable to decode json data';
            $data['error_description'] = 'try refreshing the token';
            $this->error($data);
        } elseif (is_array($jsonData) && array_key_exists('type', $jsonData) && 'error' == $jsonData['type']) {
            $data = [];
            $data['error'] = $jsonData['status'].'  - '.$jsonData['code'];
            $data['error_description'] = var_export($jsonData['context_info'], true);
            $this->error($data);
        }

        $events = [];
        $entries = $jsonData['entries'];

        while ($jsonData['chunk_size'] > 0 && $jsonData['chunk_size'] >= $limit) {
            $position = $jsonData['next_stream_position'];
            $data = $connection->query($uri.'&stream_position='.$position);
            $jsonData = json_decode($data['body'], true, 512, JSON_BIGINT_AS_STRING);
            $entries = array_merge($entries, $jsonData['entries']);
        }

        foreach ($entries as $entry) {
            $event = $this->getNewEvent();
            $event->mapBoxToClass($entry);
            array_push($events, $event);
        }

        return ['chunk_size' => $jsonData['chunk_size'], 'next_stream_position' => (string) $jsonData['next_stream_position'], 'events' => $events];
    }

    public function getFileFromBox($id = 0)
    {
        $uri = File::URI.'/'.$id; // all class constant URIs do not end in a slash

        $fields = [
            'type',
            'id',
            'etag',
            'url',
            'name',
            'description',
            'size',
            'path_collection',
            'modified_at',
            'created_by',
            'shared_link',
            'parent',
            'item_status',
            'permissions',
            'tags',
            'extension',
            'representations',
        ];
        $fieldstr = join(',', $fields);
        $fieldquery = (strlen($fieldstr) > 0) ? '?fields='.$fieldstr : '';

        $uri .= $fieldquery;

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $data = $connection->query($uri);

        $jsonData = json_decode($data['body'], true);
        /*
         * API docs says error is thrown if file does not exist or no access.
         * no example of error to parse by. Have to assume success until can modify
         */
        // error decoding json data
        if (null === $jsonData) {
            $data['error'] = 'unable to decode json data';
            $data['error_description'] = 'try refreshing the token';
            $this->error($data);
        } elseif (is_array($jsonData) && array_key_exists('type', $jsonData) && 'error' == $jsonData['type']) {
            $data = [];
            $data['error'] = $jsonData['status'].'  - '.$jsonData['code'];
            $data['error_description'] = var_export($jsonData['context_info'], true);
            $this->error($data);
        }

        $file = $this->getNewFile();
        $file->mapBoxToClass($jsonData);

        return $file;
    }

    public function downloadFile($id = 0, $forcedownload = false)
    {
        $uri = File::URI.'/'.$id.'/content'; // all class constant URIs do not end in a slash

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        if ($forcedownload) {
            $connection->setFollowLocation(true);
        } else {
            $connection->setFollowLocation(false);
        }

        $data = $connection->query($uri);

        $headers = $data['headers'];
        $responsecode = $data['code'];

        // Found
        if (302 === $responsecode) {
            return $headers['location'];
            // Found but not processed
        }
        if (202 === $responsecode) {
            return false;
        }
        if (200 === $responsecode && $forcedownload) {
            return $data;
        }

        return false;

        return false;
    }

    /*
      * @param array      $items
      * @param null|string $download_file_name
      *
      * @return \Box\Model\Zip\Zip|\Box\Model\Zip\ZipInterface
      */

    public function downloadZip($items, $download_file_name = null)
    {
        $uri = Zip::URI; // all class constant URIs do not end in a slash

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $params = [
            'download_file_name' => $download_file_name,
            'items' => $items,
        ];

        $json = $connection->post($uri, $params, true);

        $data = json_decode($json['body'], true);

        if (null === $data) {
            $data['error'] = 'sdk_json_decode';
            $data['error_description'] = 'unable to decode or recursion level too deep';
            $this->error($data);
        } elseif (array_key_exists('error', $data)) {
            $this->error($data);
        } elseif (array_key_exists('type', $data) && 'error' == $data['type']) {
            $data['error'] = 'sdk_unknown';
            $ditto = $data;
            $data['error_description'] = $ditto;
            $this->error($data);
        }

        $zip = $this->getNewZip();
        $zip->mapBoxToClass($data);

        return $zip;
    }

    public function getFolderFromBox($id = 0)
    {
        $uri = Folder::URI.'/'.$id; // all class constant URIs do not end in a slash

        $fields = [
            'type',
            'id',
            'name',
            'description',
            'size',
            'path_collection',
            'modified_at',
            'trashed_at',
            'shared_link',
            'parent',
            'item_status',
            'permissions',
            'tags',
        ];
        $fieldstr = join(',', $fields);
        $fieldquery = (strlen($fieldstr) > 0) ? '?fields='.$fieldstr : '';

        $uri .= $fieldquery;

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $data = $connection->query($uri);

        $jsonData = json_decode($data['body'], true);
        /*
         * API docs says error is thrown if folder does not exist or no access.
         * no example of error to parse by. Have to assume success until can modify
         */
        // error decoding json data
        if (null === $jsonData) {
            $data['error'] = 'unable to decode json data';
            $data['error_description'] = 'try refreshing the token';
            $this->error($data);
        } elseif (is_array($jsonData) && array_key_exists('type', $jsonData) && 'error' == $jsonData['type']) {
            $data = [];
            $data['error'] = $jsonData['status'].'  - '.$jsonData['code'];
            $data['error_description'] = (isset($jsonData['context_info']) ? var_export($jsonData['context_info'], true) : $data['body']);
            $this->error($data);
        }

        $folder = $this->getNewFolder();
        $folder->mapBoxToClass($jsonData);

        return $folder;
    }

    /**
     * @param \Box\Model\Folder\Folder|\Box\Model\Folder\FolderInterface $folder
     * @param int                                                        $limit
     * @param int                                                        $offset
     *
     * @return \Box\Model\Folder\Folder|\Box\Model\Folder\FolderInterface
     */
    public function getBoxFolderItems($folder, $limit = 100, $offset = 0)
    {
        $uri = $folder->getBoxFolderItemsUri($limit, $offset);
        $data = $this->query($uri);
        $items = $data['entries'];

        while ($offset + $limit < $data['total_count']) {
            $offset = $data['offset'] + $limit;
            $limit = $data['limit'];
            $uri = $folder->getBoxFolderItemsUri($limit, $offset);
            $data = $this->query($uri);
            $items = array_merge($items, $data['entries']);
        }

        $folder->setItemCollection($items);

        return $folder;
    }

    public function getFolderItems($id = 0)
    {
        /**
         * @var Folder|FolderInterface $folder
         */
        $folder = $this->getFolder($id);

        return $folder->getItems();
    }

    /**
     * @param     $name
     * @param int $parentFolderId
     *
     * @return Folder|FolderInterface
     */
    public function createNewBoxFolder($name, $parentFolderId = 0)
    {
        $uri = Folder::URI;

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $params = [
            'name' => $name,
            'parent' => ['id' => $parentFolderId],
        ];

        $data = $connection->post($uri, $params, true);

        $jsonData = json_decode($data['body'], true);

        // error decoding json data
        if (null === $jsonData) {
            $data = [];
            $data['error'] = 'unable to decode json data';
            $data['error_description'] = 'try refreshing the token';
            $this->error($data);
        } elseif (is_array($jsonData) && array_key_exists('type', $jsonData) && 'error' == $jsonData['type']) {
            switch ($jsonData['code']) {
                case 'name_temporarily_reserved':
                    usleep(1000000);

                    return $this->createNewBoxFolder($name, $parentFolderId);

                    break;

                case 'item_name_in_use':
                    $folder = reset($jsonData['context_info']['conflicts']);

                    return $this->getFolderFromBox($folder['id']);

                default:
                    break;
            }

            $data = [];
            $data['error'] = $jsonData['status'].'  - '.$jsonData['code'];
            $data['error_description'] = var_export($jsonData['context_info'], true);
            $this->error($data);
        }

        $folder = $this->getNewFolder();
        $folder->mapBoxToClass($jsonData);

        return $folder;
    }

    /**
     * @param FileInterface|Folder|FolderInterfaceFile $entry
     * @param bool                                     $ifMatchHeader
     * @param mixed                                    $params
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function updateEntry($entry, $params = [], $ifMatchHeader = false)
    {
        if (0 === count($params)) {
            return $entry;
        }

        $uri = $entry::URI.'/'.$entry->getId();

        // @todo implement If-Match header logic

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);
        $json = $connection->put($uri, $params, true);

        $data = json_decode($json['body'], true);

        // error decoding json data
        if (null === $data) {
            $errorData = [];
            $errorData['error'] = 'unable to decode json data';
            $errorData['error_description'] = $data;
            $this->error($errorData);
        } elseif (is_array($data) && array_key_exists('type', $data) && 'error' == $data['type']) {
            $errorData = [];
            $errorData['error'] = $data['status'].'  - '.$data['code'];
            $errorData['error_description'] = var_export($data['context_info'], true);
            $this->error($errorData);
        }

        if ('folder' === $data['type']) {
            return new \Box\Model\Folder\Folder($data);
        }
        if ('file' === $data['type']) {
            return new \Box\Model\File\File($data);
        }

        return $data;
    }

    /**
     * @param File|FileInterface|Folder|FolderInterface $entry
     * @param bool                                      $ifMatchHeader
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function renameEntry($entry)
    {
        $params = ['name' => $entry->getName()];

        return $this->updateEntry($entry, $params, false);
        // inconsistent? figure out what return is needed, if any
    }

    /**
     * @param File|FileInterface|Folder|FolderInterface $entry
     * @param bool                                      $ifMatchHeader
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function deleteEntry($entry)
    {
        $params = [];

        $uri = $entry::URI.'/'.$entry->getId();

        if ('folder' === $entry->getType()) {
            $uri .= '?recursive=true';
        }

        // @todo implement If-Match header logic

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);
        $json = $connection->delete($uri, $params, true);

        $responsecode = $json['code'];

        if (204 === $responsecode) {
            return true;
        }

        return false;
    }

    /**
     * @param null|\Box\Model\Folder\Folder|\Box\Model\Folder\FolderInterface $folder
     *
     * @return mixed raw json data as an array
     */
    public function getFolderCollaborations($folder = null)
    {
        if (!$folder instanceof FolderInterface) {
            $err['error'] = 'sdk_unexpected_type';
            $err['error_description'] = 'expecting FolderInterface class. given ('.var_export($folder, true).')';
            $this->error($err);
        }
        $folderId = $folder->getId();
        $uri = Folder::URI.'/'.$folderId.'/collaborations';

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $json = $connection->query($uri);

        $data = json_decode($json['body'], true);

        // this can be refactored too...from copyBoxFolder
        if (null === $data) {
            $data['error'] = 'sdk_json_decode';
            $data['error_description'] = 'unable to decode or recursion level too deep';
            $this->error($data);
        } elseif (array_key_exists('error', $data)) {
            $this->error($data);
        } elseif (array_key_exists('type', $data) && 'error' == $data['type']) {
            $data['error'] = 'sdk_unknown';
            $ditto = $data;
            $data['error_description'] = $ditto;
            $this->error($data);
        }

        return $data;
    }

    /**
     * @param null|\Box\Model\Folder\Folder|\Box\Model\Folder\FolderInterface                         $folder
     * @param null|\Box\Model\Group\GroupInterface|\Box\Model\User\User|\Box\Model\User\UserInterface $collaborator
     * @param string                                                                                  $role         see {@link http://developers.box.com/docs/#collaborations box documentation for all possible roles}
     *                                                                                                              default is viewer
     *
     * @return \Box\Model\Collaboration\Collaboration|\Box\Model\Collaboration\CollaborationInterface
     *
     * @throws \Box\Exception\Exception
     */
    public function addCollaboration($folder = null, $collaborator = null, $role = 'viewer')
    {
        if (!$folder instanceof FolderInterface) {
            $err['error'] = 'sdk_unexpected_type';
            $err['error_description'] = 'expecting FolderInterface class. given ('.var_export($folder, true).')';
            $this->error($err);
        }

        if (!$collaborator instanceof UserInterface && !$collaborator instanceof GroupInterface) {
            $err['error'] = 'sdk_unexpected_type';
            $err['error_description'] = 'expecting UserInterface class. given ('.var_export($collaborator, true).')';
            $this->error($err);
        }

        $uri = Collaboration::URI;

        $folderId = $folder->getId();
        $collaboratorId = $collaborator->getId();

        $params = [
            'item' => [
                'id' => $folderId,
                'type' => 'folder',
            ],
            'accessible_by' => [
                'id' => $collaboratorId,
            ],
            'role' => $role,
        ];

        // can be refactored a bit more but the json encode works in the connection class
        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $json = $connection->post($uri, $params, true);

        $data = json_decode($json['body'], true);

        if (null === $data) {
            $data['error'] = 'sdk_json_decode';
            $data['error_description'] = 'unable to decode or recursion level too deep';
            $this->error($data);
        } elseif (array_key_exists('error', $data)) {
            $this->error($data);
        } elseif (array_key_exists('type', $data) && 'error' == $data['type']) {
            $data['error'] = 'sdk_unknown';
            $ditto = $data;
            $data['error_description'] = $ditto;
            $this->error($data);
        }

        $collaboration = $this->getNewCollaboration();
        $collaboration->mapBoxToClass($data);

        return $collaboration;
    }

    /**
     * @param null|\Box\Model\Folder\Folder|\Box\Model\Folder\FolderInterface $folder
     * @param null|array shared link options with
     * default shared link set to collaborator access, no unshared time or permissions set to
     * @param null|mixed $folderId
     * @param null|mixed $params
     *
     * @return \Box\Model\Folder\Folder|\Box\Model\Folder\FolderInterface
     */
    public function createSharedLinkForFolder($folderId = null, $params = null)
    {
        $uri = Folder::URI;
        $uri .= '/'.$folderId;

        if (!is_array($params)) {
            $params = [
                'shared_link' => [],
            ];
        }

        // can be refactored a bit more but the json encode works in the connection class
        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $json = $connection->put($uri, $params, true);

        $data = json_decode($json['body'], true);

        if (null === $data) {
            $data['error'] = 'sdk_json_decode';
            $data['error_description'] = 'unable to decode or recursion level too deep';
            $this->error($data);
        } elseif (array_key_exists('error', $data)) {
            $this->error($data);
        } elseif (array_key_exists('type', $data) && 'error' == $data['type']) {
            $data['error'] = 'sdk_unknown';
            $ditto = $data;
            $data['error_description'] = $ditto;
            $this->error($data);
        }

        $updatedFolder = $this->getNewFolder();
        $updatedFolder->mapBoxToClass($data);

        return $updatedFolder->getSharedLink();
    }

    /**
     * @param null|\Box\Model\File\File|\Box\Model\File\FileInterface $file
     * @param null|array shared link options with
     * default shared link set to collaborator access, no unshared time or permissions set to
     * @param null|mixed $fileId
     * @param null|mixed $params
     *
     * @return \Box\Model\File\File|\Box\Model\File\FileInterface
     */
    public function createSharedLinkForFile($fileId = null, $params = null)
    {
        $fields = [
            'shared_link',
        ];
        $fieldstr = join(',', $fields);
        $fieldquery = (strlen($fieldstr) > 0) ? '?fields='.$fieldstr : '';

        $uri = File::URI;
        $uri .= '/'.$fileId;
        $uri .= $fieldquery;

        if (!is_array($params)) {
            $params = [
                'shared_link' => [],
            ];
        }

        // can be refactored a bit more but the json encode works in the connection class
        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $json = $connection->put($uri, $params, true);

        $data = json_decode($json['body'], true);

        if (null === $data) {
            $data['error'] = 'sdk_json_decode';
            $data['error_description'] = 'unable to decode or recursion level too deep';
            $this->error($data);
        } elseif (array_key_exists('error', $data)) {
            $this->error($data);
        } elseif (array_key_exists('type', $data) && 'error' == $data['type']) {
            $data['error'] = 'sdk_unknown';
            $ditto = $data;
            $data['error_description'] = $ditto;
            $this->error($data);
        }

        $updatedFile = $this->getNewFile();
        $updatedFile->mapBoxToClass($data);

        return $updatedFile->getSharedLink();
    }

    /**
     * @param null|\Box\Model\File\File|\Box\Model\File\FileInterface $file
     * @param null|array shared link options with
     * default shared link set to collaborator access, no unshared time or permissions set to
     * @param null|mixed $fileId
     *
     * @return \Box\Model\File\File|\Box\Model\File\FileInterface
     */
    public function createShortLivedEmbed($fileId = null)
    {
        $uri = File::URI;

        $uri .= '/'.$fileId.'/';

        $params = [
            'fields' => 'expiring_embed_link',
        ];

        $query = $this->buildQuery($params);
        $uri .= '?'.$query;

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $data = $connection->query($uri);

        $jsonData = json_decode($data['body'], true);
        /*
         * API docs says error is thrown if file does not exist or no access.
         * no example of error to parse by. Have to assume success until can modify
         */
        // error decoding json data
        if (null === $jsonData) {
            $data['error'] = 'unable to decode json data';
            $data['error_description'] = 'try refreshing the token';
            $this->error($data);
        } elseif (is_array($jsonData) && array_key_exists('type', $jsonData) && 'error' == $jsonData['type']) {
            $data = [];
            $data['error'] = $jsonData['status'].'  - '.$jsonData['code'];
            $data['error_description'] = var_export($jsonData['context_info'], true);
            $this->error($data);
        }

        $file = $this->getNewFile();
        $file->mapBoxToClass($jsonData);

        return $file->getExpiringEmbedLink();
    }

    /**
     * @param null|\Box\Model\File\File|\Box\Model\File\FileInterface $file
     * @param null|mixed                                              $uri
     *
     * @return \Box\Model\File\File|\Box\Model\File\FileInterface
     */
    public function downloadRepresentation($uri)
    {
        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);
        $connection->setFollowLocation(false);

        $data = $connection->query($uri);

        $headers = $data['headers'];
        $responsecode = $data['code'];

        switch ($responsecode) {
            // Not available yet
            case 202:
                return (int) $data['headers']['retry-after'];
                // Found, but no own thumbnail
            case 302:
                return $data;
                // Found, returning contents
            case 200:
                return $data;
                // Bad request or not found
            case 400:
            case 404:
            default:
                return false;
        }

        return false;
    }

    /**
     * @param Folder       $originalFolder
     * @param array|Folder $parent
     * @param string       $name
     * @param bool         $addToFolders
     * @param mixed        $originalFolderId
     * @param mixed        $parentId
     *
     * @return \Box\Model\Folder\Folder|\Box\Model\Folder\FolderInterface
     *
     * @throws Exception
     *
     * @internal param $destinationId
     */
    public function copyBoxFolder($originalFolderId, $parentId, $name = null, $addToFolders = true)
    {
        $uri = Folder::URI.'/'.$originalFolderId.'/copy';
        $params['parent'] = ['id' => $parentId];
        $params['name'] = $name;

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $json = $connection->post($uri, $params, true);

        $data = json_decode($json['body'], true);

        if (null === $data) {
            $data['error'] = 'sdk_json_decode';
            $data['error_description'] = 'unable to decode or recursion level too deep';
            $this->error($data);
        } elseif (array_key_exists('error', $data)) {
            $this->error($data);
        } elseif (array_key_exists('type', $data) && 'error' == $data['type']) {
            $data['error'] = 'sdk_unknown';
            $ditto = $data;
            $data['error_description'] = $ditto;
            $this->error($data);
        }

        $copy = $this->getNewFolder();
        $copy->mapBoxToClass($data);

        if (true === $addToFolders && $copy instanceof Folder) {
            $this->addFolder($copy);
        }

        return $copy;
    }

    /**
     * @param File   $originalFileId
     * @param string $parentId
     * @param string $name
     *
     * @return \Box\Model\File\File|\Box\Model\File\FileInterface
     *
     * @throws Exception
     */
    public function copyBoxFile($originalFileId, $parentId, $name = null)
    {
        $uri = File::URI.'/'.$originalFileId.'/copy';
        $params['parent'] = ['id' => $parentId];
        $params['name'] = $name;

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $json = $connection->post($uri, $params, true);

        $data = json_decode($json['body'], true);

        if (null === $data) {
            $data['error'] = 'sdk_json_decode';
            $data['error_description'] = 'unable to decode or recursion level too deep';
            $this->error($data);
        } elseif (array_key_exists('error', $data)) {
            $this->error($data);
        } elseif (array_key_exists('type', $data) && 'error' == $data['type']) {
            $data['error'] = 'sdk_unknown';
            $ditto = $data;
            $data['error_description'] = $ditto;
            $this->error($data);
        }

        $copy = $this->getNewFile();
        $copy->mapBoxToClass($data);

        return $copy;
    }

    // @todo make multiple file upload
    public function uploadFileToBox($file, $parentId = 0, $currentId = false, $autorename_counter = '0')
    {
        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        // Check for conflicts
        if (false === $currentId) {
            $params = [
                'name' => $file->name,
                'parent' => ['id' => $parentId],
            ];
            $preflight = $connection->options('https://api.box.com/2.0/files/content', $params, true);

            $data = json_decode($preflight['body'], true);

            if (array_key_exists('type', $data) && 'error' == $data['type']) {
                if ('item_name_in_use' === $data['code']) {
                    // Auto Rename if possible
                    $original_name = str_replace(' ('.$autorename_counter.')', '', $file->name);
                    $file_info = \TheLion\LetsBox\Helpers::get_pathinfo($original_name);
                    $file->name = $file_info['filename'].' ('.($autorename_counter + 1).')'.(isset($file_info['extension']) ? '.'.$file_info['extension'] : '');

                    return $this->uploadFileToBox($file, $parentId, $currentId, $autorename_counter + 1);
                }

                $data['error'] = $data['message'];
                $ditto = $data;
                $data['error_description'] = $ditto;
                $this->error($data);
            }
        }

        // Start the upload
        $uri = File::UPLOAD_URI;

        if (false !== $currentId) {
            $uri = "https://upload.box.com/api/2.0/files/{$currentId}/content";
        }

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);
        $uploaded = $connection->postFile($uri, $file, $parentId);

        $data = json_decode($uploaded['body'], true);

        if (array_key_exists('type', $data) && 'error' == $data['type']) {
            $data['error'] = $data['message'];
            $ditto = $data;
            $data['error_description'] = $ditto;
            $this->error($data);
        }

        $file = $this->getNewFile();
        $file->mapBoxToClass($data['entries'][0]);

        return $file;
    }

    public function uploadChunked($parent_id, $file)
    {
        $session = $this->startUploadSession($parent_id, $file);

        if (false === $session) {
            return false;
        }

        $uploaded = 0;
        $remaining = $file->size;
        $chunkSize = $session['part_size'];
        $parts = [];

        $handle = fopen($file->tmp_path, 'rb');

        while ($remaining > $chunkSize) {
            $uploadChunkedPart = $this->uploadChunkedPart($session['session_endpoints']['upload_part'], $handle, $uploaded, $chunkSize, $file);

            $parts[] = $uploadChunkedPart;

            // Update remaining and uploaded
            $uploaded = $uploaded + $chunkSize;
            $remaining = $remaining - $chunkSize;

            // Update the progress
            $status = [
                'bytes_up_so_far' => $uploaded,
                'total_bytes_down_expected' => $file->size,
                'percentage' => round(($uploaded / $file->size) * 100),
                'progress' => 'uploading',
            ];

            $hash = $_REQUEST['hash'];
            $current = \TheLion\LetsBox\Upload::get_upload_progress($hash);
            $current['status'] = $status;
            \TheLion\LetsBox\Upload::set_upload_progress($_REQUEST['hash'], $current);
        }

        fclose($handle);

        $entry = $this->finishUploadSession($session['session_endpoints']['commit'], $parts, $file);

        $file = $this->getNewFile();
        $file->mapBoxToClass($entry);

        return $file;
    }

    public function startUploadSession($parent_id, $file)
    {
        $url_start_session = 'https://upload.box.com/api/2.0/files/upload_sessions';

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $params = [
            'folder_id' => $parent_id,
            'file_size' => $file->size,
            'file_name' => $file->name,
        ];

        $session = $connection->post($url_start_session, $params, true);

        $headers = $session['headers'];
        $responsecode = $session['code'];

        if ($responsecode >= 400) {
            return false;
        }

        return json_decode($session['body'], true);
        // $url_uploaded_parts = $data['session_endpoints']['upload_part'];
        // $url_commit = $data['session_endpoints']['commit'];
    }

    public function finishUploadSession($url_finish_session, $parts, $file)
    {
        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $params = [
            'parts' => $parts,
        ];

        $options = $connection->getCurlOpts();

        $options[CURLOPT_HTTPHEADER] = [
            'Digest' => sha1_file($file),
        ];

        $connection->setCurlOpts($options);

        $response = $connection->post($url_finish_session, $params, true);

        return json_decode($response['body'], true);
    }

    public function uploadChunkedPart($url, $filehandle, $uploaded, $chunkSize, $file)
    {
        fseek($filehandle, $uploaded);
        $chunk = fread($filehandle, $chunkSize);

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $digest = sha1($chunk);
        $lastBytePos = $uploaded + $chunkSize - 1;

        $options = $connection->getCurlOpts();

        $options[CURLOPT_HTTPHEADER] = [
            'Digest' => $digest,
            'Content-Range' => "bytes {$uploaded}-{$lastBytePos}/{$file->size}",
            'Content-Type' => 'application/octet-stream',
        ];

        $options[CURLOPT_BINARYTRANSFER] = true;

        $connection->setCurlOpts($options);

        $uploaded_part = $connection->put($url, $chunk);

        $response = json_decode($uploaded_part['body'], true);

        return $response['part'];
    }

    public function getAccessToken()
    {
        $connection = $this->getConnection();
        $params['grant_type'] = 'authorization_code';
        $params['code'] = $this->getAuthorizationCode();
        $params['client_id'] = $this->getClientId();
        $params['client_secret'] = $this->getClientSecret();
        $params['scope'] = implode(',', $this->getScopes());

        $redirectUri = $this->getRedirectUri();
        if (null !== $redirectUri) {
            $params['redirect_uri'] = $redirectUri;
        }

        $json = $connection->post(self::TOKEN_URI, $params);

        $data = json_decode($json['body'], true);

        if (null === $data) {
            $data['error'] = 'sdk_json_decode';
            $data['error_description'] = 'unable to decode or recursion level too deep';
            $this->error($data);
        } elseif (array_key_exists('error', $data)) {
            $this->error($data);
        }

        $data['created'] = time();
        $token = $this->getToken();
        $this->setTokenData($token, $data);

        return $token;
    }

    /**
     * @param mixed $refresh_token
     *
     * @return \Box\Model\Connection\Token\Token|\BTokenInterfaceox\Model\Connection\Token\
     */
    public function refreshToken($refresh_token)
    {
        // outside script will set token via getAccessToken
        $token = new \Box\Model\Connection\Token\Token();

        $params['refresh_token'] = $refresh_token;
        $params['client_id'] = $this->getClientId();
        $params['client_secret'] = $this->getClientSecret();
        $params['grant_type'] = 'refresh_token';

        $deviceId = $this->getDeviceId();
        if (null !== $deviceId) {
            $params['device_id'] = $deviceId;
        }

        $deviceName = $this->getDeviceName();
        if (null !== $deviceName) {
            $params['device_name'] = $deviceName;
        }

        $connection = $this->getConnection();

        $json = $connection->post(self::TOKEN_URI, $params);
        $data = json_decode($json['body'], true);

        if (null === $data) {
            $data['error'] = 'sdk_json_decode';
            $data['error_description'] = 'unable to decode or recursion level too deep';
            $this->error($data);
        } elseif (array_key_exists('error', $data)) {
            $this->error($data);
        }
        $this->hasnewToken = true;
        $data['created'] = time();
        $this->setTokenData($token, $data);
        $this->setToken($token);

        return $token;
    }

    public function hasNewToken()
    {
        return $this->hasnewToken;
    }

    public function getAuthorizationHeader()
    {
        if ($this->isAccessTokenExpired()) {
            // Call Update function of plugin itself
            do_action('lets-box-refresh-token', \TheLion\LetsBox\App::get_current_account());
        }

        $token = $this->getToken();

        return 'Authorization: Bearer '.$token->getAccessToken();
    }

    /**
     * Returns if the access_token is expired.
     *
     * @return bool returns True if the access_token is expired
     */
    public function isAccessTokenExpired()
    {
        $token = $this->getToken();
        $currenttime = time();
        // If the token is set to expire in the next 5 minutes.
        return ($token->getCreated() + ($token->getExpiresIn() - 300)) < $currenttime;
    }

    /**
     * @param $token \Box\Model\Connection\Token\TokenInterface
     * @param $data
     *
     * @return \Box\Model\Connection\Token\TokenInterface
     */
    public function setTokenData($token, $data)
    {
        $token->setAccessToken($data['access_token']);
        $token->setExpiresIn($data['expires_in']);
        $token->setTokenType($data['token_type']);
        $token->setRefreshToken($data['refresh_token']);
        $token->setCreated($data['created']);

        return $token;
    }

    /**
     * @param $token \Box\Model\Connection\Token\TokenInterface|\Box\Model\Connection\Token\Token
     *
     * @return mixed
     */
    public function destroyToken($token)
    {
        $params['client_id'] = $this->getClientId();
        $params['client_secret'] = $this->getClientSecret();
        // The access_token or refresh_token to be destroyed. Only one is required, though both will be destroyed.
        $params['token'] = $token->getAccessToken();

        $connection = $this->getConnection();

        $json = $connection->post(self::REVOKE_URI, $params);
        // @todo add error handling for null data
        return json_decode($json['body'], true);
    }

    public function auth()
    {
        // build get query to auth uri
        $query = $this->buildAuthQuery();

        // send get query to auth uri (auth uri will redirect to app redirect uri)
        $connection = $this->getConnection();

        // can't get return data b/c of redirect
        $connection->query($query);
    }

    public function buildAuthQuery()
    {
        $uri = self::AUTH_URI.'?';
        $params = [];

        $params['response_type'] = 'code';

        $clientId = $this->getClientId();
        $params['client_id'] = $clientId;

        $state = $this->getState();
        if (null !== $state) {
            $params['state'] = $state;
        }

        $scope = implode(',', $this->getScopes());
        if (!empty($scope)) {
            $params['scope'] = $scope;
        }

        $query = $this->buildQuery($params); // buildQuery does urlencode
        $uri .= $query;

        $redirectUri = $this->getRedirectUri();

        if (null !== $redirectUri) {
            $redirectUri = urlencode($redirectUri);
            $uri .= '&redirect_uri='.$redirectUri;
        }

        return $uri;
    }

    /**
     * @param $connection Connection
     *
     * @return Connection
     */
    public function setConnectionAuthHeader($connection)
    {
        $headers = [$this->getAuthorizationHeader()];

        if ($connection->canGzip()) {
            $headers[] = 'Accept-Encoding: gzip, deflate';
        }
        // header opt will require a merge with other headers to not overwrite.
        // @todo refactor to allow additional headers with auth header
        $connection->setCurlOpts(['CURLOPT_HTTPHEADER' => $headers]);

        return $connection;
    }

    /**
     * @param $connection Connection
     *
     * @return Connection
     */
    public function setConnectionRepresentationHeader($connection)
    {
        $headers = $connection->getCurlOpts();
        $headers['CURLOPT_HTTPHEADER'][] = 'X-Rep-Hints: [jpg?dimensions=1024x1024]';
        $connection->setCurlOpts($headers);

        return $connection;
    }

    public function setClientId($clientId = null)
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setClientSecret($clientSecret = null)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function setScopes($scopes = [])
    {
        return $this->scopes = $scopes;
    }

    public function getScopes()
    {
        return $this->scopes;
    }

    public function setRedirectUri($redirectUri = null)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function setAuthorizationCode($authorizationCode = null)
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    public function setToken($token = null)
    {
        $this->token = $token;

        return $this;
    }

    public function getToken()
    {
        if (null === $this->token) {
            $tokenClass = $this->getTokenClass();
            $token = new $tokenClass();
            $this->token = $token;
        }

        return $this->token;
    }

    public function setTokenClass($tokenClass = null)
    {
        $this->validateClass($tokenClass, 'TokenInterface');
        $this->tokenClass = $tokenClass;

        return $this;
    }

    public function getTokenClass()
    {
        return $this->tokenClass;
    }

    public function setConnectionClass($connectionClass = null)
    {
        $this->validateClass($connectionClass, 'ConnectionInterface');

        $this->connectionClass = $connectionClass;

        return $this;
    }

    public function getConnectionClass()
    {
        return $this->connectionClass;
    }

    public function setConnection($connection = null)
    {
        if (!$connection instanceof ConnectionInterface) {
            throw new Exception('Invalid Class', Exception::INVALID_CLASS);
        }
        $this->connection = $connection;

        return $this;
    }

    public function getConnection()
    {
        if (null === $this->connection) {
            $connectionClass = $this->getConnectionClass();
            $connection = new $connectionClass();
            $this->connection = $connection;

            if (defined('LETSBOX_GZIP') && LETSBOX_GZIP === false) {
            } else {
                $this->connection->enableGzip();
            }
        }

        return $this->connection;
    }

    public function setEventClass($eventClass = null)
    {
        $this->validateClass($eventClass, 'EventInterface');
        $this->eventClass = $eventClass;

        return $this;
    }

    public function getEventClass()
    {
        return $this->eventClass;
    }

    public function setFileClass($fileClass = null)
    {
        $this->validateClass($fileClass, 'FileInterface');
        $this->fileClass = $fileClass;

        return $this;
    }

    public function getFileClass()
    {
        return $this->fileClass;
    }

    /**
     * @todo determine best validation for this
     *
     * @param null $files
     *
     * @return $this
     */
    public function setFiles($files = null)
    {
        $this->files = $files;

        return $this;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function setFolderClass($folderClass = null)
    {
        $this->validateClass($folderClass, 'FolderInterface');
        $this->folderClass = $folderClass;

        return $this;
    }

    public function getFolderClass()
    {
        return $this->folderClass;
    }

    public function setFolders($folders = null)
    {
        $this->folders = $folders;

        return $this;
    }

    public function setCollaborationClass($collaborationClass = null)
    {
        $this->validateClass($collaborationClass, 'CollaborationInterface');
        $this->collaborationClass = $collaborationClass;

        return $this;
    }

    public function getCollaborationClass()
    {
        return $this->collaborationClass;
    }

    public function setZipClass($zipClass = null)
    {
        $this->validateClass($zipClass, 'ZipInterface');
        $this->zipClass = $zipClass;

        return $this;
    }

    public function getZipClass()
    {
        return $this->zipClass;
    }

    public function setUserClass($userClass = null)
    {
        $this->validateClass($userClass, 'UserInterface');
        $this->userClass = $userClass;

        return $this;
    }

    public function getUserClass()
    {
        return $this->userClass;
    }

    /**
     * @return \Box\Model\User\User|\Box\Model\User\UserInterface
     */
    public function getUserInfo()
    {
        $uri = USER::CURRENT_USER_URI;

        $fields = [
            'type',
            'id',
            'name',
            'avatar_url',
            'login',
            'space_amount',
            'space_used',
            'enterprise',
        ];
        $fieldstr = join(',', $fields);
        $fieldquery = (strlen($fieldstr) > 0) ? '?fields='.$fieldstr : '';

        $uri .= $fieldquery;

        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);

        $data = $connection->query($uri);

        $jsonData = json_decode($data['body'], true);
        // error decoding json data
        if (null === $jsonData) {
            $data['error'] = 'unable to decode json data';
            $data['error_description'] = 'try refreshing the token';
            $this->error($data);
        }

        $user = $this->getNewUser();
        $user->mapBoxToClass($jsonData);

        return $user;
    }

    public function setGroupClass($groupClass = null)
    {
        $this->validateClass($groupClass, 'GroupInterface');
        $this->groupClass = $groupClass;

        return $this;
    }

    public function getGroupClass()
    {
        return $this->groupClass;
    }

    /**
     * @param array $collaborations
     *
     * @return \Box\Model\Client\Client $this
     */
    public function setCollaborations($collaborations = null)
    {
        $this->collaborations = $collaborations;

        return $this;
    }

    /**
     * @return array
     */
    public function getCollaborations()
    {
        return $this->collaborations;
    }

    public function setDeviceId($deviceId = null)
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    public function getDeviceId()
    {
        return $this->deviceId;
    }

    public function setDeviceName($deviceName = null)
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    public function getDeviceName()
    {
        return $this->deviceName;
    }

    public function setState($state = null)
    {
        $this->state = $state;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    /**
     * @param \Box\Model\Folder\Folder|\Box\Model\Folder\FolderInterface $root
     *
     * @return \Box\Model\Client\Client
     */
    public function setRoot($root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return \Box\Model\Folder\Folder|\Box\Model\Folder\FolderInterface
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param $uri
     *
     * @return mixed
     */
    public function query($uri = null)
    {
        $connection = $this->getConnection();
        $connection = $this->setConnectionAuthHeader($connection);
        $connection = $this->setConnectionRepresentationHeader($connection);

        $json = $connection->query($uri);

        $data = json_decode($json['body'], true);

        if (null === $data) {
            $data['error'] = 'sdk_json_decode';
            $data['error_description'] = 'unable to decode or recursion level too deep';
            $this->error($data);

            return $data;
        }
        if (array_key_exists('error', $data)) {
            $this->error($data);

            return $data;
        }
        if (array_key_exists('type', $data) && 'error' == $data['type']) {
            $data['error'] = 'sdk_unknown';
            $ditto = $data;
            $data['error_description'] = $ditto;
            $this->error($data);

            return $data;
        }

        return $data;
    }

    public function search($query = null, $ancestor_ids = null, $file_extensions = null, $type = null, $content_types = null, $limit = 100, $offset = 0)
    {
        if (empty($query)) {
            throw new Exception('please enter a search term', Exception::INVALID_INPUT);
        }

        $uriQuery = rawurlencode($query);

        $uri = self::SEARCH_URI.'/?query='.$uriQuery;

        if (null !== $ancestor_ids) {
            $uri .= '&ancestor_folder_ids='.$ancestor_ids;
        }

        if (null !== $file_extensions) {
            $uri .= '&file_extensions='.$file_extensions;
        }

        if (null !== $type) {
            $uri .= '&type='.$type;
        }

        if (null !== $content_types) {
            $uri .= '&content_types='.$content_types;
        }

        $fields = [
            'type',
            'id',
            'url',
            'etag',
            'name',
            'description',
            'size',
            'path_collection',
            'modified_at',
            'trashed_at',
            'created_by',
            'shared_link',
            'parent',
            'item_status',
            'permissions',
            'extension',
            'representations',
        ];
        $fieldstr = join(',', $fields);
        $fieldquery = (strlen($fieldstr) > 0) ? '&fields='.$fieldstr : '';

        $data = $this->query($uri.$fieldquery.'&limit='.$limit.'&offset='.$offset);

        $found_entries = $data['entries'];

        // For now, max $limit (=1000) results

        // while ($offset + $limit < $data['total_count']) {
        //     $offset = $data['offset'] + $limit;
        //     $limit = $limit;
        //     $data = $this->query($uri . $fieldquery . "&limit=" . $limit . "&offset=" . $offset);
        //     $found_entries = array_merge($found_entries, $data['entries']);
        // }

        $items = [];
        foreach ($found_entries as $entry) {
            if ('folder' === $entry['type']) {
                $items[] = new \Box\Model\Folder\Folder($entry);
            } elseif ('file' === $entry['type']) {
                $items[] = new \Box\Model\File\File($entry);
            } elseif ('web_link' === $entry['type']) {
                $items[] = new \Box\Model\File\File($entry);
            } else {
                continue;
            }
        }

        return $items;
    }
}
