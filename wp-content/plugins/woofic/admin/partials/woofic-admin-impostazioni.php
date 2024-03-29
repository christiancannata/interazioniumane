<div class="wrap">

    <div id="tabs" class="settings-tab">

        <div class="metabox-holder">


            <div class="postbox" style="max-width: inherit">
                <h2 class="title">Impostazioni</h2>


                <form method="POST" action="">

                    <!--   <h2 class="title">Conto di Saldo collegato</h2>
                    <p>
                        Seleziona il conto da utilizzare per registrare le tue fatture su FattureInCloud.
                    </p>
                    <?php foreach ($payments_accounts as $payment): ?>
                        <label>
                            <input type="radio" name="account_payment_id" value="<?php echo $payment->getId() ?>"
                                <?php if ($selected_account_payment_id == $payment->getId()): ?> checked <?php endif; ?>
                            >
                            <?php echo $payment->getName(); ?>
                        </label><br><br>
                    <?php endforeach; ?>

                    <br><br> -->
                    <h2 class="title">Suffisso numerazione della fattura</h2>
                    <p>
                        Se vuoi inserire un prefisso alla numerazione delle tue fatture su FattureInCloud, compila il
                        campo di testo
                        inserendo il valore che preferisci.
                    </p>
                    <input type="text" name="woofic_suffix" placeholder="/E"
                           <?php if ($woofic_suffix): ?>value="<?php echo $woofic_suffix; ?>"<?php endif; ?>
                    >
                    <br><br>

                    <br><br>
                    <h2 class="title">Creazione automatica su FattureInCloud</h2>
                    <p>

                    </p>

                    <select autocomplete="off" name="fic_automatic_status"
                            class="form-control">
                        <option value="0"
                            <?php if ($fic_automatic_status == 0): ?> selected <?php endif; ?>
                        >Invio automatico disabilitato
                        </option>
                        <?php foreach ($order_statuses as $value => $order_status): ?>
                            <option value="<?php echo $value; ?>"
                                <?php if ($value === $fic_automatic_status): ?> selected <?php endif; ?>
                            >Quando l'ordine vas in stato "<?php echo $order_status ?>"
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <br><br>
                    <h2 class="title">Documenti da creare su FattureInCloud</h2>
                    <p>

                    </p>


                    <label>
                        <input type="checkbox" name="woofic_document_types[]" value="INVOICE"
                            <?php if (in_array('INVOICE', $woofic_document_types)): ?> checked <?php endif; ?>
                        >
                        Fatture
                    </label><br><br>
                    <label>
                        <input type="checkbox" name="woofic_document_types[]" value="RECEIPT"
                            <?php if (in_array('RECEIPT', $woofic_document_types)): ?> checked <?php endif; ?>

                        >
                        Ricevute
                    </label><br><br>
                    <label>
                        <input type="checkbox" name="woofic_document_types[]" value="CORRISPETTIVO"
                            <?php if (in_array('CORRISPETTIVI', $woofic_document_types)): ?> checked <?php endif; ?>
                        >
                        Corrispettivi
                    </label><br><br>

                    <br>
                    <button class="button button-primary button-large">Salva</button>


                </form>
            </div>
        </div>
    </div>
</div>