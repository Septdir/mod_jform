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

use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

jimport('joomla.filesystem.file');

class modJFormHelper
{

	/**
	 * Form submit method
	 *
	 * @return bool|string
	 *
	 * @since 1.0.0
	 */
	public static function getAjax()
	{
		$app      = Factory::getApplication();
		$moduleID = $app->input->get('module_id');

		$module = self::getModule($moduleID);
		if (!$module)
		{
			throw new Exception(Text::_('COM_AJAX_MODULE_NOT_ACCESSIBLE'), 404);

			return false;
		}

		$form = self::getForm($module);
		if (!$form)
		{
			throw new Exception(Text::_('MOD_JFORM_ERRORS_NOFORM'), 404);

			return false;
		}

		$data = $app->input->post->get($form->getName(), array(), 'array');

		$validData = self::validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			return false;
		}


		return true;

	}


	/**
	 * Method to validate the form data.
	 *
	 * @param   \JForm $form The form to validate against.
	 * @param   array  $data The data to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @see     \JFormRule
	 * @see     \JFilterInput
	 * @since   1.6
	 */
	public static function validate($form, $data)
	{
		// Include the plugins for the delete events.
		PluginHelper::importPlugin('content');

		$dispatcher = \JEventDispatcher::getInstance();
		$dispatcher->trigger('onUserBeforeDataValidation', array($form, &$data));

		// Filter and validate the form data.
		$data   = $form->filter($data);
		$return = $form->validate($data);

		// Check for an error.
		if ($return instanceof \Exception)
		{
			throw new Exception($return->getMessage(), 404);

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $error)
			{
				throw new Exception($error->getMessage(), 404);

			}

			return false;
		}

		return $data;
	}


	/**
	 * Method for getting the form from the model.
	 *
	 * @param   object $module Module object
	 *
	 * @return bool|JForm|Form A \JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public static function getForm($module)
	{

		$params = new Registry($module->params);
		// Get Form params
		$formParams = ($params->get('form', 0)) ? new Registry($params->get('form')) : false;
		if (!$formParams)
		{
			return false;
		}

		// Get form file
		$file = ($formParams->get('file', 0)) ? __DIR__ . '/forms/' . $formParams->get('file') . '.xml' : false;
		if (empty($file) || !JFile::exists($file))
		{
			return false;
		}

		$id = ($formParams->get('id', 0)) ? $formParams->get('id') : $module->module . '_' . $module->id;

		$form = new Form($id, array('control' => $id));
		$form->loadFile($file);

		return $form;
	}


	/**
	 * Get Module Object
	 *
	 * @param int $pk module id
	 *
	 * @return bool|object Module object or false
	 *
	 * @since 1.0.0
	 */
	protected static function getModule($pk = null)
	{
		$pk = (empty($pk)) ? Factory::getApplication()->input->get('module_id', 0) : $pk;
		if (empty($pk))
		{
			return false;
		}

		// Get Params
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__modules')
			->where('id =' . $pk);
		$db->setQuery($query);
		$module = $db->loadObject();

		return (!empty($module)) ? $module : false;
	}
}