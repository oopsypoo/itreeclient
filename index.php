<?php
	header('Content-language: nb-NO');	
	header('Content-type: text/html; charset=UTF-8');
	$title = "itree-php-client-test";
	
	
	echo <<<END

	<!DOCTYPE HTML>
	<html>
	<head>

	<meta charset="utf-8">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="description" content="Testing the itree-client. The php-version. Testing connection to home-computer" />
	<meta name="keywords" content="iTree, itreeclient, itree-client, itree client, php cilent" />
	<title>{$title}</title>
	</head>
	<body>
	<form action="index.php" method="get" enctype="application/x-www-form-urlencoded" target="_self">
	<input type="text" name="lemma" rows="1" cols="20" placeholder="Søkeord" /><br />
	<input name="soek" value="Søk" type="submit" />
	</form>
END;
	$item = NULL;
	if($method = $_SERVER['REQUEST_METHOD']) 
	{
		if($method == 'GET')
		{
			if(isset($_GET['lemma']))
			{
				$item = $_GET['lemma'];
				$temp = strstr($item, ' (', TRUE);//look for " (". If yes return everything on the left side
				if($temp)
				  $item = $temp;
			}
		}
	}
	echo "	<!-- now we get the synonyms   -->";
	if($item !== NULL) {
	$idlemma = $item;
	echo "Synonyms of <b><i>$idlemma</i></b> are: <br/><br/>\n";
//	include_once("client.php");
	include_once("itreeclient.php");
	$obj = new itreeClient('84.212.23.235', 80,$idlemma);
	if(!$obj->validObject())
	{
	  echo "\n<br />************************************************************<br />
			*****   Could not initiate itreeClient-object..*************<br />
			************************************************************<br />\n";
			goto end_script;
	}
	
	
//	$rdata is a string or an array that contains data. It is either a message i.e. "Nothing returned..".
//or data in the form (type1)|syn1|syn2
//														(type2)|syn3|syn4
//															....			..		..
//														(typeN)|synA|synB

 	
 //	echo $rdata."<br /><br /><br /><br />\n\n\n";
 	
	  if($item !== NULL)
	  {
		$obj->GetData(1);  
	  }

	}
end_script:
echo <<<END

 </body>
 </html>
END;


?>	
	