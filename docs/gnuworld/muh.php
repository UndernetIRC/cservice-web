<?php
	include("../../php_includes/cmaster.inc");
	$var = "ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝ";
	$var2 = "àáâãäåçèéêëìíîïñòóôõöøùúûüý";

	echo $var2 . "<br>";
	echo strtolower($var2)."<br>";
	echo C_strtolower($var2)."<br>";
	die;
?>
