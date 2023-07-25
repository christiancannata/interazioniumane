<div class="wrap">

    <div id="tabs" class="settings-tab">

        <div class="metabox-holder">


            <div class="postbox" style="max-width: inherit">
                <h2 class="title">Metodi di pagamento</h2>

                <p>
                    <?php
                    if (empty($ficPaymentsMethods)):
                    ?>
                <div class="alert alert-danger">Non ci sono metodi di pagamento su FattureInCloud!</div>
                <?php
                endif;
                ?>

                </p>
                <form method="POST" action="">
                    <table class="table">
                        <thead>
                        <th>Metodo di pagamento WooCommerce</th>
                        <th>Metodo di pagamento FattureInCloud</th>
                        </thead>
                        <tbody>
                        <?php foreach ($enabled_gateways as $payment): ?>
                            <tr>
                                <td><?php echo $payment['name']; ?></td>
                                <td>
                                    <?php
                                    if (empty($ficPaymentsMethods) || !$payment['fic_id']): ?>
                                        <div class="alert alert-danger">Nessun metodo di pagamento associato.</div>
                                    <?php
                                    endif;
                                    ?>
                                    <select autocomplete="off" name="payment_methods[<?php echo $payment['id']; ?>]"
                                            class="form-control">
                                        <?php foreach ($ficPaymentsMethods as $paymentMethod): ?>
                                            <option value="<?php echo $paymentMethod['id'] ?>"
                                                <?php if ($paymentMethod['id'] == $payment['fic_id']): ?> selected <?php endif; ?>
                                            ><?php echo $paymentMethod['name'] ?></option>
                                        <?php endforeach; ?>
                                        <option value="0"
                                            <?php if ($paymentMethod['id'] == 0): ?> selected <?php endif; ?>
                                        >Disabilita invio su Fatture in Cloud
                                        </option>
                                    </select>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>

                    </table>

                    <button class="button button-primary button-large">Salva e sincronizza</button>
                </form>


            </div>


        </div>

    </div>

</div>
