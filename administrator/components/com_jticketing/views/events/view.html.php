<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jticketing
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

// Import Csv export button
jimport('techjoomla.tjtoolbar.button.csvexport');

/**
 * View class for a list of Jticketing.
 *
 * @since  1.6
 */
class JticketingViewEvents extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	public $utilities;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$input            = Factory::getApplication()->input;
		$layout        	= $input->get('layout');
		$params = ComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');

		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$model        = $this->getModel();
		$this->issite = 0;

		JLoader::import('time', JPATH_SITE . '/components/com_jticketing/helpers');

		$this->utilities = JT::utilities();

		$this->singleEventItemid = $this->utilities->getItemId('index.php?option=com_jticketing&view=event');

		// Get filter form.
		$this->filterForm = $this->get('FilterForm');

		// Get active filters.
		$this->activeFilters = $this->get('ActiveFilters');

		// Native Event Manager.
		if ($integration < 1)
		{
			$this->sidebar = JHtmlSidebar::render();
			ToolBarHelper::preferences('com_jticketing');
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		if ($layout == 'tickettypes')
		{
			$this->allTicketTypes      = $model->GetTicketTypes('', true);
		}

		$this->pagination = $this->get('Pagination');

		$this->params = ComponentHelper::getParams('com_jticketing');

		// Get integration set.
		$this->integration = $this->params->get('integration', '', 'INT');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JticketingHelper::addSubmenu('events');

		$this->addToolbar();

		if (JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/jticketing.php';

		$state = $this->get('State');
		$canDo = JticketingHelper::getActions($state->get('filter.category_id'));
		$bar   = ToolBar::getInstance('toolbar');

		ToolBarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('COM_JTICKETING_TITLE_EVENTS'), 'list');

		// Check if the form exists before showing the add/edit buttons.
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/event';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				ToolBarHelper::addNew('event.add', 'JTOOLBAR_NEW');
			}

			if (JVERSION >= '4.0.0')
			{
				$dropdown = $bar->dropdownButton('status-group')
					->text('JTOOLBAR_CHANGE_STATUS')
					->toggleSplit(false)
					->icon('icon-ellipsis-h')
					->buttonClass('btn btn-action')
					->listCheck(true);

				$childBar = $dropdown->getChildToolbar();
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				ToolBarHelper::editList('event.edit', 'JTOOLBAR_EDIT');
				ToolbarHelper::custom('events.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				ToolBarHelper::divider();

				if (JVERSION < '4.0.0')
				{
					ToolBarHelper::custom('events.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
					ToolBarHelper::custom('events.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
				}
				else
				{
					$childBar->publish('events.publish')->listCheck(true);
					$childBar->unpublish('events.unpublish')->listCheck(true);
				}
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				ToolBarHelper::deleteList('', 'events.delete', 'JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->state))
			{
				ToolBarHelper::divider();

				if (JVERSION < '4.0.0')
				{
					ToolBarHelper::archiveList('events.archive', 'JTOOLBAR_ARCHIVE');
				}
				else
				{
					$childBar->archive('events.archive')->listCheck(true);
				}
			}

			if (isset($this->items[0]->checked_out))
			{
				if (JVERSION < '4.0.0')
				{
					ToolBarHelper::custom('events.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
				}
				else
				{
					$childBar->checkin('events.checkin')->listCheck(true);
				}
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				ToolBarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'events.delete', 'JTOOLBAR_EMPTY_TRASH');
				ToolBarHelper::divider();
			}
			elseif ($canDo->get('core.delete'))
			{
				if (JVERSION < '4.0.0')
				{
					ToolBarHelper::trash('events.trash', 'JTOOLBAR_TRASH');
				}
				else
				{
					$childBar->trash('events.trash')->listCheck(true);
				}

				ToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.create'))
		{
			if (JVERSION < '4.0.0')
			{
				$buttonImport = '<a data-target="#import_eventswrap" data-toggle="modal" class="btn ImportButton">
				<span class="icon-upload icon-white"></span>' . Text::_('COMJTICKETING_EVENT_IMPORT_CSV') . '</a>';
				$bar->appendButton('Custom', $buttonImport);
			}
			else
			{
				$buttonImport = '&nbsp;&nbsp;<a data-bs-target="#import_eventswrap" data-bs-toggle="modal" class="btn ImportButton">
				<span class="icon-upload icon-white"></span>' . Text::_('COMJTICKETING_EVENT_IMPORT_CSV') . '</a>';
				$bar->appendButton('Custom', $buttonImport);
			}
		}

		if (isset($this->items[0]->state))
		{
			$message = array();
			$message['success'] = Text::_("COM_JTICKETING_EXPORT_FILE_SUCCESS");
			$message['error'] = Text::_("COM_JTICKETING_EXPORT_FILE_ERROR");
			$message['inprogress'] = Text::_("COM_JTICKETING_EXPORT_FILE_NOTICE");

			if (!empty($this->items))
			{
				$bar->appendButton('CsvExport',  $message);
			}
		}

		if ($canDo->get('core.admin'))
		{
			ToolBarHelper::preferences('com_jticketing');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
			'a.state' => Text::_('COM_JTICKETING_EVENTS_PUBLISHED'),
			'a.title' => Text::_('COM_JTICKETING_EVENTS_TITLE'),
			'a.catid' => Text::_('COM_JTICKETING_EVENTS_CATEGORY_ID'),
			'a.created_by' => Text::_('COM_JTICKETING_EVENTS_CREATOR'),
			'a.startdate' => Text::_('COM_JTICKETING_EVENTS_STARTDATE'),
			'a.enddate' => Text::_('COM_JTICKETING_EVENTS_ENDDATE'),
			'a.location' => Text::_('COM_JTICKETING_EVENTS_LOCATION'),
			'a.featured' => Text::_('COM_JTICKETING_EVENTS_FEATURED'),
			'a.id' => Text::_('JGRID_HEADING_ID')
		);
	}
}
