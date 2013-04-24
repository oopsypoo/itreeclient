<?php
/*
	This is a try to port the client application "client" written in "C" for communicating with the itree-server.
	We are first of all going to write a simple client which can only "get" data.
	Here's some usefull information:
	port: 2001
	host: 10.0.0.3
	//these vales are constant in this version of the php-client	
	$CMDIT_DATA_GET = 0x01; //first byte of header
	$DT_STRING = 0x01; 		//second byte of header
	$index = 0x00;				//seventh byte of header
	The only variable is the buffer size which is sent as a 32-bit unsigned integer in ths order:
	bits 24-32 //use function BYTE32
	bits 16-24 //use function BYTE24
	bits 8-16  //use function BYTE16
	bits 1-8   //use function BYTE8
	
	The receiving end should then make a uint/ulong(32-bit unsigned integer/unsigned value)
	
*/
$request = $idlemma;
error_reporting(E_ALL);

function MAKEULONG($a, $b, $c, $d)
{
	return (($a<<24) | ($b<<16) | ($c<<8) | $d);
}
function BYTE32($dw)
{
	return (($dw & 0xFF000000)>>24);
}
function BYTE24($dw)
{
	return (($dw & 0x00FF0000)>>16);
}
function BYTE16($dw)
{
	return (($dw & 0x0000FF00)>>8);
}
function BYTE8($dw)
{
	return ($dw & 0x000000FF);
}




////send request for data
function writeIData($p, $fd)
{
	$CMDIT_DATA_GET = 0x01;
	$DT_STRING = 0x01;
	$index = 0;
	
	$buf_size = strlen($p)+1;
	//pack data into binary/hex-values."C7"=>unsigned char		
	$data = pack("C7",$CMDIT_DATA_GET, $DT_STRING, BYTE32($buf_size),BYTE24($buf_size),BYTE16($buf_size),BYTE8($buf_size),$index);

	$n = fwrite($fd, $data);
	if(!$n)
	{
		echo "First write failed(writeIData). Data: ".$data."...var_dump: ".var_dump($n)."\n";
		return 0;
	}
	else 
	{
		$n = fwrite($fd, $p);
		if(!$n)
		{
			echo "Second write failed(writeIData). Data: $p...var_dump: ".var_dump($n)."\n";
			return 0;
		}
	}
	return 1;
}

function readIData($fd)
{
	$n = fread($fd, 7);
	if(!$n)
	{
		"Reading of header-data failed...exiting: var_dump: ".var_dump($n)."\n";
		return NULL;
	}
	else
	{
		$pd = unpack('C7', $n);//unpack the header-package	
		//get first value
		$response_type = $pd[1];
		//get second value
		$data_type = $pd[2];
		//get ulong value-parts
		$data_index = $pd[7];
		$buf_size = MAKEULONG($pd[3],$pd[4],$pd[5],$pd[6]);
		$itree_data = fread($fd, $buf_size+4);
		
		if(!$itree_data)
		{
			echo "Second read failed in readIData...buf_size: $buf_size, var_dump(n): ".var_dump($itree_data)." \n";
			return NULL;
		}
		
	}//for some reason I'm getting 1 byte more than I ask for at the begining of the data-buffer.
	//remove it for now..
	return substr_replace($itree_data, NULL, 0, 1);	
}

if($request === NULL)
{
	echo "Error: No point in sending NULL. Exiting script.";
	exit(1);
}

$data_request = "then&".$request."\0";

//$ip = '10.0.0.3'; //old local testing ip
//we're going to make remote-request to my workstation.
//this is the ip
$ip = '84.212.23.235';
$port = 80;


$handle = fsockopen($ip, $port, $errno, $errstr);
if($handle === FALSE)
{
	echo "Opening socket failed.\n";
	echo "errno: $errno\n";
	echo "error_str: $errstr\nExiting with return-value 1";
	exit(1);
}	
if(writeIData($data_request, $handle) === NULL)
{
	echo "writeIData returned NULL...exiting";
	stream_socket_shutdown($handle, STREAM_SHUT_RDWR);
	exit(1);
}

$received_data = readIData($handle);
if($received_data === NULL)
{
	echo "readIData returned NULL...exiting";
	stream_socket_shutdown($handle, STREAM_SHUT_RDWR);
	exit(1);
}
//echo "Received data:\n".$received_data."\n";
stream_socket_shutdown($handle, STREAM_SHUT_RDWR);
?>
