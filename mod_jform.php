<?php
/**
 * @package    JForm Module
 * @version    1.0.0
 * @author     Igor Berdicheskiy - septdir.ru
 * @copyright  Copyright (c) 2013 - 2017 Igor Berdicheskiy. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://septdir.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

require_once __DIR__ . '/helper.php';
$helper = new modJFormHelper;

$form = $helper->getForm($module);

if ($form)
{
	$formParams = new Registry($params->get('form', ''));
	$Itemid     = Factory::getApplication()->input->get('Itemid');

	$ajax   = ($formParams->get('ajax', 0)) ? ' data-modjform-ajax' : '';
	$action = ($formParams->get('action', 0)) ? $formParams->get('action') :
		'/index.php?option=com_ajax&module=' . $module->name . '&Itemid=' . $Itemid . '&module_id=' . $module->id;

	if (!$formParams->get('action', 0) && $ajax)
	{
		$action .= '&format=json';

	}

	require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
}
else
{
	echo Text::_('MOD_JFORM_ERRORS_NOFORM');
}

