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

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

jimport('joomla.filesystem.file');

class modJFormHelper
{
	/**
	 * Module object
	 *
	 * @var    object
	 *
	 * @since 1.0.0
	 */
	protected static $_module = null;

	/**
	 * Method to instantiate the JForm Module Helper
	 *
	 * @param object $module Module object
	 *
	 * @since 1.0.0
	 */
	public function __construct($module = null)
	{
		if ((!empty($module)))
		{
			$module->params = new Registry($module->params);
		}
		self::$_module = (!empty($module)) ? $module : null;
	}

	/**
	 * Form submit method
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function getAjax()
	{
		if (!Session::checkToken())
		{
			return self::setError(Text::_('JINVALID_TOKEN'));
		}

		// Get Module
		$module = self::getModule();
		if (!$module)
		{
			return self::setError(Text::_('MOD_JFORM_ERROR_MODULE_NOT_FOUND'));
		}
		$params = $module->params;

		// Get Form
		$form = self::getForm();
		if (!$form)
		{
			return self::setError(Text::_('MOD_JFORM_ERROR_MODULE_NOT_FOUND'));
		}

		echo '<pre>', print_r($form, true), '</pre>';
		echo '<pre>', print_r($module, true), '</pre>';
		echo '<pre>', print_r($params, true), '</pre>';

		return true;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @return bool|Form  Form object on success, false on failure
	 *
	 * @since   1.0
	 */
	public static function getForm()
	{
		// Get Module
		$module = self::getModule();
		if (!$module)
		{
			return false;
		}
		$params = $module->params;

		// Get form file
		$file = ($params->get('file')) ? __DIR__ . '/forms/' . $params->get('file') . '.xml' : false;
		if (!$file || !JFile::exists($file))
		{
			return false;
		}

		// Get Form
		$formName = $module->module . '_' . $module->id;
		$form     = new Form($formName, array('control' => $formName));
		$form->loadFile($file);

		// Add captcha field
		$captcha = $params->get('captcha');
		if ($captcha == 1 || ($captcha == 2 && Factory::getUser()->guest))
		{
			$form->setField(new SimpleXMLElement('<field name="captcha" type="captcha" label="MOD_JFORM_CAPTCHA_LABEL"	
				description="MOD_JFORM_CAPTCHA_DESCRIPTION"	validate="captcha"/>'));
		}

		return $form;
	}

	/**
	 *  Method to get Module object
	 *
	 * @param int $pk Module id
	 *
	 * @return bool|object  Module object on success, false on failure
	 *
	 * @since 1.0.0
	 */
	protected static function getModule($pk = null)
	{
		$pk = (empty($pk)) ? Factory::getApplication()->input->get('module_id', 0) : $pk;

		if (empty(self::$_module))
		{
			$module = false;
			try
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select('*')
					->from('#__modules')
					->where('id =' . $pk)
					->where('module = ' . $db->quote('mod_jform'));
				$db->setQuery($query);
				$object = $db->loadObject();

				if (!empty($object))
				{
					$module         = $object;
					$module->params = new Registry($module->params);
				}

				self::$_module = $module;
			}
			catch (Exception $e)
			{
				self::$_module = false;
			}
		}

		return self::$_module;
	}

	/**
	 * Method to set error response
	 *
	 * @param string $message Message text
	 *
	 * @return false
	 *
	 * @since 1.0.0
	 */
	protected static function setError($message)
	{
		$app    = Factory::getApplication();
		$return = $app->input->get('return', null, 'base64');
		if (!$app->input->get('ajax') && !is_null($return) && Uri::isInternal(base64_decode($return)))
		{
			$app->enqueueMessage($message, 'error');
			$app->redirect(base64_decode($return));

			return false;
		}

		throw new Exception($message, 404);

		return false;
	}
}