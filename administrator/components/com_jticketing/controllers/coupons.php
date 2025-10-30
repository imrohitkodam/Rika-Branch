<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2024 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
JLoader::import('joomla.application.component.model');
use Joomla\CMS\Factory;

JLoader::import('jticketlist', JPATH_ROOT . '/administrator/components/com_jticketing/controllers');

/**
 * Coupons list controller class.
 *
 * @since  1.6
 */
class JticketingControllerCoupons extends JTicketingControllerJticketlist
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   boolean  $name    If true, the view output will be cached
	 * @param   boolean  $prefix  If true, the view output will be cached
	 * @param   array    $config  An array of safe url parameters and their variable types, for valid values see {@link
	 *
	 * @return  object|boolean The model
	 *
	 * @since  1.6
	 */
	public function getModel($name = 'coupon', $prefix = 'JticketingModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to fetch vendor specific events
	 *
	 * @return  string
	 *
	 * @since   2.4.0
	 */
	public function getVendorSpecificEvents()
	{
		$input    = Factory::getApplication()->input;
		$vendorId = $input->get('vendorId');
		$eventsList = array();

		if ($vendorId)
		{
			$eventsModel = JT::model('events');
			$eventsList = $eventsModel->getVendorSpecificEvents($vendorId);
		}
		else
		{
			$eventsList = array("error" => 1);
		}

		echo json_encode($eventsList);
		jexit();
	}
}
