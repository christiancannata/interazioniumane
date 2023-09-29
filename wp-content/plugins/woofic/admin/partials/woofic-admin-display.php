<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   WooFic
 * @author    Christian Cannata <christian@christiancannata.com>
 * @copyright 2022 Christian Cannata
 * @license   GPL 2.0+
 * @link      https://christiancannata.com
 */
?>

<div class="wrap">

    <div class="postbox" style="max-width: inherit">
        <h2 class="title">Chiave di Licenza WooFic</h2>

        <?php

        $woofic_active_license = get_option('woofic_active_license', false);
        if (!$woofic_active_license): ?>
            <form method="POST" action="">
                <p>
                    Il tuo sito non è collegato con WooFic, inserisci la tua chiave di licenza ricevuta.
                </p>
                <label>Chiave di Licenza</label><br>
                <input autocomplete="off" style="width:100%" type="text" name="woofic_licence_key" required

                    <?php if (get_option('woofic_licence_key')): ?>
                        value="<?php echo get_option('woofic_licence_key'); ?>"
                    <?php endif; ?>
                >
                <br><br>
                <label>Email di acquisto della licenza</label><br>
                <input autocomplete="off" style="width:100%" type="text" name="woofic_licence_email" required
                    <?php if (get_option('woofic_licence_email')): ?>
                        value="<?php echo get_option('woofic_licence_email'); ?>"
                    <?php endif; ?>
                >
                <br><br>
                <button class="button-primary" type="submit">Attiva la tua licenza</button>

            </form>
        <?php else: ?>
            <?php

            $app_client_id = "uDLsAoTsy573Kq3VjVVNbrqMhxor5bZw";

            $response = wp_remote_post('https://api-v2.fattureincloud.it/oauth/device', [
                'body' => [
                    'client_id' => $app_client_id,
                    'scope' => 'situation:r entity.clients:a issued_documents.invoices:a issued_documents.receipts:a receipts:a archive:a emails:r settings:a'
                ]
            ]);

            $result_decoded = json_decode($response['body'], true);

            $device_code_forwfic = $result_decoded['data']['device_code'];
            $wfic_user_code = $result_decoded['data']['user_code'];

            $accessToken = get_option('woofic_access_token');

            $wooficLicenceKey = get_option('woofic_license_key');
            $wooficLicence = get_option('woofic_active_license');

            ?>
            <p>Correttamente collegato a Woofic</p>
            <br><br>
            <table>
                <tr>
                    <td><b>Chiave di licenza</b></td>
                    <td><?php echo $wooficLicence['licenseKey'] ?></td>
                </tr>
                <tr>
                    <td><b>Email</b></td>
                    <td><?php echo get_option('woofic_license_email') ?></td>
                </tr>
                <tr>
                    <td><b>Attivata il</b></td>
                    <?php if ($wooficLicence['createdAt']): ?>
                        <td> Attiva
                            dal <?php echo (new \DateTime($wooficLicence['createdAt']))->format("d-m-Y") ?> </td>
                    <?php else: ?>
                        <td>Non ancora attivata</td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td><b>Scade il</b></td>
                    <td><?php echo (new \DateTime($wooficLicence['expiresAt']))->format("d-m-Y") ?></td>
                </tr>
            </table><br><br>

            <a href="/wp-admin/admin.php?logout_woofic=1&page=woofic" class="button-primary">Disconnetti
                da
                WooFic</a>
        <?php endif; ?>
    </div>

    <div class="postbox" style="max-width: inherit">
        <h2 class="title">Connessione a FattureInCloud</h2>

        <?php
        $accessToken = get_option('woofic_token', false);
        if (!$accessToken):

            $app_client_id = "uDLsAoTsy573Kq3VjVVNbrqMhxor5bZw";

            $response = wp_remote_post('https://api-v2.fattureincloud.it/oauth/device', [
                'body' => [
                    'client_id' => $app_client_id,
                    'scope' => 'situation:r entity.clients:a issued_documents.invoices:a issued_documents.receipts:a receipts:a archive:a emails:r settings:a'
                ]
            ]);

            $result_decoded = json_decode($response['body'], true);
            $device_code_forwfic = $result_decoded['data']['device_code'];
            $wfic_user_code = $result_decoded['data']['user_code'];


            //echo "Device Code = " . $device_code_forwfic  ."<br>";
            echo "<p><b>1)</b> Prendi lo User Code = <b><span style='background: white; padding: 0.30em 0.80em'>" . $result_decoded['data']['user_code'] . "</span></b></p>";


            update_option('wfic_device_code', $device_code_forwfic);

            /*
            $type = 'updated';
            $message = __('User Code attivato: '.$wfic_user_code, 'woo-fattureincloud-premium');
            add_settings_error('woo-fattureincloud-premium', esc_attr('settings_updated'), $message, $type);
            settings_errors('woo-fattureincloud-premium');
            */


            echo "<p><b> 2)</b> Vai a questo indirizzo <a href='https://secure.fattureincloud.it/connetti' onClick=\"MyWindow=window.open('https://secure.fattureincloud.it/connetti','wfic_connection','width=600,height=700'); return false;\">https://secure.fattureincloud.it/connetti</a> </p>

            <p> <b>3)</b> inserisci lo User Code <b><span style='background: white; padding: 0.30em 0.80em'>" . $result_decoded['data']['user_code'] . "</span></b></p>

            <p> <b>4)</b>   clicca su <b><span style='background: lightblue; padding: 0.30em 0.80em'>Continua</span></b></p>

            <p> <b> 5) </b> e poi Clicca su <b><span style='background: lightblue; padding: 0.30em 0.80em'>Autorizza</span></b></p>

            <p><b>6)</b> torna qui e Clicca sul tab <b><span style='background: lightgrey; padding: 0.30em 0.80em'><a href=\"?page=woofic-connetti\">Connetti </a></span></b> </p>";

        else:
            ?>
            <p>Correttamente collegato a FattureInCloud con token
                *******<?php echo substr($accessToken['access_token'], -4); ?></p>

            <?php

            $companies = get_option('woofic_companies', false);


            if (!$companies) {
                $responseCompanies = wp_remote_get("https://api-v2.fattureincloud.it/user/companies", [
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $accessToken['access_token']
                    )
                ]);

                $companies = json_decode($responseCompanies['body'], true);
                $companies = $companies['data']['companies'];

                update_option('woofic_companies', $companies);

            }

            $selectedCompany = get_option('woofic_company_id', null);
            if (!$selectedCompany) {
                update_option('woofic_company_id', $companies[0]['id']);
                $selectedCompany = $companies[0]['id'];
            }

            foreach ($companies as $company):
                ?>
                <label>
                    <input
                            autocomplete="off"
                        <?php if ($selectedCompany == $company['id']): ?> checked <?php endif; ?>
                            type="radio" name="woofic_company_id" value="<?php echo $company['id']; ?>">
                    <?php echo $company['name']; ?>
                </label>
            <?php endforeach; ?>
            <br><br>
            <a href="/wp-admin/admin.php?logout=1&page=woofic-dashboard" class="button-primary">Disconnetti
                da
                FattureInCloud</a>
        <?php endif; ?>
    </div>

</div>
