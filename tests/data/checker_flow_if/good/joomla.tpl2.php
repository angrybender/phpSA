<?php if ($params->get('show_readmore') && $this->item->fulltext != null) :
	$link1 = JRoute::_('index.php?option=com_users&view=login');
	$link = new JURI($link1);?>
	<p class="readmore">
		<a href="<?php echo $link; ?>">
			<?php $attribs = json_decode($this->item->attribs);  ?>
			<?php
			if ($attribs->alternative_readmore == null) :
				echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
			elseif ($readmore = $this->item->alternative_readmore) :
				echo $readmore;
				if ($params->get('show_readmore_title', 0) != 0) :
					echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
				endif;
			elseif ($params->get('show_readmore_title', 0) == 0) :
				echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
			else :
				echo JText::_('COM_CONTENT_READ_MORE');
				echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
			endif; ?></a>
	</p>
<?php endif; ?>