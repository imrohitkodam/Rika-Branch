<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2024 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_COMPONENT . '/controller.php';

	/**
	 * Allticketsales controller class.
	 *
	 * @since  3.2
	 */
class JticketingControllerallticketsales extends BaseController
{
	/**
	 * Function to save
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function save()
	{
		$input = Factory::getApplication()->input;
		$task = $input->get('task');

		switch ($task)
		{
			case 'cancel':
				$this->setRedirect('index.php?option=com_jticketing');
		}
	}

	/**
	 * Function to cancel
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function cancel()
	{
		$input = Factory::getApplication()->input;
		$task = $input->get('task');

		switch ($task)
		{
			case 'cancel':
			$this->setRedirect('index.php?option=com_jticketing');
		}
	}
}
