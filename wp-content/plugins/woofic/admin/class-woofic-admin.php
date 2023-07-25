<?php
include_once 'services/WooficSender.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://christiancannata.acom
 * @since      1.0.0
 *
 * @package    Woofic
 * @subpackage Woofic/admin
 */

use WooFic\Services\WooficSender;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woofic
 * @subpackage Woofic/admin
 * @author     Christian Cannata <christian@christiancannata.com>
 */
class Woofic_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    private $wooficSender;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->wooficSender = new WooficSender();

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woofic_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woofic_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woofic-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woofic_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woofic_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woofic-admin.js', array('jquery'), $this->version, false);

    }

    public function add_menu()
    {
        add_menu_page('WooFic', 'WooFic', 'manage_options', 'woofic-dashboard', array($this, 'renderHome'), plugins_url('/img/icon.png', __DIR__));

        add_submenu_page(null, 'Connetti WooFic', 'Connetti Woofic', 'manage_options', 'woofic-connetti', array($this, 'renderConnetti'));
        add_submenu_page('woofic-dashboard', 'Metodi di pagamento', 'Metodi di pagamento', 'manage_options', 'woofic-metodi-pagamento', array($this, 'renderPaymentMethods'));
        add_submenu_page('woofic-dashboard', 'Aliquote', 'Aliquote', 'manage_options', 'woofic-aliquote', array($this, 'renderAliquote'));


        add_submenu_page('woofic-dashboard', 'Impostazioni', 'Impostazioni', 'manage_options', 'woofic-impostazioni', array($this, 'renderImpostazioni'));

    }

    public function renderHome()
    {


        if (isset($_POST['woofic_client_id'])) {
            update_option('woofic_client_id', $_POST['woofic_client_id'], true);
            update_option('woofic_client_secret', $_POST['woofic_client_secret'], true);
            update_option('woofic_redirect_uri', $_POST['woofic_redirect_uri'], true);

        }

        if (isset($_GET['logout'])) {
            delete_option('woofic_token');
            delete_option('woofic_client_id');
            delete_option('woofic_client_secret');
            delete_option('woofic_redirect_uri');
            delete_option('woofic_company_id');
            delete_option('woofic_active_licence');
            delete_option('woofic_licence_key');
            delete_option('woofic_licence_email');

        }

        if (isset($_POST['woofic_licence_key'])) {

            $key = $_POST['woofic_licence_key'];

            $responseActivation = wp_remote_get(WOOFIC_ENDPOINT . '/wp-json/lmfwc/v2/licenses/activate/' . $key, [
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode(WOOFIC_ENDPOINT_USERNAME . ':' . WOOFIC_ENDPOINT_PASSWORD)
                )
            ]);

            $response = json_decode(wp_remote_retrieve_body($responseActivation), true);

            if ($response['code'] == 'lmfwc_rest_data_error') {

                $responseActivation = wp_remote_get(WOOFIC_ENDPOINT . '/wp-json/lmfwc/v2/licenses/' . $key, [
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode(WOOFIC_ENDPOINT_USERNAME . ':' . WOOFIC_ENDPOINT_PASSWORD)
                    )
                ]);
                $response = json_decode(wp_remote_retrieve_body($responseActivation), true);

            }


            if ($response['success']) {

                update_option('woofic_active_license', $response['data']);
                update_option('woofic_license_key', $key);
                update_option('woofic_license_email', $_POST['woofic_licence_email']);
            }

        }

        if (isset($_GET['logout'])) {

            delete_option('woofic_active_license');
            delete_option('woofic_license_key');
            delete_option('woofic_license_email');
        }


        include('partials/woofic-admin-display.php');
    }

    public function renderConnetti()
    {

        $app_client_id = "X2oRM6dkUdcd353SNTfWEC8c5XYqaAbd";
        $code = get_option('wfic_device_code', false);

        if ($code) {

            $response = wp_remote_post('https://api-v2.fattureincloud.it/oauth/token', [
                'body' => [
                    'client_id' => $app_client_id,
                    'device_code' => $code,
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code'
                ]
            ]);

            $result_decoded = json_decode($response['body'], true);

            update_option('woofic_token', $result_decoded);

            header("Location: admin.php?page=woofic-dashboard");
            die();
        }


        include('partials/woofic-admin-connetti.php');
    }

    public function renderImpostazioni()
    {
        include('partials/woofic-admin-impostazioni.php');
    }


    public function renderAliquote()
    {

        $companyId = get_option('woofic_company_id', false);

        if (!$companyId) {
            header("Location: admin.php?page=woofic-dashboard");
            die();
        }

        $token = get_option('woofic_token', false);

        if (!$token) {
            header("Location: admin.php?page=woofic-dashboard");
            die();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST['aliquote'] as $id => $aliquota) {
                update_option('fic_aliquota_' . $id, $aliquota);
            }
        }

        $woofic = $this->wooficSender;

        //   $ficAliquote = $woofic->getVatTypes();
        $token = get_option('woofic_token', false);

        $responseActivation = wp_remote_get('https://api-v2.fattureincloud.it/c/' . $companyId . '/info/vat_types', [
            'headers' => array(
                'Authorization' => 'Bearer ' . $token['access_token']
            )
        ]);
        $response = json_decode(wp_remote_retrieve_body($responseActivation), true);

        $ficAliquote = $response['data'];

        $all_tax_rates = [];
        $tax_classes = \WC_Tax::get_tax_classes(); // Retrieve all tax classes.
        if (!in_array('', $tax_classes)) { // Make sure "Standard rate" (empty class name) is present.
            array_unshift($tax_classes, '');
        }
        foreach ($tax_classes as $tax_class) { // For each tax class, get all rates.
            $taxes = \WC_Tax::get_rates_for_tax_class($tax_class);
            $taxes = array_map(function ($tax) {
                $tax->fic_id = get_option('fic_aliquota_' . $tax->tax_rate_id, true);

                return $tax;
            }, $taxes);
            $all_tax_rates = array_merge($all_tax_rates, $taxes);
        }

        $aliquote = $all_tax_rates;
        $fic_aliquote = $ficAliquote;

        include('partials/woofic-admin-aliquote.php');
    }

    public function renderPaymentMethods()
    {

        $companyId = get_option('woofic_company_id', false);

        if (!$companyId) {
            header("Location: admin.php?page=woofic-dashboard");
            die();
        }

        $token = get_option('woofic_token', false);

        if (!$token) {
            header("Location: admin.php?page=woofic-dashboard");
            die();
        }


        $responseActivation = wp_remote_get('https://api-v2.fattureincloud.it/c/' . $companyId . '/settings/payment_methods', [
            'headers' => array(
                'Authorization' => 'Bearer ' . $token['access_token']
            )
        ]);
        $response = json_decode(wp_remote_retrieve_body($responseActivation), true);

        $ficPaymentsMethods = $response['data'];


        $gateways = WC()->payment_gateways->get_available_payment_gateways();

        $enabled_gateways = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST['payment_methods'] as $id => $payment_method) {
                update_option('fic_' . $id, $payment_method, true);
            }
        }

        if ($gateways) {

            $gateways = array_filter($gateways, function ($gateway) {
                return $gateway->enabled == 'yes';
            });

            foreach ($gateways as $gateway) {

                if (!get_option('fic_' . $gateway->id)) {
                    $ficPaymentsMethod = array_filter($ficPaymentsMethods, function ($ficPayment) use ($gateway) {
                        return trim(strtolower($ficPayment['name'])) == trim(strtolower($gateway->method_title));
                    });

                    if (!empty($ficPaymentsMethod)) {
                        $ficPaymentsMethod = reset($ficPaymentsMethod);
                        update_option('fic_' . $gateway->id, $ficPaymentsMethod['id'], true);
                    }
                }


                $enabled_gateways[] = [
                    'id' => $gateway->id,
                    'type' => $gateway->id,
                    'name' => $gateway->method_title,
                    'fic_id' => get_option('fic_' . $gateway->id)
                ];

            }
        }


        include('partials/woofic-admin-payment-methods.php');
    }
}
