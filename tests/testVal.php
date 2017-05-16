<?php

$sv = "1,233";

$sv = intval(str_replace(",", "", $sv));



$al = array("a", "b", "c");
$al[] ="d";
$ret = array_search("d", $al);
var_dump(count($al)-1);
var_dump($ret);
var_dump("this is a val $al[1]afd ");