<?php
/**
 *
 * @author k.vagin
 */

public function getImages() {

	$images = array();
	if (isset($this->_headerFooterImages[self::IMAGE_HEADER_LEFT])) 	$images[self::IMAGE_HEADER_LEFT] = 		$this->_headerFooterImages[self::IMAGE_HEADER_LEFT];
	if (isset($this->_headerFooterImages[self::IMAGE_HEADER_CENTER])) 	$images[self::IMAGE_HEADER_CENTER] = 	$this->_headerFooterImages[self::IMAGE_HEADER_CENTER];
	if (isset($this->_headerFooterImages[self::IMAGE_HEADER_RIGHT])) 	$images[self::IMAGE_HEADER_RIGHT] = 	$this->_headerFooterImages[self::IMAGE_HEADER_RIGHT];
	if (isset($this->_headerFooterImages[self::IMAGE_FOOTER_LEFT])) 	$images[self::IMAGE_FOOTER_LEFT] = 		$this->_headerFooterImages[self::IMAGE_FOOTER_LEFT];
	if (isset($this->_headerFooterImages[self::IMAGE_FOOTER_CENTER])) 	$images[self::IMAGE_FOOTER_CENTER] = 	$this->_headerFooterImages[self::IMAGE_FOOTER_CENTER];
	if (isset($this->_headerFooterImages[self::IMAGE_FOOTER_RIGHT])) 	$images[self::IMAGE_FOOTER_RIGHT] = 	$this->_headerFooterImages[self::IMAGE_FOOTER_RIGHT];

	return $images;
}