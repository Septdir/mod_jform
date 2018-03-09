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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

if ($ajax)
{
	HTMLHelper::_('jquery.framework');
	HTMLHelper::_('script', 'media/mod_jform/ajax.min.js', array('version' => 'auto'));
}
$data_attribute = ($ajax) ? ' data-mod_jform="ajax"' : '';

echo '<form id="' . $form->getName() . '" action="' . $action . '"' . $data_attribute . ' method="post">';

// Render fieldsets
foreach ($form->getFieldsets() as $fieldset)
{
	echo '<fieldset>';
	echo (!empty($fieldset->label)) ? '<legend>' . $fieldset->label . '</legend>' : '';
	echo $form->renderFieldset($fieldset->name);
	echo '</fieldset>';
}

// Render captcha
$captcha = $params->get('captcha');
if ($captcha == 1 || ($captcha == 2 && Factory::getUser()->guest))
{
	echo $form->renderField('captcha');
}

// Render hidden fields
if ($return)
{
	echo '<input type="hidden" name="return" value="' . $return . '" />';
}
echo HTMLHelper::_('form.token');

// Render submit button
echo '<div class="control-group"><button type="submit">' . Text::_('JSUBMIT') . '</button>	</div>';

echo '</form>';