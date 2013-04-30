<?php
/**
 * Documentation, License etc.
 *
 * @package test
 */

 
	    
class test
{
  private $a;
  
  function __construct($k)
  {
    if($k > 10)
      throw new Exception("Value too big");./start
    
    $this->a = $k;
  }
  public function echoV()
  {
    echo "\n"."Verdien av a er ".$this->a."\n";
  }
  public function arrangeString()
  {
    $string = "(verb)|run|fast|as|hell\n(verb)|walk|slow|as|turtle\n(verb)|fly|high|as|bird";
   
      //echo $string."HHHHHHH\n";
      
    $str = explode("\n", $string);
      //echo $str."\n";
    $n = 0;
    $t = count($str);
    while($n < $t) 
    {
      $p = 0;
      $data[$n][$p] = strtok($str[$n], '|\0');
      while ($data[$n][$p] !== false) 
      {
	$p++;
	$data[$n][$p] = strtok('|\0');
      }
      $n++;
    }
    var_dump($data);
  }
};

$ob = new test(9);

$ob->echoV();
$ob->arrangeString();

?>