/*
 * @package    JForm Module
 * @version    1.0.0
 * @author     Igor Berdicheskiy - septdir.ru
 * @copyright  Copyright (c) 2013 - 2017 Igor Berdicheskiy. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://septdir.ru
 */

(function ($) {
	$(document).ready(function () {
		$('body').on('submit', '[data-modjform-ajax]', function () {
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: $(this).attr('action'),
				data: $(this).serializeArray(),
				beforeSend: function (response) {
				},
				complete: function () {
					if (typeof grecaptcha !== 'undefined' && grecaptcha && grecaptcha.reset) {
						grecaptcha.reset();
					}
				},
				success: function (response) {
					console.log(response);
				},
				error: function (response) {
				}
			});

			return false;
		});
		// $('[data-modjform-ajax]').each(function () {
		// 	// Prepare variables
		// 	var block = $(this),
		// 		data = $.parseJSON('[' + block.data('mod-freelancehunt-profile') + ']');
		//
		// 	// Get Profile HTML
		// 	$.ajax({
		// 		type: 'POST',
		// 		dataType: 'json',
		// 		url: '/index.php?option=com_ajax&module=freelancehunt_profile&format=json&Itemid=' + data[1],
		// 		data: {module_id: data[0]},
		// 		success: function (response) {
		// 			if (response.data) {
		// 				block.html(response.data);
		// 			}
		// 		}
		// 	});
		// });
	});
})(jQuery);