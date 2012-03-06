<?php

require "../IPTools.class.php";

$range = "192.168.1.1-192.168.1.10";

$ips = IPTools::breakrange($range);

print_r($ips);


