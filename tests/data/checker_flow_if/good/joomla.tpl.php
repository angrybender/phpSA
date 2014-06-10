<?php if (count($this->items) == 0): ?>
	<tr class="row0">
		<td class="center" colspan="7">
			<?php
			if ($this->total == 0):
				echo JText::_('COM_FINDER_NO_FILTERS');
				?>
				<a href="<?php echo JRoute::_('index.php?option=com_finder&task=filter.add'); ?>" title="<?php echo JText::_('COM_FINDER_CREATE_FILTER'); ?>">
					<?php echo JText::_('COM_FINDER_CREATE_FILTER'); ?>
				</a>
			<?php
			else:
				echo JText::_('COM_FINDER_NO_RESULTS');
			endif;
			?>
		</td>
	</tr>
<?php endif; ?>