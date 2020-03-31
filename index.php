<?php

include ("Odds.php");

$data = new Odds();
var_dump (json_decode($data->load()));


?>