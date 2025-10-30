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
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View to edit
 *
 * @since  1.6
 */
class JticketingViewCoupon extends BaseHtmlView
{
	/**
	 * The model state
	 *
	 * @var  CMSObject
	 */
	protected $state;

	/**
	 * The coupon object
	 *
	 * @var  \stdClass
	 */
	protected $item;

	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  An optional associative array.
	 *
	 * @return  array|boolean
	 *
	 * @since 1.6
	 */
	public function display($tpl = null)
	{
		$params = ComponentHelper::getParams('com_jticketing');

		// Native Event Manager.
		if ($params->get('integration') < 1)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COMJTICKETING_INTEGRATION_NOTICE'), 'Warning');

			return false;
		}

		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();

		ToolBarHelper::preferences('com_jticketing');

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  array
	 *
	 * @since 1.6
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$viewTitle = ($this->item->id == 0) ? Text::_('COM_JTICKETING_ADD_COUPON') : Text::_('COM_JTICKETING_EDIT_COUPON');

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == Factory::getUser()->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		JLoader::register('JticketingHelper', JPATH_COMPONENT . '/components/com_jticketing/helpers/jticketing.php');
		$canDo = ContentHelper::getActions('com_jticketing');

		ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . $viewTitle, 'pencil-2');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			ToolBarHelper::apply('coupon.apply', 'JTOOLBAR_APPLY');
			ToolBarHelper::save('coupon.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			ToolBarHelper::custom('coupon.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		if (empty($this->item->id))
		{
			ToolBarHelper::cancel('coupon.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolBarHelper::cancel('coupon.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
