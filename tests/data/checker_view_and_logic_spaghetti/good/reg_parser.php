<?php
/**
 *
 * @author k.vagin
 */

function _buildParagraphs()
{
	if (!preg_match('/\<\/?' . self::BASE64_PARAGRAPH_TAG . '\>/', $this->_text)) {
		$this->_text = '<' . self::BASE64_PARAGRAPH_TAG . '>' . $this->_text . '</' . self::BASE64_PARAGRAPH_TAG . '>';
		$this->_text = preg_replace('/([\040\t]+)?(\n|\r){2,}/e', '"</" . self::BASE64_PARAGRAPH_TAG . "><" .self::BASE64_PARAGRAPH_TAG . ">"', $this->_text);
	}

	if (!preg_match('/\<' . self::BASE64_BREAKLINE_TAG . '\>/', $this->_text)) {
		$this->_text = preg_replace('/(\n|\r)/e', '"<" . self::BASE64_BREAKLINE_TAG . ">"', $this->_text);
	}
}