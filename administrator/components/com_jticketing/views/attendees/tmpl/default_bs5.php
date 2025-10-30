<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2024 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.modal');
HTMLHelper::_('jquery.token');

JticketingHelper::getLanguageConstant();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$comParams    = JT::config();
$utilities    = JT::utilities();
?>
<div id="jtwrap" class="tjBs5">
<?php
// Modal pop up for mass enrollment
echo JHtmlBootstrap::renderModal('myModalNew', $this->modal_params, $this->body);
echo JHtmlBootstrap::renderModal('import_attendees', $this->csv_params);
echo JHtmlBootstrap::renderModal('move_attendee', $this->attendeePrams, $this->attendeeBody);
echo $this->addToolbar();
?>
<style>
@media print {
  body * {
    visibility: hidden;
  }
  #printContainer, #printContainer * {
    visibility: visible;
  }
  #printContainer {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
  table {
    width: 100% !important;
    border-collapse: collapse;
  }
  th, td {
    word-break: break-word;
    border: 1px solid #000;
    padding: 5px;
  }
}
</style>

<span id="ajax_loader"></span>
<form action="
<?php echo Route::_('index.php?option=com_jticketing&view=attendees' . $this->component, false); ?>" method="post" name="adminForm" id="adminForm">
	<?php
	if (!empty($this->sidebar))
	{ ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php
	}
	else
	{?>
		<div id="j-main-container">
	<?php
	}

	echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => $this->filterHide)));

	if (empty($this->items))
	{ ?>
		<div class="alert alert-info">
			<?php echo Text::_('COM_JTICKETING_NO_ATTENDEES_FOUND'); ?>
		</div>
		<?php
		return;
	}
	if ($this->state->get('filter.events'))
	{
		$event = JT::event()->loadByIntegration((int)$this->state->get('filter.events'));
		?>
		<div id="eventNamewithStartDateDetails" class="d-none">
			<h2 id="eventTitle"><?php echo $event->getTitle(); ?></h2>
			<p id="eventStartDate"><?php echo Text::sprintf('COM_JTICKETING_EVENT_DETAILS_WITH_START_DATE', $utilities->getFormatedDate($event->getStartDate())); ?></p>
		</div>
		<?php
	}?>
		<div class="jticketing-tbl">
			<table class="table table-striped left_table" id="usersList">
				<thead>
					<tr>
						<?php
						// Do not rendar the unnessessary fields,rows for pop up layouts.
						if ($this->tmpl !== 'component')
						{ ?>
							<th width="1%" class="hidden-phone noNeedInPrint">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>
							<?php 
						}

						if (in_array('COM_JTICKETING_ORDER_ID', $this->attendeeListingFields))
						{ ?>
							<th class='left'>
								<?php echo  Text::_('COM_JTICKETING_ORDER_ID'); ?>
							</th>
							<?php 
						} ?>

						<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ENROLMENT_ID', 'attendee.id', $listDirn, $listOrder); ?>
						</th>

						<?php
						if (in_array('COM_JTICKEITNG_ATTENDEE_ENTRY_NUMBER', $this->attendeeListingFields))
						{ ?>
							<th class='left'>
								<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKEITNG_ATTENDEE_ENTRY_NUMBER', 'oitem.entry_number', $listDirn, $listOrder); ?>
							</th>
						<?php } ?>

						<th class='left'>
							<?php echo  Text::_('COM_JTICKETING_ATTENDEE_USER_NAME'); ?>
						</th>

						<?php
						if (in_array('COM_JTICKETING_BUYER_NAME', $this->attendeeListingFields))
						{ ?>
							<th class='left'>
								<?php echo  Text::_('COM_JTICKETING_BUYER_NAME'); ?>
							</th>
							<?php 
						} 

						if (in_array('COM_JTICKETING_ENROLMENT_USER_USERNAME', $this->attendeeListingFields))
						{ ?>
							<th class='left'>
								<?php echo  Text::_('COM_JTICKETING_ENROLMENT_USER_USERNAME'); ?>
							</th>
						<?php } ?>

						<?php
						if (in_array('COM_JTICKEITNG_ATTENDEE_EMAIL', $this->attendeeListingFields))
						{ ?>
							<th class='left'>
								<?php echo Text::_('COM_JTICKEITNG_ATTENDEE_EMAIL'); ?>
							</th>
						<?php } ?>

						<th class='left'>
							<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ENROLMENT_EVENT_NAME', 'events.title', $listDirn, $listOrder); ?>
						</th>

						<?php
						if (in_array('COM_JTICKEITNG_TICKET_TYPE_COLUMN', $this->attendeeListingFields))
						{ ?>
							<th class='left'>
								<?php echo Text::_('COM_JTICKEITNG_TICKET_TYPE_COLUMN'); ?>
							</th>
							<?php 
						} 

						if (in_array('TICKET_PRICE', $this->attendeeListingFields))
						{ ?>
							<th class='left'>
								<?php echo Text::_('TICKET_PRICE'); ?>
							</th>
							<?php 
						} 

						// Do not rendar the unnessessary fields,rows,menus from a pop up view.
						if ($this->tmpl !== 'component')
						{
							if (($this->isEnrollmentEnabled && $this->isEnrollmentApproval) || $this->enableAttendeeMove)
							{
							?>
							<th class='left noNeedInPrint'>
								<?php
								if ($this->isEnrollmentEnabled && $this->isEnrollmentApproval)
								{
									echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ENROLMENT_APPROVAL', 'attendee.status', $listDirn, $listOrder);
								}
								else
								{
									echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_ATTENDESS_VIEW_MOVE_ATTENDEE', 'attendee.status', $listDirn, $listOrder);
								}?>
							</th>
							<?php
							} ?>
							<th class="left noNeedInPrint">
								<?php echo  Text::_('PREVIEW_TICKET'); ?>
							</th>

							<?php
							if (in_array('COM_JTICKETING_ENROLMENT_ACTION', $this->attendeeListingFields))
							{ ?>
								<th align="left" class="noNeedInPrint">
									<?php echo  Text::_('COM_JTICKETING_ENROLMENT_ACTION'); ?>
								</th>
							<?php }
					}
						if (in_array('COM_JTICKETING_CHECKIN_TIME', $this->attendeeListingFields))
						{ ?>
							<th class='left'>
								<?php echo HTMLHelper::_('grid.sort',  'COM_JTICKETING_CHECKIN_TIME', 'chck.checkintime', $listDirn, $listOrder); ?>
							</th>
							<?php 
						}
					if ($this->tmpl !== 'component')
					{
						if (in_array('COM_JTICKETING_CHECKIN', $this->attendeeListingFields))
						{ ?>
							<th align="left" class="noNeedInPrint">
								<?php echo  Text::_('COM_JTICKETING_CHECKIN'); ?>
							</th>
						<?php } ?>

							 <th class='left noNeedInPrint'>
								<?php echo Text::_('COM_JTICKETING_ENROLMENT_NOTIFY'); ?>
							</th>
					<?php }?>
					</tr>
				</thead>
				<tfoot>
					<?php
					if (isset($this->items[0]))
					{
						$colspan = count(get_object_vars($this->items[0]));
					}
					else
					{
						$colspan = 10;
					}
					?>
					<tr>
						<td colspan="<?php echo $colspan ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
			<tbody>
				<?php
				$j = 0;

				foreach($this->items as $i => $item) :
					$ordering   = ($listOrder == 'b.ordering');
					?>
					<tr class="row<?php echo $i % 2; ?>" >
						<?php
						// Do not rendar the unnessessary fields,rows,menus from a pop up view.
						if ($this->tmpl !== 'component')
						{ ?>
							<td class="center hidden-phone noNeedInPrint">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<?php
							if (isset($this->items[0]->state)): ?>
								<td class="center">
									<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'enrollments.', $canChange, 'cb'); ?>

								</td>
							<?php
							endif;
						} 

						if (in_array('COM_JTICKETING_ORDER_ID', $this->attendeeListingFields))
						{ ?>
							<td class="text-break">
								<?php echo htmlspecialchars($item->order_id); ?>
							</td>
						<?php } ?>

						<td class="text-break">
							<?php echo htmlspecialchars($item->enrollment_id); ?>
						</td>
						<?php
						if (in_array('COM_JTICKEITNG_ATTENDEE_ENTRY_NUMBER', $this->attendeeListingFields))
						{ ?>
							<td class="text-break">
								<?php echo htmlspecialchars($item->entry_number); ?>
							</td>
						<?php } ?>
						<td>
							<?php
							if (!empty($item->fname))
							{
							    echo $this->escape(ucfirst($item->fname) . ' ' . ucfirst($item->lname));
							}
							elseif (!empty($item->firstname))
							{
							    echo $this->escape($item->firstname . ' ' . $item->lastname);
							}
							else
							{
							    echo $this->escape($item->name);
							} ?>
						</td>

						<?php
						if (in_array('COM_JTICKETING_BUYER_NAME', $this->attendeeListingFields))
						{ ?>
							<td class="text-break">
								<?php
									if (!empty($item->buyer_name))
									{
										echo htmlspecialchars($item->buyer_name);
									}
									else
									{
										echo htmlspecialchars('-');
									}
								?>
							</td>
						<?php
						}

						if (in_array('COM_JTICKETING_ENROLMENT_USER_USERNAME', $this->attendeeListingFields))
						{ ?>
							<td class="text-break">
								<?php
								if (!empty($item->username))
								{
									echo htmlspecialchars($item->username);
								}
								else
								{
									echo htmlspecialchars('-');
								}
								?>
							</td>
						<?php } 
						
						if (in_array('COM_JTICKEITNG_ATTENDEE_EMAIL', $this->attendeeListingFields))
						{ ?>
							<td class="text-break">
								<?php
								if (!empty($item->attendee_email))
								{
									echo $this->escape($item->attendee_email);
								}
								else
								{
									echo $this->escape($item->owner_email);
								} ?>
							</td>
						<?php } ?>

						<td>
							<?php
								$eventTitle = htmlspecialchars($item->title);
								echo $eventTitle;
							?>
						</td>
						<?php
						if (in_array('COM_JTICKEITNG_TICKET_TYPE_COLUMN', $this->attendeeListingFields))
						{ ?>
							<td class="text-break">
								<?php echo htmlspecialchars($item->ticket_type_title); ?>
							</td>
							<?php 
						} 

						if (in_array('TICKET_PRICE', $this->attendeeListingFields))
						{ ?>
							<td class="text-break">
								<?php echo $utilities->getFormattedPrice($item->amount);?>
							</td>
							<?php 
						} 

						$app = Factory::getApplication();
						$isAdmin = 0;

						if ($app->isClient("administrator"))
						{
							$isAdmin = 1;
						}

						// Do not rendar the unnessessary fields,rows,menus from a pop up view.
						if ($this->tmpl !== 'component')
						{
							if (($this->isEnrollmentEnabled && $this->isEnrollmentApproval) || $this->enableAttendeeMove)
							{
								if(!$item->checkin)
								{
									$onchange = "jtCommon.enrollment.updateEnrollment(" . $i . "," . $item->id . ",'update'," . $isAdmin . ")";
									$isOrderPresent = $item->order_id ? true : false;
									if ($isOrderPresent && $item->order_status == COM_JTICKETING_CONSTANT_ORDER_STATUS_INCOMPLETE)
									{
										?>
										<td>
											<?php
												echo Text::_('JT_PSTATUS_INITIATED');
											?>
										</td>
										<?php
									}
									else if ($this->isEnrollmentEnabled === '1'  && $this->isEnrollmentApproval === '1')
									{
										// Get the valid order status list options according to the current status of the enrollment.
										$validStatus = $this->attendeesModel->getValidAttendeeActions($item->status, $this->attendeeActions);
										$logedInUser  = Factory::getUser();
										$canEnrollOwn = $logedInUser->authorise('core.enrollown', 'com_jticketing');
										$enroll = 0;

										if (!$canEnrollOwn)
										{
											$vendor = JT::event($item->event_id)->getVendorDetails();
											$enroll = ($logedInUser->id == $vendor->user_id)?1:0;
										}

										if ($item->status == 'R' || $enroll == 1 )
										{
											unset($validStatus['M']);
										}
										?>
										<td>
											<?php
											echo HTMLHelper::_('select.genericlist', $validStatus, "assign_" . $i, 'onchange=' . $onchange, "value", " text", $item->status);
											?>
										</td>
									<?php
									}
									elseif ($this->enableAttendeeMove && $item->status !== COM_JTICKETING_CONSTANT_ATTENDEE_STATUS_PENDING)
									{
										$onchange = "jtCommon.enrollment.updateEnrollment(" . $i . "," . $item->id . ",'moveAttendee'," . $isAdmin . ")";
										?>
										<td>
											<input type="button" id="assign_<?php echo $i;?>" onclick="<?php echo $onchange;?>" value="<?php echo Text::_('COM_JTICKETING_ATTENDESS_VIEW_MOVE_ATTENDEE'); ?>"/>
										</td>
							<?php 	}
									elseif($this->enableAttendeeMove)
									{
									?>
										<td>
											<?php echo '-';?>
										</td>
										<?php
									}
									else
									{
										?>
										<td>
											<?php echo '-';?>
										</td>
										<?php
									}?>

								<?php
								}
								else
								{
									$onchange = "jtCommon.enrollment.updateEnrollment(" . $i . "," . $item->id . ",'moveAttendee'," . $isAdmin . ")";
									?>
									<td>
										<?php echo Text::_('COM_JTICKETING_ATTENDESS_VIEW_CHECKED_IN');?>
									</td>
									<?php
								}
							}?>
						<td class="noNeedInPrint">
							<?php
								if ($item->status == 'A')
								{
									$this->print_params['url'] = Route::_(Uri::base(true) . '/index.php?option=com_jticketing&view=attendees&tmpl=component&layout=myticket&attendee_id='. $item->id);
									echo HTMLHelper::_(
										'bootstrap.renderModal', 'previewModal' .$item->id,
										array(
										'url' => $this->print_params['url'], 'title' => Text::_('PREVIEW_DES'),
										'height' => '600px', 'width' => '600px',
										'bodyHeight' => '70', 'modalWidth'=> '80',
										'closeButton' => true, 'backdrop' => 'static',
										'keyboard' => false,
										'footer' => '<button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">' . Text::_('JCANCEL') . '</button>'
										)
									);
								?>
								<?php $buttonId = "previewModal" . $item->id; ?>
								<a 	onclick="document.getElementById('<?php echo $buttonId?>').open();"
									href="javascript:void(0);">
									<?php echo Text::_('PREVIEW_DES');?>
								</a>
								<br>
								<?php }?>

								<!-- For Extra Attendee Fields -->
								<?php

								if ($this->collect_attendee_info_checkout)
								{
									$this->attendee_params['url'] = Route::_(Uri::base(true) . '/index.php?option=com_jticketing&view=attendees&tmpl=component&layout=attendee_details&eventid=' . $item->event_id . '&attendee_id=' . $item->id);
									echo HTMLHelper::_(
										'bootstrap.renderModal', 'previewAttendeeModal' .$item->id,
										array(
											'url' => $this->attendee_params['url'], 'title' => Text::_('COM_JTICKETING_VIEW_ATTENDEE'),
											'height' => '600px', 'width' => '600px',
											'bodyHeight' => '70', 'modalWidth'=> '80',
											'closeButton' => true, 'backdrop' => 'static',
											'keyboard' => false,
											'footer' => '<button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">' . Text::_('JCANCEL') . '</button>'
										)
									);
								?>
								<?php $buttonId = "previewAttendeeModal" . $item->id; ?>
								<a 	onclick="document.getElementById('<?php echo $buttonId;?>').open();"
									href="javascript:void(0);">
									<?php echo Text::_('COM_JTICKETING_VIEW_ATTENDEE');?>
								</a>
								<?php }?>
							</td>
							
							<?php
							if (in_array('COM_JTICKETING_ENROLMENT_ACTION', $this->attendeeListingFields))
							{ ?>
								<td class="noNeedInPrint">
									<?php
										$isOrderPresent = $item->order_id ? true : false;
										if (!$isOrderPresent)
										{
											?>
											<a href="javascript:void(0);" class="hasTooltip" data-original-title="<?php echo Text::_('JTOOLBAR_TRASH');?>" onclick="jtCommon.enrollment.deleteEnrollment('<?php echo $item->id; ?>')">

												<span class="icon-trash af-icon-red" area-hidden="true" ></span>
											</a>
											<?php
										}
										else 
										{
											echo '-';
										}
									?>
								</td>
							<?php }
				  		} 
						if (in_array('COM_JTICKETING_CHECKIN_TIME', $this->attendeeListingFields))
						{ ?>
							<td>
								<?php
								if (!empty($item->checkintime) && !empty($item->checkin))
								{
									?>
									<?php echo ($item->checkintime); ?>
								<?php
								}
								else
								{
									echo '-';
								}
								?>
							</td>
							<?php 
						}
						// Do not rendar the unnessessary fields,rows,menus from a pop up view.
						if ($this->tmpl !== 'component')
						{
							if (in_array('COM_JTICKETING_CHECKIN', $this->attendeeListingFields))
							{ ?>
								<td align="center" class="noNeedInPrint">
									<?php if ($item->status == 'A'){
									?>
									<a href="javascript:void(0);" class="hasTooltip" data-original-title="<?php echo ($item->checkin) ? Text::_('COM_JTICKETING_CHECKIN_FAIL') : Text::_('COM_JTICKETING_CHECKIN_SUCCESS');?>" onclick="Joomla.listItemTask('cb<?php echo $i;?>','<?php echo ($item->checkin) ? 'attendees.undochekin' : 'attendees.checkin';?>')">

										<img src="<?php echo Uri::root();?>administrator/components/com_jticketing/assets/images/<?php echo ($item->checkin) ? 'publish.png' : 'unpublish.png';?>" width="16" height="16" border="0" />
									</a>
									<?php
									}
									else
									{
										echo '-';
									}
									?>
								</td>
								<?php
							} ?>

							<td class="noNeedInPrint">
								<label>
									<input id="notify_user_<?php echo $item->id ?>" type="checkbox" name='notify_user_<?php echo $item->id ?>' checked>
								</label>
							</td>
					<?php } ?>

						<input type="hidden" id="eid_<?php echo $i ?>" name="eid" value="<?php echo $item->event_id; ?>" />
						<input type="hidden" id="owner_<?php echo $i ?>" name="ownerId" value="<?php echo $item->owner_id; ?>" />
					</tr>
					<?php $j++;
				endforeach;
				?>
			</tbody>
		</table>
	</div><!--j-main-container ENDS-->
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<input type="hidden" name="controller" id="controller" value="attendees" />

	<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
</div>

<script>
	jQuery(".close").click(function() {
		parent.location.reload();
	});

    jQuery('.print-attendee-list').on('click', function () {
        // Clone the original table
        const $clonedTable = jQuery('#usersList').clone();

        // Find indexes of th.noNeedInPrint
        const indexesToRemove = [];
        $clonedTable.find('thead th').each(function (i) {
            if (jQuery(this).hasClass('noNeedInPrint')) {
                indexesToRemove.push(i);
            }
        });

        // Remove th and td at those indexes
        $clonedTable.find('tr').each(function () {
            jQuery(this).find('th, td').each(function (i) {
                if (indexesToRemove.includes(i)) {
                    jQuery(this).remove();
                }
            });
        });

        // Get event details
        const eventTitle = jQuery('#eventTitle').text();
        const eventStartDate = jQuery('#eventStartDate').text();

        // Create print container
        const printWindow = window.open('', '', 'height=800,width=1000');
        printWindow.document.write('<html><head><title>Attendee List</title>');
        printWindow.document.write('<style>');
        printWindow.document.write(`
        @media print {
          body {
            font-family: Arial, sans-serif;
            margin: 20px;
          }
          #printContainer {
            margin: 10px;
          }
          h2, p {
            margin: 10px 0;
            padding: 0;
          }
          table {
            width: 100% !important;
            border-collapse: collapse;
            margin-top: 20px;
          }
          thead th {
            background-color: #f0f0f0;
          }
          tbody td, thead th {
            word-break: break-word;
            border: 1px solid #000;
            padding: 8px;
          }
          tbody tr {
            page-break-inside: avoid;
          }
        }
      `);

        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div id="printContainer">');
        printWindow.document.write(`<h2>${eventTitle}</h2>`);
        printWindow.document.write(`<p>${eventStartDate}</p>`);
        printWindow.document.write($clonedTable.prop('outerHTML'));
		printWindow.document.close();
		printWindow.focus();
		printWindow.print();
    });

</script>
