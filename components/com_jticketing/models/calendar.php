<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2024 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Model for calendar
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelCalendar extends ListModel
{
	/**
	 * Get all events for calendar
	 *
	 * @return  array  event list
	 *
	 * @since   1.0
	 */
	public function getEvents()
	{
		$this->populateState();
		$params = array();
		$params['category_id'] = $this->state->get("filter.filter_evntCategory");
		$Jticketingmainhelper = new Jticketingmainhelper;
		$data =	$Jticketingmainhelper->getEvents($params);

		foreach ($data as $k => $v)
		{
			$config = Factory::getConfig();
			date_default_timezone_set($config->get('offset'));

			if ($v['startdate'])
			{
				$data[$k]['start']  = strtotime($v['startdate']) . '000';
			}

			$data[$k]['end']  = strtotime($v['startdate']) . '000';

			$data[$k]['event_time'] = date('G:i', strtotime($data[$k]['startdate'])) . '-' . date('G:i', strtotime($data[$k]['enddate']));
			$data[$k]['event_start_time'] = date('G:i', strtotime($data[$k]['startdate']));

			if (date('a', strtotime($data[$k]['startdate'])) === 'am')
			{
				$data[$k]['event_title_time'] = str_replace("am", "a", date('a', strtotime($data[$k]['startdate'])));
				$data[$k]['event_title_time'] = date('g', strtotime($data[$k]['event_time'])) . $data[$k]['event_title_time'];
			}
			else
			{
				$data[$k]['event_title_time'] = str_replace("pm", "p", date('a', strtotime($data[$k]['startdate'])));
				$data[$k]['event_title_time'] = date('g', strtotime($data[$k]['startdate'])) . $data[$k]['event_title_time'];
			}

			$data[$k]['background_color'] = "#87CEEB";
		}

		return $data;
	}

	/**
	 * Method to get all events
	 *
	 * @param   object  $ordering   user id
	 * @param   object  $direction  user id
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication('site');

		// Filtering ServiceType
		$filter_evntCategory = $app->getUserStateFromRequest($this->context . '.filter.filter_evntCategory', 'filter_evntCategory', '', 'string');
		$this->setState('filter.filter_evntCategory', $filter_evntCategory);
	}
}
