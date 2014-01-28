<?php
/**
 *
 * @author k.vagin
 */

for($i=0;$i<10;$i++) {
	pcntl_wait($i);
}