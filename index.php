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
	
	$rdata = $obj->GetData();


//	$rdata is a string or an array that contains data. It is either a message i.e. "Nothing returned..".
//or data in the form (type1)|syn1|syn2
//														(type2)|syn3|syn4
//															....			..		..
//														(typeN)|synA|synB

 	
 //	echo $rdata."<br /><br /><br /><br />\n\n\n";
 	
	  if($item !== NULL)
	  {
		  $data = '';
		  $string = strtok($rdata, "\n");
		  if($string !== false) 
		  {
			  $n = 0;
			  $data[$n++] = $string;
			  while($string !== false) 
			  {
				  $string = strtok("\n");
				  if($string !== false)
					  $data[$n++] = $string;
			  }
		  }
		  echo "<table style=\"border:1px solid;\">\n";
		  echo "<tr>\n";
		  echo "<th>Type</th>\n";
		  echo "<th>Synonymer</th>\n";
		  echo "</tr>\n";
		  foreach($data as $val)
		  {
  //			echo "Dette er val: ".$val."<br>";
			  
			  $p = 0;
			  $string = strtok($val,"|");
			  if($string !== false) 
			  {
				  echo "<tr>\n";
				  echo "<td style=\"border:1px solid;\">".$string."</td>\n";
				  echo "<td style=\"border:1px solid;\">\n";
				  while($string !== false)
				  {
					  $string = strtok("|");
					  if($string !== false)
					  {
						  if($p == 0)//and we add a link
							  echo "<a href=\"?lemma=$string\">".$string."</a>";
						  else 
							  echo ", "."<a href=\"?lemma=$string\">".$string."</a>";
						  $p++;
					  }
				  }
				  
				  echo "</td>\n";
				  echo "</tr>\n";
			  }	
		  }
		  echo "</table>\n";
	  }

	}
end_script:
echo <<<END

 </body>
 </html>
END;


?>	
	