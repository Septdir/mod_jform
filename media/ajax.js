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
		$('body').on('submit', '[data-mod_jform="ajax"]', function () {
			var form = $(this),
				messages = $(form).find('.messages');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: $(form).attr('action'),
				data: $(form).serializeArray(),
				beforeSend: function () {
					$(messages).hide();
					$(messages).find('.message').hide().html('');
				},
				complete: function () {
					if (typeof grecaptcha !== 'undefined' && grecaptcha && grecaptcha.reset) {
						grecaptcha.reset();
					}
				},
				success: function (response) {
					if (response.success) {
						$(messages).find('.message.success').html(response.messages.success.join('<br />')).show();
						$(form)[0].reset();
					}
					else {
						$(messages).show();
						$(messages).find('.message.error').html(response.message.replace(/\n/g, '<br />')).show();
					}
					$(messages).show();
				},
				error: function (response) {
					$(messages).find('.message.error').html(response.status + ':' + response.statusText).show();
					$(messages).show();
				}
			});

			return false;
		});
	});
})(jQuery);