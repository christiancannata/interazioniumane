<?php

use Uncanny_Automator\Recipe;

/**
 * Class Add_Wpcp_LetsBox_Integration.
 */
class Add_Wpcp_LetsBox_Integration
{
    use Recipe\Integrations;

    /**
     * Add_Wpcp_LetsBox_Integration constructor.
     */
    public function __construct()
    {
        $this->setup();
    }

    protected function setup()
    {
        $this->set_integration('wpcp-letsbox');
        $this->set_external_integration(true);
        $this->set_name('Lets-Box (Box)');
        $this->set_icon('box_logo.svg');
        $this->set_icon_path(LETSBOX_ROOTDIR.'/css/images/');
        $this->set_plugin_file_path(LETSBOX_ROOTDIR.'/lets-box.php');
    }

    /**
     * @return bool
     */
    public function plugin_active()
    {
        return true;
    }
}
