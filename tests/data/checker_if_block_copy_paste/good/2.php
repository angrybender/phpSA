<?php
/**
 *
 * @author PHPEXcel
 */

function writeXf()
{
	if (self::_mapBorderStyle($this->_style->getBorders()->getBottom()->getBorderStyle()) == 0) {
		$this->_bottom_color = 0;
	}
	if (self::_mapBorderStyle($this->_style->getBorders()->getTop()->getBorderStyle())  == 0) {
		$this->_top_color = 0;
	}
	if (self::_mapBorderStyle($this->_style->getBorders()->getRight()->getBorderStyle()) == 0) {
		$this->_right_color = 0;
	}
	if (self::_mapBorderStyle($this->_style->getBorders()->getLeft()->getBorderStyle()) == 0) {
		$this->_left_color = 0;
	}
	if (self::_mapBorderStyle($this->_style->getBorders()->getDiagonal()->getBorderStyle()) == 0) {
		$this->_diag_color = 0;
	}
}