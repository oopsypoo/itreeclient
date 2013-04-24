<?php
/**
 * Documentation, License etc.
 *
 * @package itreeclient
 */

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


class itreeClient
{
  //user input
  private $request;
  //string sent to server as a request
  private $data_request;
  //name of the data-tree we want to search.
  private $data_tree;
  private $received_data;
  private $hsocket;
  private $valid_object;
  private function writeIData()
  {
	  $CMDIT_DATA_GET = 0x01;
	  $DT_STRING = 0x01;
	  $index = 0;
	  
	  $buf_size = strlen($this->data_request)+1;
	  //pack data into binary/hex-values."C7"=>unsigned char		
	  $data = pack("C7",$CMDIT_DATA_GET, $DT_STRING, BYTE32($buf_size),BYTE24($buf_size),BYTE16($buf_size),BYTE8($buf_size),$index);

	  $n = fwrite($this->hsocket, $data);
	  if(!$n)
	  {
		  echo "First write failed(writeIData). Data: ".$data."...var_dump: ".var_dump($n)."\n";
		  return 0;
	  }
	  else 
	  { //echo "writeIData(data_request):".$this->data_request."\n"; 
		  $str = $this->data_request;
		  $n = fwrite($this->hsocket, $str);
		  if(!$n)
		  {
			  echo "Second write failed(writeIData). Data: $this->data_request...var_dump: ".var_dump($n)."\n";
			  return 0;
		  }
	  }
	  return 1;
  }
  private function readIData()
  {
	  $n = fread($this->hsocket, 7);
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
		  $itree_data = fread($this->hsocket, $buf_size+4);
		  
		  if(!$itree_data)
		  {
			  echo "Second read failed in readIData...buf_size: $buf_size, var_dump(n): ".var_dump($itree_data)." \n";
			  return NULL;
		  }
		  
	  }//for some reason I'm getting 1 byte more than I ask for at the begining of the data-buffer.
	  //remove it for now..
	  return substr_replace($itree_data, NULL, 0, 1);	
  }
  //constructor..setting most important stuff.
  function __construct($ip, $port, $searchterm=NULL)
  {
    $this->data_tree = "then";
    $this->request = $searchterm;
    $this->valid_object = true;
    if($this->request === NULL)
    {
	    echo "Error: No point in sending NULL. Exiting script.";
	    $this->valid_object = false;
	    return;
    }
    $this->data_request = $this->data_tree."&".$this->request."\0";
//    echo $this->data_request;
    $this->hsocket = fsockopen($ip, $port, $errno, $errstr);
    if($this->hsocket === FALSE)
    {
	    echo "Opening socket failed.\n";
	    echo "errno: $errno\n";
	    echo "error_str: $errstr\nExiting with return-value 1";
	    $this->valid_object = false;  
	    return;
    }	
    if($this->writeIData() === NULL)
    {
	    echo "writeIData returned NULL...exiting";
	    stream_socket_shutdown($this->hsocket, STREAM_SHUT_RDWR);
	    $this->valid_object = false;
	    return;
    }
    $this->received_data = $this->readIData();
    if($this->received_data === NULL)
    {
	    echo "readIData returned NULL...exiting";
	    stream_socket_shutdown($this->hsocket, STREAM_SHUT_RDWR);
	    $this->valid_object = false;
	    return;
    }
    //echo "Received data:\n".$received_data."\n";
    stream_socket_shutdown($this->hsocket, STREAM_SHUT_RDWR);
  }
 //The GetData-function should return different types of data, depending on
 //parameter given...1 represent unformated data. 2 - in table form, 3 - data stored in a 2-dim array
  public function GetData()
  {
    return $this->received_data;
  }
  public function validObject()
  {
    if(!$this->valid_object)
      return false;
    else
        return true;
  }
    
};

?>