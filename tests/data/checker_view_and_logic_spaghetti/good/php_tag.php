<?php
/**
 *
 * @author k.vagin
 */

function addClassPHP( $className) {
		foreach($this->stack(1) as $node) {
			$classes = $node->getAttribute('class');
			$newValue = $classes
				? $classes.' <'.'?php '.$className.' ?'.'>'
				: '<'.'?php '.$className.' ?'.'>';
			$node->setAttribute('class', $newValue);
		}
		return $this;
}