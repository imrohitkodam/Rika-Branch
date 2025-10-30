<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticekting
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2022 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('jquery.token');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<div id="jtwrap" class="tjBs3">
	<form action="<?php echo JRoute::_('index.php?option=com_jticketing&view=waitinglist'); ?>"
	method="post" name="adminForm" id="adminForm" class="jtFilters">

		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));?>
		<?php echo $this->addTJtoolbar();?>

		<?php
		if (empty($this->items))
		{
			?>
			<div class="col-xs-12 pull-right alert alert-info jtleft">
				<?php echo JText::_('COM_JTICKETING_NO_WAITING_LIST_FOUND'); ?>
			</div>
			<?php
		}
		else
		{
			?>
			<div class="jticketing-tbl" id="no-more-tables">
				<table class="table table-striped left_table" id="usersList">
					<thead>
						<tr>
							<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>

							<th class='left'>
							<?php echo  JText::_('COM_JTICKETING_WAITING_LIST_USER_NAME'); ?>
							</th>

							<th class='left'>
							<?php echo  JText::_('COM_JTICKETING_WAITING_LIST_NAME'); ?>
							</th>

							<th class='left'>
								<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_WAITING_LIST_EVENT_NAME', 'events.title', $listDirn, $listOrder); ?>
							</th>

							<?php
							if ($this->enableWaitingList == 'classroom_training' || $this->enableWaitingList == 'both')
							{
								?>

								<th align="left">
									<?php echo  JText::_('COM_JTICKETING_WAITING_LIST_STATUS'); ?>
								</th>

							<?php
							}
								?>

							<th class='left'>
								<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_WAITING_LIST_ID', 'waitlist.id', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>

					<tbody>
					<?php
					$j = 0;

					foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'b.ordering');
						?>
						<tr class="row<?php echo $i % 2; ?>" >
							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>

							<?php
								if (isset($this->items[0]->state))
								:
							?>
								<td class="center">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'waitinglist.', $canChange, 'cb'); ?>
								</td>
							<?php
								endif;
							?>

							<td data-title="<?php echo JText::_('COM_JTICKETING_WAITING_LIST_USER_NAME');?>">
								<?php echo htmlspecialchars($item->username); ?>
							</td>

							<td data-title="<?php echo JText::_('COM_JTICKETING_WAITING_LIST_NAME');?>">
								<?php echo htmlspecialchars($item->name); ?>
							</td>

							<td data-title="<?php echo JText::_('COM_JTICKETING_WAITING_LIST_EVENT_NAME');?>">
								<?php echo htmlspecialchars($item->title); ?>
							</td>

							<?php
							if ($this->enableWaitingList == 'classroom_training' || $this->enableWaitingList == 'both')
								:
								$id = array();
								$id = $item->id;
							 ?>
							 <td data-title="<?php echo JText::_('COM_JTICKETING_WAITING_LIST_STATUS');?>">
								<select id="assign_<?php echo $i ?>" name="assign_<?php echo $i ?>" onChange='jtCommon.waitinglist.changeStatus(<?php echo $i; ?>, <?php echo $id; ?>)'>
									<option value="WL"><?php echo JText::_('COM_JTICKETING_WAITLIST'); ?></option>
									<option value="C" <?php echo $item->status === 'c' || $item->status === 'C' ? 'selected':'' ?> ><?php echo JText::_('COM_JTICKETING_CLEAR'); ?></option>
									<option value="CA" <?php echo $item->status === 'CA' || $item->status === 'ca' ? 'selected':'' ?> ><?php echo JText::_('COM_JTICKETING_CANCEL'); ?></option>
								</select>
							</td>
							<?php
							endif;
							?>

							<td data-title="<?php echo JText::_('COM_JTICKETING_WAITING_LIST_ID');?>">
								<?php echo htmlspecialchars($item->id); ?>
							</td>

							<input type="hidden" id="event_id_<?php echo $i ?>" name="event_id" value="<?php echo $item->event_id; ?>" />

							<input type="hidden" id="user_id_<?php echo $i ?>" name="user_id" value="<?php echo $item->user_id; ?>" />
						</tr>
						<?php $j++;
					endforeach;
					?>
				</tbody>
			</table>

			<div class="row">
				<div class="col-xs-12">
					<?php $class_pagination = 'pagination';?>
					<div class="<?php echo $class_pagination;?> com_jticketing_align_center">
						<?php echo $this->pagination->getListFooter();?>
					</div>
				</div>
				<!-- col-lg-12 col-md-12 col-sm-12 col-xs-12-->
			</div>
		</div><!--j-main-container ENDS-->

			<?php
		}
		?>

		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" id="wid" name="wid" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<input type="hidden" name="controller" id="controller" value="waitinglist" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>

<script>
	/** global: jticketing_baseurl */
	var jticketing_baseurl = "<?php echo JUri::root();?>";
	var isAdmin = 0;
</script>
