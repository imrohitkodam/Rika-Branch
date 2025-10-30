<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Helper class for module
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class ModJTicketingHelper
{
	/**
	 * Get data
	 *
	 * @param   Array  $params  com_jticketing params
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getData($params)
	{
		// FrontendHelper is loaded here
		JLoader::import('frontendhelper', JPATH_SITE . '/components/com_jticketing/helpers');
		$jticketingFrontendHelper = new Jticketingfrontendhelper;
		$orderByDir      = $params->get('order_dir');
		$noOfEventShow = $params->get('no_of_event_show');
		$featuredEvent   = $params->get('featured_event');
		$showTime        = $params->get('show_time');
		$ticketType      = $params->get('ticket_type');
		$image            = $params->get('image');
		$defaultCatid     = $params->get('defaultCatid');
		$orderBy     = $params->get('event_order_by');
		$date             = date("Y-m-d H:i:s");

		$input = Factory::getApplication()->input;
		$tagId = $input->get('tagid', '', array());

		if (empty($tagId))
		{
			$tagId = $params->get('tags', array());
		}

		$where = array();

		$db      = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select(array('e.*'));
		$query->select(
		$db->qn(
			array('c.path','v.name', 'v.online_provider', 'v.address', 'v.country', 'v.state_id','v.city', 'v.zipcode')
		)
		);
		$query->select($db->quoteName('v.params', 'venue_params'));
		$query->from($db->qn('#__jticketing_events', 'e'));
		$query->join('LEFT', $db->qn('#__jticketing_venues', 'v') . ' ON (' . $db->qn('v.id') . ' = ' . $db->qn('e.venue') . ')');
		$query->join('LEFT', $db->qn('#__categories', 'c') . ' ON (' . $db->qn('e.catid') . ' = ' . $db->qn('c.id') . ')');
		$query->where($db->qn('e.state') . '=' . $db->quote(1) . ' AND ' . $db->qn('c.extension') . '=' . $db->quote('com_jticketing'));

		if (!empty($defaultCatid))
		{
			$query->where($db->qn('e.catid') . ' = ' . $db->quote($defaultCatid));
		}

		if ($featuredEvent == 1)
		{
			$query->where($db->qn('e.featured') . ' = ' . $db->quote(1));
		}

		if ($showTime == "upcoming")
		{
			$query->where($db->qn('e.startdate') . ' >= ' . $db->quote($date));
		}

		if ($showTime == "past")
		{
			$query->where('e.enddate <= UTC_TIMESTAMP()');
		}

		if ($showTime == "ongoing")
		{
			$query->where("e.enddate >= UTC_TIMESTAMP()");
		}

		if ($showTime == "today")
		{
			$today = date("Y-m-d");
			$query->where(('DATE(e.startdate)') . ' = ' . $db->quote($today));
		}

		if (is_array($tagId) && count($tagId) === 1)
		{
			$tagId = current($tagId);
		}

		if (is_array($tagId))
		{
			$tagId = implode(',', ArrayHelper::toInteger($tagId));

			if ($tagId)
			{
				$subQuery = $db->getQuery(true)
				->select('DISTINCT content_item_id')
				->from($db->quoteName('#__contentitem_tag_map'))
				->where('tag_id IN (' . $tagId . ')')
				->where('type_alias = ' . $db->quote('com_jticketing.event'));

				$query->innerJoin('(' . (string) $subQuery . ') AS tagmap ON tagmap.content_item_id = e.id');
			}
		}
		elseif ($tagId)
		{
			$query->innerJoin(
					$db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON tagmap.tag_id = ' . (int) $tagId
					. ' AND tagmap.content_item_id = e.id'
					. ' AND tagmap.type_alias = ' . $db->quote('com_jticketing.event')
					);
		}

		$query->group($db->qn('e.id'));
		$query->order($db->qn('e.' . $orderBy) . $orderByDir);
		$query->setLimit($noOfEventShow);

		$db->setQuery($query);
		$event = $db->loadObjectList();

		if ($event)
		{
			for ($i = 0; $i < count($event); $i++)
			{
				$eventData['event'] = $event[$i];
				$eventId        = $event[$i]->id;

				$query = $db->getQuery(true);
				$query->select('i.eventid,i.id');
				$query->from($db->qn('#__jticketing_integration_xref', 'i'));
				$query->where($db->qn('i.source') . ' = ' . $db->quote('com_jticketing') . ' AND' . $db->qn('i.eventid') . ' = ' . $db->quote($eventId));
				$db->setQuery($query);
				$integrationDetails       = $db->loadObject();

				if ($integrationDetails)
				{
					BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
					$jtickeitngModelEventFrom = BaseDatabaseModel::getInstance('EventForm', 'JticketingModel');
					$eventImageData = $jtickeitngModelEventFrom->getItem($integrationDetails->eventid);
				}

				if (isset($eventImageData->image->media_m))
				{
					$eventData['image'] = $eventImageData->image->media_m;
				}
				else
				{
					$eventData['image'] = '';
				}

				$eventData['ticket_types'] = $jticketingFrontendHelper->getTicketTypes($integrationDetails->id);

				if (count($eventData['ticket_types']) == 1)
				{
					foreach ($eventData['ticket_types'] as $ticketInfo)
					{
						$eventData['event_max_ticket'] = $ticketInfo->price;
						$eventData['event_min_ticket'] = $ticketInfo->price;
					}
				}
				else
				{
					$maxTicketPrice = -9999999;
					$minTicketPrice = 9999999;

					foreach ($eventData['ticket_types'] as $ticketInfo)
					{
						if ($ticketInfo->price > $maxTicketPrice)
						{
							$maxTicketPrice = $ticketInfo->price;
						}

						if ($ticketInfo->price < $minTicketPrice)
						{
							$minTicketPrice = $ticketInfo->price;
						}
					}

					$eventData['event_max_ticket'] = $maxTicketPrice;
					$eventData['event_min_ticket'] = $minTicketPrice;
				}

				if (empty($eventData['event']->location) && $eventData['event']->venue != '0')
				{
					$venueDetails = JT::model('venueform')->getItem($eventData['event']->venue);

					if (!empty($venueDetails->online) && $venueDetails->online_provider == 'plug_tjevents_adobeconnect')
					{
						$eventData['location'] = 'Adobe-' . $venueDetails->name;
					}
					else
					{
						$address = $eventData['event']->address;
						$eventData['location'] = $venueDetails->name . ' - ' . $address;
					}
				}

				$result[]              = $eventData;
			}

			return $result;
		}
	}

	/**
	 * function to sort
	 *
	 * @param   integer  $array   array
	 * @param   integer  $column  column
	 * @param   integer  $order   order
	 * @param   integer  $count   count
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function multi_d_sort($array, $column, $order, $count)
	{
		foreach ($array as $key => $row)
		{
			$orderby[$key] = $row->$column;
		}

		if ($order == 'ASC')
		{
			array_multisort($orderby, SORT_ASC, $array);
		}
		else
		{
			if (!empty($array))
			{
				array_multisort($orderby, SORT_DESC, $array);
			}
		}

		return $array;
	}
}
