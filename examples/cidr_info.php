<?php

/**
 * This example will display information about a cidr block
 * 
 * You can pass along a full cidr nottaion such as "192.168.1.0/24", or a shorthand notation like "10/8"
 */

require "../IPTools.class.php";


// This example shows you can pass along a full cidr notation and we will see the results
$cidr1 = "192.168.1.0/24";
echo "$cidr1\n";
$ips = IPTools::cidrinfo($cidr1);

print_r($ips);


$cidr2 = "10/8";
echo "$cidr2\n";
$ips = IPTools::cidrinfo($cidr2);

print_r($ips);

