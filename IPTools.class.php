<?php
/*
 * IP Tools
 *
 * @author     Nick MacCarthy <nickmaccarthy@gmail.com>
 * @version    1
 * @license    GPL v3, MIT
 */
class IPTools {

    /**
     * returns information for a CIDR block -- 32bit and 64bit safe
     *
     * @param    string  $ip_addr    either cidr notation of IP, or network -- note can be shorthard or regular cidr notation (i.e. 192.168.1.0/24, or 192.168.1/24)
     * @param    string  $netmask    netmask for network if not used with CIDR notation (i.e. 255.255.255.0, instead of /24)
     * @return   array   $cidr_info  associative array containg network information
     *
     * example return array for "192.168.1.0/24"
     * Array
            (
                [NUM_OF_HOSTS] => 255
                [RANGE_START] => 192.168.1.0
                [RANGE_END] => 192.168.1.255
                [BROADCAST_ADDRESS] => 192.168.1.255
                [NETWORK_ADDRESS] => 192.168.1.0
                [USABLE_NUM_OF_HOSTS] => 254
                [USABLE_RANGE_START] => 192.168.1.1
                [USABLE_RANGE_END] => 192.168.1.254
            )
     */
	public static function cidrinfo($ip_addr, $netmask = NULL) {


		if ( strpos($ip_addr, "/"))
		{
		    $ip_arr = explode('/', $ip_addr);

		    $ip_address = $ip_arr[0];
		    $netmask = $ip_arr[1];

		    /*
		    * 'Pad' out our IP if we got a shorthand notation for the IP (i.e. 192.168/16, instead of 192.168.0.0/16)
		    */
		    $dotcount = substr_count($ip_address, ".");
		    $padding = str_repeat(".0", 3 - $dotcount);
		    $ip_address .= $padding;

		}
		else
		{

		    $ip_address = $ip_addr;
		    $netmask = $netmask;

		    // verify our $ip_address and $netmask
		    if ( ! preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $ip_address)) return false;

		    // strip out a "/" if it exists to account for anyone putting "/24" instead of "24"
		    $netmask = str_replace("/", "", trim($netmask));

		    // check netmask, we can do a cidr netmask, or a full dotted quad IP (255.255.255.0);
		    // if we have a dotted quad netmask, convert it into its equivilent cidr (i.e. 255.255.255.0 -> /24)
		    if ( preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $netmask))
		    {
                $long = ip2long($netmask);
                $base = ip2long("255.255.255.255");

                $netmask = 32 - log( ( ($long ^ $base) + 1), 2);
		    }

		}


		//Convert our netmask into binary
		$bin = '';
		for( $i=1; $i <= 32; $i++) {

		   $bin .= $netmask >= $i ? '1' : '0';

		}

		// Now covnert our binary netmask into decimal
		$bit_mask = bindec($bin);

		$ip = ip2long($ip_address);
		$netmask = $bit_mask;
		$network = ( $ip & $netmask );
		$broadcast = $network  | ~$netmask;

		$usable_start = long2ip( $network + 1);
		$usable_end = long2ip( $broadcast - 1);

		$num_of_hosts = ( ( ip2long(long2ip($broadcast)) - ip2long(long2ip($network)) ) );

		$usable_num_hosts = ( ( ip2long($usable_end) - ip2long($usable_start) )+ 1);


		$cidr_info = array(
				"NUM_OF_HOSTS" => $num_of_hosts,
				"RANGE_START" => long2ip($network),
				"RANGE_END" => long2ip($broadcast),
				"BROADCAST_ADDRESS" => long2ip($broadcast),
				"NETWORK_ADDRESS" => long2ip($network),
				"USABLE_NUM_OF_HOSTS" => $usable_num_hosts,
				"USABLE_RANGE_START" => long2ip($network + 1),
				"USABLE_RANGE_END" => long2ip($broadcast - 1)
				);

		return $cidr_info;

	}


    /**
     * breaks a range of IPs in an array containg the ip addres of each host in that array
     *
     * @param    string  $ip_range   ip range you wish to break up, needs to be in this format:  192.168.1.0-192.168.1.10
     * @return   array   $range_arr  numerically indexed array of each host from the range given
     */
    public static function breakrange($ip_range)
    {

        if ( ! preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}-\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $ip_range)) return FALSE;
               
        $range_parts = explode("-", $ip_range);

        $range_start = ip2long($range_parts[0]);
        $range_end = ip2long($range_parts[1]);

        $range_diff = ($range_end - $range_start);

        for ($i = 0; $i <= $range_diff; $i++)
        {

            $ip = $range_start + $i;

            $range_arr[] = long2ip($ip);
        }

        return $range_arr;

    }

}
