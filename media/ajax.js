/*
 * @package    JForm Module
 * @version    1.0.0
 * @author     Igor Berdicheskiy - septdir.ru
 * @copyright  Copyright (c) 2013 - 2018 Igor Berdicheskiy. All rights reserved.
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
	});
})(jQuery);