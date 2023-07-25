/* global w_js_vars */
import './styles/public.scss';
import $ from 'jquery';

(($) => {
	'use strict';
	$('#billing_customer_type').change(function () {
		if ($(this).val() == 'COMPANY') {
			$('#billing_vat_field input').attr('required', 'required');
			$('#billing_vat_field').show();
			$('#billing_vat_field .optional').hide();

			$('#billing_company_field').show();
			$('#billing_company_field input').attr('required', 'required');
			$('#billing_company_field .optional').hide();

			$('#billing_pec_field').show();
			$('#billing_sdi_field').show();
		} else {
			$('#billing_vat_field input').removeAttr('required');
			$('#billing_vat_field').hide();

			$('#billing_company_field input').removeAttr('required');

			$('#billing_company_field').hide();
			$('#billing_pec_field').hide();
			$('#billing_sdi_field').hide();
		}
	});
})(jQuery);
