<?php
/**
 * @package    JForm Module
 * @version    1.0.0
 * @author     Igor Berdicheskiy - septdir.ru
 * @copyright  Copyright (c) 2013 - 2018 Igor Berdicheskiy. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://septdir.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if ($ajax)
{
	HTMLHelper::_('jquery.framework');
	HTMLHelper::_('script', 'media/mod_jform/ajax.min.js', array('version' => 'auto'));
}

?>
<form id="<?php echo $form->getName(); ?>" action="<?php echo $action; ?>" <?php echo $ajax; ?>>
	<?php echo $form->renderFieldset('all'); ?>
	<div class="control-group">
		<button type="submit"><?php echo Text::_('JSUBMIT'); ?></button>
	</div>
</form>



