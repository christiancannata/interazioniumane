<?php

// Include Header
include_once 'header.php'; // phpcs:ignore

global $wpowp_fs;

$option = $this->get_settings(); // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

?>

<form id="<?php echo esc_attr( WPOWP_PLUGIN_PREFIX ); ?>settings-form" method="post" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
	<table class="form-table wpowp-content-table">
		<tbody>
			<tr class="wpowp-admin-separator">
				<th scope="row"><label
						for="wpowp_order_status"><?php esc_html_e( 'Order Status', WPOWP_TEXT_DOMAIN ); ?></label>
				</th>
				<td>					
					<select name="wpowp_order_status" id="wpowp_order_status">
						<?php
						if ( ! empty( $this->order_status_list() ) ) {
							$status_list = $this->order_status_list(); //phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable 
							foreach ( $status_list as $key => $label ) {
								echo '<option value="' . wp_kses_post( $key ) . '" ' . ( $key === $option['order_status'] ? 'selected' : '' ) . '>' . wp_kses_post( $label ) . '</option>';
							}
						}
						?>
					</select>
					<p><?php esc_html_e( '( Order status after placing order )', WPOWP_TEXT_DOMAIN ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Skip Cart', WPOWP_TEXT_DOMAIN ); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<span>checkbox</span>
						</legend>
						<label for="wpowp_skip_cart">
							<input type="hidden" name="wpowp_skip_cart" value="no" />
							<input name="wpowp_skip_cart" type="checkbox" id="wpowp_skip_cart" value="yes"
								<?php echo ( true === wc_string_to_bool( $option['skip_cart'] ) ) ? 'checked' : ''; ?> />
							
						</label>
					</fieldset>
					<p><?php esc_html_e( '( Skip Cart & Go to Checkout on Add to Cart )', WPOWP_TEXT_DOMAIN ); ?></p>
				</td>
			</tr>	
			<?php
			if ( $wpowp_fs->is_paying() ) {
				?>
			<tr class="wpowp-admin-separator">
				<th scope="row"><?php esc_html_e( 'Add to cart text', WPOWP_TEXT_DOMAIN ); ?></th>
				<td>
					<input name="wpowp_add_cart_text" type="text" value="<?php echo esc_attr( $option['add_cart_text'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label
						for="wpowp_remove_shipping_adress"><?php esc_html_e( 'Remove shipping rates', WPOWP_TEXT_DOMAIN ); ?></label>
				</th>
				<td>					
					<select name="wpowp_remove_shipping" >
						<option value="no" <?php echo ( false === wc_string_to_bool( $option['remove_shipping'] ) ) ? 'selected' : ''; ?>><?php echo esc_html_e( 'No' ); ?></option>
						<option value="yes" <?php echo ( true === wc_string_to_bool( $option['remove_shipping'] ) ) ? 'selected' : ''; ?>><?php echo esc_html_e( 'Yes' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label
						for="wpowp_remove_privacy_policy_text"><?php esc_html_e( 'Remove checkout privacy text', WPOWP_TEXT_DOMAIN ); ?></label>
				</th>
				<td>					
					<select name="wpowp_remove_privacy_policy_text">
						<option value="no" <?php echo ( false === wc_string_to_bool( $option['remove_privacy_policy_text'] ) ) ? 'selected' : ''; ?>><?php echo esc_html_e( 'No' ); ?></option>
						<option value="yes" <?php echo ( true === wc_string_to_bool( $option['remove_privacy_policy_text'] ) ) ? 'selected' : ''; ?>><?php echo esc_html_e( 'Yes' ); ?></option>
					</select>
				</td>
			</tr>
			<tr class="wpowp-admin-separator">
				<th scope="row"><label
						for="wpowp_remove_checkout_terms_conditions"><?php esc_html_e( 'Remove checkout terms and conditions', WPOWP_TEXT_DOMAIN ); ?></label>
				</th>
				<td>					
					<select name="wpowp_remove_checkout_terms_conditions">
						<option value="no" <?php echo ( false === wc_string_to_bool( $option['remove_checkout_terms_conditions'] ) ) ? 'selected' : ''; ?>><?php echo esc_html_e( 'No' ); ?></option>
						<option value="yes" <?php echo ( true === wc_string_to_bool( $option['remove_checkout_terms_conditions'] ) ) ? 'selected' : ''; ?>><?php echo esc_html_e( 'Yes' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Free Product', WPOWP_TEXT_DOMAIN ); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<span>checkbox</span>
						</legend>
						<label for="wpowp_free_product">
							<input type="hidden" name="wpowp_free_product" value="no" />
							<input name="wpowp_free_product" type="checkbox" id="wpowp_free_product" value="yes" <?php echo ( true === wc_string_to_bool( $option['free_product'] ) ) ? 'checked' : ''; ?> />
						</label>
						<p><?php esc_html_e( '( For WooCommerce price label of $0.00, show custom text, such as the word “FREE”)', WPOWP_TEXT_DOMAIN ); ?></p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Free product text', WPOWP_TEXT_DOMAIN ); ?></th>
				<td>
					<input name="wpowp_free_product_text" type="text" value="<?php echo esc_attr( $option['free_product_text'] ); ?>" />
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>		
			<?php
			if ( $wpowp_fs->is_not_paying() ) {
				?>
				
				<table class="form-table wpowp-content-table" >
					<tbody>			
						<tr>
							<th scope="row"><?php esc_html_e( 'For PRO Version', WPOWP_TEXT_DOMAIN ); ?></th>
							<td>
								<?php
									esc_html_e( 'Please ', WPOWP_TEXT_DOMAIN );
									echo '<a href="' . esc_url( $wpowp_fs->get_upgrade_url() ) . '">' .
									esc_html( 'Upgrade Now!', WPOWP_TEXT_DOMAIN ) .
									'</a>';
								?>
							</td>
						</tr>
						<tr class="wpowp-admin-separator">
							<th scope="row"><?php esc_html_e( 'Add to cart text', WPOWP_TEXT_DOMAIN ); ?></th>
							<td>
								<input readonly type="text" value="<?php echo esc_attr( $option['add_cart_text'] ); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><label
									for="wpowp_remove_shipping_adress"><?php esc_html_e( 'Remove shipping rates', WPOWP_TEXT_DOMAIN ); ?></label>
							</th>
							<td>					
								<select disabled>
									<option><?php echo esc_html_e( 'No' ); ?></option>
									<option><?php echo esc_html_e( 'Yes' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label
									for="wpowp_remove_privacy_policy_text"><?php esc_html_e( 'Remove checkout privacy text', WPOWP_TEXT_DOMAIN ); ?></label>
							</th>
							<td>					
								<select disabled>
									<option><?php echo esc_html_e( 'No' ); ?></option>
									<option><?php echo esc_html_e( 'Yes' ); ?></option>
								</select>
							</td>
						</tr>
						<tr class="wpowp-admin-separator">
							<th scope="row"><label
									for="wpowp_remove_checkout_terms_conditions"><?php esc_html_e( 'Remove checkout terms and conditions', WPOWP_TEXT_DOMAIN ); ?></label>
							</th>
							<td>					
								<select disabled>
									<option><?php echo esc_html_e( 'No' ); ?></option>
									<option><?php echo esc_html_e( 'Yes' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Free Product', WPOWP_TEXT_DOMAIN ); ?></th>
							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span>checkbox</span>
									</legend>
									<label for="wpowp_status">
										<input disabled name="" type="checkbox" id="" value="yes"  <?php echo ( true === wc_string_to_bool( $option['free_product'] ) ) ? 'checked' : ''; ?> />
									</label>
									<p><?php esc_html_e( '( For WooCommerce price label of $0.00, show custom text, such as the word “FREE”)', WPOWP_TEXT_DOMAIN ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Free product text', WPOWP_TEXT_DOMAIN ); ?></th>
							<td>
								<input disabled name="" type="text" value="<?php echo esc_attr( $option['free_product_text'] ); ?>" />
							</td>
						</tr>
					</tbody>
				</table>
				
			<?php } ?>

		</tbody>
	</table>
	<p class="submit"><input type="submit" name="<?php echo esc_attr( WPOWP_PLUGIN_PREFIX ); ?>settings-submit" id="<?php echo esc_attr( WPOWP_PLUGIN_PREFIX ); ?>settings-submit" class="button button-primary"
			value="<?php esc_attr_e( 'Save Changes', WPOWP_TEXT_DOMAIN ); ?>"></p>
</form>
