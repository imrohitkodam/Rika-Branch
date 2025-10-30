<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2024 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Extract the display data which will have the $eventOptions
extract($displayData);

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select');
?>
<div class="contentpane component">
	<form action="<?php echo Route::_('index.php?option=com_jticketing&view=enrollment&layout=attendeemove&tmpl=component', false); ?>"
	 method="post" name="adminForm1" id="adminForm1">
		<div id="enroll-user" class='row'>
			<div class="col-xs-12">
				<div class="control-label">
					<label id="jform_title-lbl" for="jform_title" class="hasTooltip required af-font-600" title="<?php echo Text::_('COM_JTICKETING_SELECT_EVENT_TO_ENROLLMENT_DESCRIPTION') ?>">
						<?php echo Text::_('COM_JTICKETING_SELECT_EVENT_TO_ENROLLMENT'); ?><span class="star">&nbsp;*</span>
					</label>
					<?php
						echo JHtmlSelect::genericlist($eventOptions, 'selected_event', 'class="btn input-medium" size="10" name="groupfilter"', "value", "text", '');
					?>
				</div>
			</div>
			<div class="col-xs-12 af-mt-30">
				<button class="btn btn-primary pull-right" type="submit" value="Submit"/><?php echo Text::_('COM_JTICKETING_MOVE_ATTENDEE_BUTTON'); ?></button>
			</div>
		</div>
		<input type="hidden" name="task" value="enrollment.moveAttendee" />
		<input type="hidden" name="attendeeId" id="attendeeId" value="" />
		<input type="hidden" name="userId" id="userId" value="" />
		<input type="hidden" name="eventId" id="eventId" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
