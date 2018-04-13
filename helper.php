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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;

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
			return self::setError(Text::_('MOD_JFORM_ERROR_FORM_NOT_FOUND'));
		}

		$app     = Factory::getApplication();
		$control = $form->getFormControl();
		$data    = $app->input->post->get($control, array(), 'array');

		// Filter and validate the form data.
		$validData = self::validate($form, $data);

		// Bind data to the form.
		$form->bind($validData);

		if ($params->get('send_email'))
		{
			$siteConfig = Factory::getConfig();

			// Prepare admin mail
			$subject   = Text::sprintf('MOD_JFORM_ADMIN_MAIL_SUBJECT', $module->title);
			$sender    = array($siteConfig->get('mailfrom'), $siteConfig->get('sitename'));
			$recipient = $params->get('admin_email', $siteConfig->get('mailfrom'));
			$body      = self::getAdminMailBody($form);

			// Send email
			$mail = Factory::getMailer();
			$mail->setSubject($subject);
			$mail->setSender($sender);
			$mail->addRecipient($recipient);
			$mail->setBody($body);
			$mail->isHtml(true);
			$mail->Encoding = 'base64';

			return ($mail->send()) ? self::setResponse(Text::_('MOD_JFORM_SUCCESS_SEND_ADMIN_MAIL')) :
				self::setError(Text::_('MOD_JFORM_ERROR_SEND_ADMIN_MAIL'));
		}

		return self::setResponse(Text::_('MOD_JFORM_SUCCESS'));
	}

	/**
	 * Method for getting admin email body
	 *
	 * @param $form Form  Form object on success, false on failure
	 *
	 * @return string Message body
	 *
	 * @since   1.0
	 */
	public static function getAdminMailBody($form)
	{
		$body      = '<table cellspacing="1" cellpadding="3"><tbody>';
		$fieldsets = $form->getFieldsets();
		foreach ($fieldsets as $key => $fieldset)
		{
			$fields = $form->getFieldset($key);
			foreach ($fields as $field)
			{
				$value = $field->value;
				$type  = $field->getAttribute('type');
				if ($type == 'list' || $type == 'checkboxes')
				{
					$options = $field->__get('options');
					if (!is_array($value))
					{
						$value = self::getOptionValue($options, $value);
					}
					else
					{
						$values = array();
						foreach ($value as $item)
						{
							$values[] = self::getOptionValue($options, $item);
						}
						$value = implode(', ', $values);
					}
				}
				if (is_array($value))
				{
					$value = ArrayHelper::toString($value);
				}

				$body .= '<tr>';
				$body .= '<td align="right"><strong>' . $field->getAttribute('label') . '</strong></td>';
				$body .= '<td>' . $value . '</td>';
				$body .= '</tr>';
			}
		}
		$body .= '</tbody></table>';

		return $body;
	}

	/**
	 * Method to get value text form field options
	 *
	 * @param array $options Options array
	 * @param array $value   Field value
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	protected static function getOptionValue($options = array(), $value = null)
	{
		foreach ($options as $option)
		{
			if ($option->value == $value)
			{
				return $option->text;
			}
		}

		return '';
	}

	/**
	 * Method for getting the form.
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
	 * Method to validate the form data.
	 *
	 * @param   Form  $form The form to validate against.
	 * @param   array $data The data to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @see     \Joomla\CMS\Form\FormRule
	 * @see     \Joomla\Filter\InputFilter
	 * @since   1.0.0
	 */
	protected static function validate($form, $data)
	{
		// Include the plugins for the content events.
		PluginHelper::importPlugin('content');

		$dispatcher = \JEventDispatcher::getInstance();
		$dispatcher->trigger('onUserBeforeDataValidation', array($form, &$data));

		// Filter and validate the form data.
		$data   = $form->filter($data);
		$return = $form->validate($data);

		// Check for an error.
		if ($return instanceof \Exception)
		{
			self::setError($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			$errors = array();
			foreach ($form->getErrors() as $message)
			{
				$errors[] = $message->getMessage();
			}
			self::setError($errors);

			return false;
		}

		return $data;
	}

	/**
	 *  Method to get module object
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
	 * Method to set response
	 *
	 * @param string|array $messages Messages text
	 *
	 * @return true
	 *
	 * @since 1.0.0
	 */
	protected static function setResponse($messages)
	{
		$app      = Factory::getApplication();
		$return   = $app->input->get('return', null, 'base64');
		$messages = (is_array($messages)) ? $messages : (array) $messages;

		if (!$app->input->get('ajax') && !is_null($return) && Uri::isInternal(base64_decode($return)))
		{
			foreach ($messages as $message)
			{
				$app->enqueueMessage($message, 'success');
			}
			$app->redirect(base64_decode($return));

			return true;
		}

		foreach ($messages as $message)
		{
			$app->enqueueMessage($message, 'success');
		}
		$app->input->set('ignoreMessages', false);

		return true;
	}

	/**
	 * Method to set error response
	 *
	 * @param string|array $messages Messages text
	 *
	 * @return false
	 *
	 * @since 1.0.0
	 */
	protected static function setError($messages)
	{
		$app      = Factory::getApplication();
		$return   = $app->input->get('return', null, 'base64');
		$messages = (is_array($messages)) ? $messages : (array) $messages;

		if (!$app->input->get('ajax') && !is_null($return) && Uri::isInternal(base64_decode($return)))
		{
			foreach ($messages as $message)
			{
				$app->enqueueMessage($message, 'error');
			}
			$app->redirect(base64_decode($return));

			return false;
		}

		throw new Exception(implode(PHP_EOL, $messages), 404);

		return false;
	}
}