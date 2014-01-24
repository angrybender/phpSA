<?php

/**
 * http://govnokod.ru/13825
 */

require_once ("db.php");

$region = $_POST["region"];

$array = mysql_query ("SELECT * FROM city WHERE region = '$region'");
echo "<option value=\"\">Выберете город</option>";
while ($m = mysql_fetch_array($array)){
	echo "<option value=\"".$m["alias"]."\" id=\"".$m["id"]."\">".$m["title"]."</option>";
}