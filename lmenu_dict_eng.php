<p>Dette er en engelsk ordbok inklusiv synonym-ordbok. Ordboken er noe enkel, men lettforst&aring;elig. Den er skrevet av noen ivrige personer fra princeton-university.
	Denne kan utgis s&aring; lenge deres <a href="index.php?mtype=2&menuitem=15&file=wnlicense" lang="no" title="Se p&aring; Word Net's lisens">lisens</a> f&oslash;lger med. V&aelig;r vennlig &aring; les denne.
</p>
<p>Synonym-ordboken er basert p&aring; en tekst-fil som jeg lastet ned en eller annen gang for &aring; teste data-strukturen "itree"(se http://itree.no).
	Synonym-ordboken er lagret og brukt av denne strukturen og bruker min egen-utviklede klient-server program skrevet for Linux.
</p>

<?php
	function rstrstr($haystack,$needle)
   {
  		return substr($haystack, 0,strpos($haystack, $needle));
   }
   function strstrb($h,$n)
   {
   	return array_shift(explode($n,$h,2));
	}
	$mtype = $_GET['mtype'];
	$mitem = $_GET['menuitem'];
	$todays_charset = "iso-8859-1";
	//include 'wnconfig.php';
	//include 'opendb.php';
	$dbhost = '10.0.0.112';
	$dbuser = 'ns';
	$dbpass = 'norskesoek';
	$dbname = 'dict';
	$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die                      ('Error connecting to mysql'.mysql_error());
	mysql_select_db($dbname);
	mysql_set_charset($todays_charset, $conn);
	
	echo	"<form id=\"dictid\" name=\"dictid\" method=\"get\" action=\"index.php\">\n";
		echo	"<input type=\"hidden\" name=\"mtype\" value=\"$mtype\" />\n";
	  	echo	"<input type=\"hidden\" name=\"menuitem\" value=\"$mitem\" />\n";
	  	echo	"<input name=\"lemma\" type=\"text\" id=\"lemma\" tabindex=\"1\" size=\"45\" maxlength=\"45\" />\n";
	  	echo	"<input type=\"submit\" name=\"dbsearch\" id=\"dbsearch\" value=\"Search\" tabindex=\"2\" />\n";
	echo "</form>\n";
	if(isset($_GET["file"]))
	{
		$license = $_GET["file"];
		if($license === "wnlicense")
		{
			$lines = file("license.txt");
			if(!$lines)
				echo "License-text does not exist or is truncated to zero length\n";
			else
			{
				echo "<table class=\"tabeller\">\n";
				foreach($lines as $line_num => $line)
					echo "<tr><td>$line</td></tr>\n";
				echo "</table>\n";
				
			}
		}
	}
	else
	{
		if(isset($_GET["lemma"]))
		{
			$idlemma=$_GET["lemma"];
			//do something
			$query  = "SELECT lemma, data FROM wndict WHERE lemma='".$idlemma."'";
			
			$result = mysql_query($query, $conn);
			if(!$result)
				echo mysql_error($conn);
			$row = mysql_fetch_row($result);
	//		if($row)
			{
				echo "<table class=\"tabeller\">\n";
				echo "<tr class=\"tab_row1_dict\">\n";
				echo "<th>S&oslash;keord</th>\n";
				echo "<th>Definisjon(er)</th>\n";
				echo "</tr>\n";
				echo "<tr>\n";
				echo "<td><b>".$idlemma."</b></td>\n";
				echo "<td>";
				if($row)
				{
					while(1)
					{
						if(!$row)
							break;
						echo $row[1]."<hr />\n";
						$row = mysql_fetch_row($result);
					}
				}
				else
					echo "<b>Fant ingenting</b>";
				echo "</td>\n";
				echo "</tr>\n";
			}
			mysql_free_result($result);
			
		echo "	<!-- now we get the synonyms   -->";
			
			$command = './client localhost then get '.$idlemma.' -1'; //(-1) - get all synonyms/data at localhost
			exec($command, $return_array);
			if($return_array[0] != "Nothing returned..")
			{
				$array_count = count($return_array);
				$n = 0;
				$value_count = 0;
				echo "<tr>\n";
				echo "<th colspan=\"2\"><b>Synonymer av</b> '<b>$idlemma</b>'</th>\n";
				echo "</tr>\n";
				$tokens=" ";
				strtok($return_array[$n], $tokens);
				strtok($tokens);
		
				$tokens="|\n";
				$res=strtok($tokens);
				echo "<tr>\n";
				while ($res !== false)
				{
		        		if($value_count == 0)
		        		{
	             		echo "<td>Som $res: </td><td>";
	             		$value_count++;
		        		}
		        		else
		        		{
		        			if($temp = strstrb($res, '('))
		        				echo "<a href=\"index.php?mtype=$mtype&menuitem=$mitem&lemma=".urlencode($temp)."\">$res</a>\n";
		        			else
		            		echo "<a href=\"index.php?mtype=$mtype&menuitem=$mitem&lemma=".urlencode($res)."\">$res</a>\n";
		            }    		
		        		$res = strtok($tokens);
				}
				echo "</td></tr>\n";
				if($array_count > 1)
				{
		        		
		        		$n++;
		        		while($n < $array_count)
		        		{
		        			if($n % 2)
		        				echo "<tr class=\"tab_row1_dict\">\n";
		        			else
		        				echo "<tr>\n";
	             		$value_count = 0;
	             		$res = strtok($return_array[$n], $tokens);
	             		while($res !== false)
	             		{
	                     		if($value_count == 0)
	                     		{
	                             		echo "<td>Som $res: </td><td>";
	                             		$value_count++;
	                     		}
	                     		else
						        		{
						        			if($temp = strstrb($res, '('))
						        				echo "<a href=\"index.php?mtype=$mtype&menuitem=$mitem&lemma=".urlencode($temp)."\">$res, </a>\n";
						        			else
						            		echo "<a href=\"index.php?mtype=$mtype&menuitem=$mitem&lemma=".urlencode($res)."\">$res, </a>\n";
						            }    		
	                     		$res = strtok($tokens);
	                     		if(!$res)
	                             		echo "</td>\n";
	             		}
	             		$n++;
	             		echo "</tr>\n";
		        		}
		      }
			}
			echo "</table>\n";
		} 
		include 'closedb.php';
	}
?>