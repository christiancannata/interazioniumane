<div class="postbox" style="max-width: inherit">
    <h2 class="title">Aliquote IVA</h2>

    <p>

    </p>
    <form method="POST" action="">
        <table class="table">
            <thead>
            <th>Nazione</th>
            <th>Aliquota</th>
            <th>Aliquota WooCommerce</th>
            <th>Aliquota FattureInCloud</th>
            </thead>
            <tbody>
            <?php foreach ($aliquote as $tax): ?>
                <tr>
                    <td><?php echo $tax->tax_rate_country; ?></td>
                    <td><?php echo number_format($tax->tax_rate, 2); ?>%</td>
                    <td><?php echo $tax->tax_rate_name; ?></td>
                    <td>
                        <select autocomplete="off" name="aliquote[<?php echo $tax->tax_rate_id; ?>]"
                                class="form-control">
                            <?php foreach ($fic_aliquote as $fixAliquota): ?>
                                <option value="<?php echo $fixAliquota['id'] ?>"
                                    <?php if ($fixAliquota['id'] == $tax->fic_id): ?> selected <?php endif; ?>
                                ><?php echo $fixAliquota['value'] . '% ' . $fixAliquota['description']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>

        <button class="button button-primary button-large">Salva e sincronizza</button>
    </form>


</div>