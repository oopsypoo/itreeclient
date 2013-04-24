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
      throw new Exception("Value too big");
    
    $this->a = $k;
  }
  public function echoV()
  {
    echo "\n"."Verdien av a er ".$this->a."\n";
  }

};

$ob = new test(12);

$ob->echoV();

undef(
?>