<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://christiancannata.acom
 * @since      1.0.0
 *
 * @package    Woofic
 * @subpackage Woofic/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woofic
 * @subpackage Woofic/public
 * @author     Christian Cannata <christian@christiancannata.com>
 */
class Woofic_Public
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

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woofic-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woofic-public.js', array('jquery'), $this->version, false);

    }

    public function onOrderCompleted($order_id)
    {

        $wooficSender = new \Christiancannata\Woofic\WooficSender();

        $order = wc_get_order($order_id);

        $enabledTypes = get_option('woofic_document_types', [
            'INVOICE',
            'RECEIPT',
            'CORRISPETTIVI'
        ]);

        //check enabled types
        $type = $order->get_meta('_billing_type');

        if (empty($type)) {
            $type = 'RECEIPT';
        }

        if (empty($enabledTypes) || !in_array($type, $enabledTypes)) {
            $order->add_order_note('Errore creazione ordine su FattureInCloud:<br>Tipologia non abilitata, controllare le impostazioni nel plugin nella sezione "Avanzate"');
        } else {

            try {
                $wooficSender->createInvoice($order);
            } catch (\FattureInCloud\ApiException $e) {

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
                $wooficSender->createCorrispettivo($order);
            }

        }
    }

    public function addCheckoutFields($fields)
    {
        $fields = ['invoice_type' => [
                'label' => __('Vuoi ricevere la fattura?', 'Woofic'),
                'required' => true,
                'type' => 'select',
                'options' => array(
                    'INVOICE' => __('Fattura', 'Woofic'),
                    'RECEIPT' => __('Ricevuta', 'Woofic')
                )]
            ] + $fields;

        return $fields;
    }

}
