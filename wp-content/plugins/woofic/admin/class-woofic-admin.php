<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';

//include_once 'services/WooficSender.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://christiancannata.acom
 * @since      1.0.0
 *
 * @package    Woofic
 * @subpackage Woofic/admin
 */

use FattureInCloud\Api\InfoApi;
use FattureInCloud\ApiException;

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

        $this->wooficSender = new Christiancannata\Woofic\WooficSender();

        define('WOOFIC_ENDPOINT', 'https://woofic.it');
        define('WOOFIC_ENDPOINT_USERNAME', 'ck_3427c1863c554d5d721d0865409a6dc5f87b9917');
        define('WOOFIC_ENDPOINT_PASSWORD', 'cs_51c4228ca21190d0fb34e93f5dcf85e5ff10ee58');

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


        \add_submenu_page(
            null,
            'Importa Ordine',
            'manage_options',
            'manage_options',
            'import-order',
            [$this, 'importOrder']);
    }

    public function add_metaboxes()
    {

        add_meta_box(
            'woofic-box',
            'FattureInCloud',
            function ($post) {
                $order = wc_get_order($post->ID);

                if ($order->get_status() == 'completed') {
                    $invoiceId = get_post_meta($post->ID, 'woofic_invoice_id', true);
                    if ($invoiceId): ?>
                        Importato con ID <?php echo $invoiceId; ?><br>
                        <a href="https://secure.fattureincloud.it/invoices-view-<?php echo $invoiceId; ?>"
                           target="_blank">Apri su FattureInCloud</a>
                        <br><br>
                        <a href="/wp-admin/admin.php?page=import-order&order_id=<?php echo $post->ID; ?>"
                           class="button import-fic button-primary" href="#">Aggiorna manualmente</a><br><br>
                    <?php
                    else: ?>
                        <span>Non ancora importato</span><br><br>
                        <a href="/wp-admin/admin.php?page=import-order&order_id=<?php echo $post->ID; ?>"
                           class="button import-fic button-primary">Importa ora</a>
                    <?php
                    endif;
                } else {
                    echo "<strong>Ordine non completato.</strong>";
                }
            },
            'shop_order',
            'side',
            'core'
        );
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

        $app_client_id = "uDLsAoTsy573Kq3VjVVNbrqMhxor5bZw";
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

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            update_option('selected_account_payment_id', $_POST['account_payment_id']);
            update_option('woofic_suffix', $_POST['woofic_suffix']);
            update_option('woofic_document_types', $_POST['woofic_document_types']);
        }


        $config = $this->wooficSender->getConfig();

        $apiInstance = new InfoApi(
            null,
            $config
        );

        $company_id = get_option('woofic_company_id');

        $result = $apiInstance->listPaymentAccounts($company_id);
        $payments_accounts = $result->getData();

        $orderStatuses = wc_get_order_statuses();
        unset($orderStatuses['wc-pending']);
        unset($orderStatuses['wc-refunded']);
        unset($orderStatuses['wc-failed']);
        unset($orderStatuses['wc-checkout-draft']);
        unset($orderStatuses['wc-cancelled']);

        $woofic_prefix = get_option('woofic_prefix');

        $selected_account_payment_id = get_option('selected_account_payment_id');
        $order_statuses = $orderStatuses;
        $fic_automatic_status = get_option('fic_automatic_status', 0);
        $woofic_document_types = get_option('woofic_document_types', [
            'INVOICE',
            'RECEIPT',
            'CORRISPETTIVI'
        ]);


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

        $token = $this->wooficSender->getToken();

        if (!$token) {
            header("Location: admin.php?page=woofic-dashboard");
            die();
        }

        $responseActivation = wp_remote_get('https://api-v2.fattureincloud.it/c/' . $companyId . '/settings/payment_methods', [
            'headers' => array(
                'Authorization' => 'Bearer ' . $token
            )
        ]);

        $body = wp_remote_retrieve_body($responseActivation);
        $response = json_decode($body, true);

        $ficPaymentsMethods = $response['data'];

        $company_id = get_option('woofic_company_id');

        $config = $this->wooficSender->getConfig();

        $apiInstance = new InfoApi(
            null,
            $config
        );

        $result = $apiInstance->listPaymentAccounts($company_id);
        $ficPaymentAccounts = $result->getData();

        $gateways = WC()->payment_gateways->get_available_payment_gateways();

        $enabled_gateways = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST['payment_methods'] as $id => $payment_method) {
                update_option('fic_' . $id, $payment_method, true);
            }

            foreach ($_POST['payment_accounts'] as $id => $payment_method) {
                update_option('fic_' . $id . '_payment_account', $payment_method, true);
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
                    'fic_id' => get_option('fic_' . $gateway->id),
                    'payment_account_id' => get_option('fic_' . $gateway->id . '_payment_account', null)
                ];

            }
        }


        include('partials/woofic-admin-payment-methods.php');
    }

    public function importOrder()
    {

        if ($_GET['order_id']) {

            $order = wc_get_order($_GET['order_id']);

            $enabledTypes = get_option('woofic_document_types', [
                'INVOICE',
                'RECEIPT',
                'CORRISPETTIVI'
            ]);

            //check enabled types
            $type = $order->get_meta('_billing_type');

            if (empty($type)) {
                $type = 'INVOICE';
            }

            if (empty($enabledTypes) || !in_array($type, $enabledTypes)) {
                $order->add_order_note('Errore creazione ordine su FattureInCloud:<br>Tipologia non abilitata, controllare le impostazioni nel plugin nella sezione "Avanzate"');
                wp_redirect(wp_get_referer());
            } else {

                try {
                    $this->wooficSender->createInvoice($order);
                } catch (ApiException $e) {

                    if ($e->getResponseBody()) {
                        $error = json_decode($e->getResponseBody(), true);

                        $message = $error['error']['message'];

                        $errors = [];

                        $validation = $error['error']['validation_result'];


                        foreach ($validation as $field => $singleError) {
                            $errors[] = $field . ": " . reset($singleError);
                        }

                        $order->add_order_note('Errore creazione ordine su FattureInCloud: ' . $message . "<br>" . implode("<br>", $errors));
                    }

                } catch (\Exception $e) {

                    $order->add_order_note('Errore creazione ordine su FattureInCloud: ' . $e->getMessage());
                }


                //if receipt create corrispettivo

                if ($type == 'RECEIPT' && in_array('CORRISPETTIVO', $enabledTypes)) {

                    $wooficSender = new WooficSender();
                    $wooficSender->createCorrispettivo($order);

                }

            }


            wp_redirect(wp_get_referer());
        }

    }

}
