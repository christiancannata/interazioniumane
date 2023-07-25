<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\LetsBox;

class Account
{
    /**
     * Account ID.
     *
     * @var string
     */
    private $_id;

    /**
     * Account Name.
     *
     * @var string
     */
    private $_name;

    /**
     * Account Email.
     *
     * @var string
     */
    private $_email;

    /**
     * Account profile picture (url).
     *
     * @var string
     */
    private $_image;

    /**
     * Kind of Account.
     *
     * @var string
     */
    private $_type;

    /**
     * $_authorization contains the authorization token for the linked Cloud storage.
     *
     * @var \TheLion\LetsBox\Authorization
     */
    private $_authorization;

    public function __construct($id, $name, $email, $type = null, $image = null)
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_email = $email;
        $this->_image = $image;
        $this->_type = $type;
        $this->_authorization = new Authorization($this);
    }

    public function __sleep()
    {
        // Don't store authorization class in DB */
        $keys = get_object_vars($this);
        unset($keys['_authorization']);

        return array_keys($keys);
    }

    public function __wakeup()
    {
        $this->_authorization = new Authorization($this);
    }

    public function get_id()
    {
        return $this->_id;
    }

    public function get_name()
    {
        return $this->_name;
    }

    public function get_email()
    {
        return $this->_email;
    }

    public function get_image()
    {
        if (empty($this->_image)) {
            return LETSBOX_ROOTPATH.'/css/images/box_logo.svg';
        }

        return $this->_image;
    }

    public function set_id($_id)
    {
        $this->_id = $_id;
    }

    public function set_name($_name)
    {
        $this->_name = $_name;
    }

    public function set_email($_email)
    {
        $this->_email = $_email;
    }

    public function set_image($_image)
    {
        $this->_image = $_image;
    }

    public function get_type()
    {
        return $this->_type;
    }

    public function set_type($_type)
    {
        $this->_type = $_type;
    }

    /**
     * @return \TheLion\LetsBox\StorageInfo
     */
    public function get_storage_info()
    {
        $transient_name = 'letsbox_'.$this->get_id().'_driveinfo';
        $storage_info = get_transient($transient_name);

        if (empty($storage_info)) {
            $storage_info = new StorageInfo();

            if ('service' === $this->get_type()) {
                // Service Accounts don't have any drive data
                $storage_info->set_quota_total(0);
                $storage_info->set_quota_used(0);
            } else {
                App::set_current_account($this);

                /**
                 * @var Box\Model\User
                 */
                $storage_info_data = API::get_account_info();

                $storage_info->set_quota_total($storage_info_data->getSpaceAmount());
                $storage_info->set_quota_used($storage_info_data->getSpaceUsed());
            }
            set_transient($transient_name, $storage_info, DAY_IN_SECONDS);
        }

        return $storage_info;
    }

    /**
     * @return \TheLion\LetsBox\Authorization
     */
    public function get_authorization()
    {
        return $this->_authorization;
    }
}
