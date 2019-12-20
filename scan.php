<?php 
include "data/config.inc.php";
function getBetween($content, $start, $end)
{
    $r = explode($start, $content);
    if (isset($r[1]))
    {
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}

if($_POST["task"]=="scan")
{
set_time_limit(0);
print(str_repeat(" ", 300) . "\n");
$range = $_POST['range'];
$range = explode('.', $range );
foreach( $range as $index=>$octet )
	$range[$index] = array_map( 'intval', explode('-',$octet) );
	
// 4 for loops to generate the ip address 4 octets
for( $octet1=$range[0][0]; $octet1<=(($range[0][1])? $range[0][1]:$range[0][0]); $octet1++ )
for( $octet2=$range[1][0]; $octet2<=(($range[1][1])? $range[1][1]:$range[1][0]); $octet2++ )
for( $octet3=$range[2][0]; $octet3<=(($range[2][1])? $range[2][1]:$range[2][0]); $octet3++ )
for( $octet4=$range[3][0]; $octet4<=(($range[3][1])? $range[3][1]:$range[3][0]); $octet4++ )
{
	// assemble the IP address
	$ip = $octet1.".".$octet2.".".$octet3.".".$octet4;
	
	// initialise the URL
	
	$ch = curl_init("http://" . $ip);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	$text = curl_exec($ch);
	
	// print the result for that IP address
	
	if (strpos($text, 'Tasmota') !== false)
		{ 
        //Get Version
        $url = 'http://' . $ip . '/cm?cmnd=status%202';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $version = getBetween($data, '"Version":"', '"');

        //Get Name
        $url = 'http://' . $ip . '/cm?cmnd=status';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $name = getBetween($data, 'FriendlyName":["', '"');
        $name = str_replace("'", "", $name);

		$db_handle = mysqli_connect($DBServer, $DBUser, $DBPassword);
        	$db_found = mysqli_select_db($db_handle, $DBName);
       		$check = mysqli_query($db_handle, "select * from devices where ip = '$ip'");
        	$checkrows = mysqli_num_rows($check);
	        if ($checkrows < 1)
	        	{
            
        	    	$sql = "INSERT INTO devices (name,ip,version) VALUES ('$name', '$ip', '$version')";
			$result = mysqli_query($db_handle, $sql);
			}
		}
} 
}
header("Location: index.php");

?>
