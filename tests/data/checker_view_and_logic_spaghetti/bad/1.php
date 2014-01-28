<?php
function a($foo)
{
	foreach ($foo as $key1=>$value) {
		if ($foo[$key1][parent] == 0 ) {
			echo '<li><a href="/index.php?cPath='.$key1.'">'.$foo[$key1][name].'</a>';
			foreach ($foo as $key2=>$value) { if ($foo[$key2][parent] == $key1){echo '<ul>'; break; }};
			foreach ($foo as $key2=>$value) {
				if ($foo[$key2][parent] == $key1) {
					echo '<li><a href="/index.php?cPath='.$key1.'_'.$key2.'">'.$foo[$key2][name].'</a>';
					foreach ($foo as $key3=>$value) { if ($foo[$key3][parent] == $key2){echo '<ul>'; break;} };
					foreach ($foo as $key3=>$value) {
						if ($foo[$key3][parent] == $key2) {
							echo '<li><a href="/index.php?cPath='.$key1.'_'.$key2.'_'.$key3.'">'.$foo[$key3][name].'</a>';
							foreach ($foo as $key4=>$value) { if ($foo[$key4][parent] == $key3){echo '<ul>'; break;} };
							foreach ($foo as $key4=>$value) {
								if ($foo[$key4][parent] == $key3) {
									echo '<li><a href="/index.php?cPath='.$key1.'_'.$key2.'_'.$key3.'_'.$key4.'">'.$foo[$key4][name].'</a>';
									foreach ($foo as $key5=>$value) { if ($foo[$key5][parent] == $key4){echo '<ul>'; break;} };
									foreach ($foo as $key5=>$value) {
										if ($foo[$key5][parent] == $key4) {
											echo '<li><a href="/index.php?cPath='.$key1.'_'.$key2.'_'.$key3.'_'.$key4.'_'.$key5.'">'.$foo[$key5][name].'</a>';
											foreach ($foo as $key6=>$value) {if ($foo[$key6][parent] == $key5) {echo '<ul>'; break;} };
											foreach ($foo as $key6=>$value) {
												if ($foo[$key6][parent] == $key5) {
													echo '<li><a href="/index.php?cPath='.$key1.'_'.$key2.'_'.$key3.'_'.$key4.'_'.$key5.'_'.$key6.'">'.$foo[$key6][name].'</a>';
												}
											}
											foreach ($foo as $key6=>$value) { if ($foo[$key6][parent] == $key5){echo '</ul>'; break;} };
											echo '</li>';
										}
									}
									foreach ($foo as $key5=>$value) { if ($foo[$key5][parent] == $key4){echo '</ul>'; break;} };
									echo '</li>';
								}
							}
							foreach ($foo as $key4=>$value) {if ($foo[$key4][parent] == $key3) {echo '</ul>'; break;} };
							echo '</li>';
						}
					}
					foreach ($foo as $key3=>$value) { if ($foo[$key3][parent] == $key2){echo '</ul>'; break;} };
					echo '</li>';
				}
			}
			foreach ($foo as $key2=>$value) {if ($foo[$key2][parent] == $key1) {echo '</ul>'; break;} };
			echo '</li>';
		}
	}
}