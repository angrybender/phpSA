<?php
/**
 *
 * @author k.vagin
 */

namespace Extractors;


class Full extends \Analisator\ParentExtractor {

	/**
	 * передает код 1-в-1
	 */
	public function extract(array $filter = null)
	{
		return array(array(
			'body' => $this->tokens,
			'type' => '',
			'line' => 0
		));
	}

} 