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

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

// Include Module Helper
require_once __DIR__ . '/helper.php';
$helper = new modJFormHelper($module);

// Get form
$form = $helper->getForm();
if ($form)
{
	$ajax           = $params->get('ajax');
	$default_action = JUri::root(true) . '/index.php?option=com_ajax&module=' . $module->name;
	$action         = $params->get('action', $default_action);

	// Set default action params
	if ($action == $default_action)
	{
		$action .= '&module_id=' . $module->id;
		$action .= '&Itemid=' . Factory::getApplication()->input->get('Itemid');
		$action .= ($ajax) ? '&ajax=1' : '';
		$action .= ($ajax) ? '&format=json' : '&format=raw';
	}

	// Get Return field value
	$return = ($params->get('return', 0)) ? Route::_($params->get('return')) : Uri::getInstance()->toString();
	$return = (!$ajax) ? base64_encode($return) : false;

	require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
}
else
{
	echo Text::_('MOD_JFORM_ERROR_FORM_NOT_FOUND');
}