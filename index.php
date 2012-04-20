<?php

	/* SETTINGS */
	$LINES_PER_PAGE = 18;

	$target = empty($_GET["target_url"]) ? "http://" : $_GET["target_url"];
	
	if($target != "http://")
	{
		$ch = curl_init($target);

		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

		$result = curl_exec($ch);
		curl_close($ch);

		preg_match("/<title.*\/title>/s", $result, $title);
		preg_match("/<body.*\/body>/s", $result, $body);

		$title = (count($title) > 0) ? $title[0] : "";
		
		if(count($body) > 0) { 

			$body = $body[0]; 

			/*$body = preg_replace("~<script (.|\n)*?>(.|\n)*?</script>~","",$body);
			//$body = preg_replace("~<noscript (.|\n)*?>(.|\n)*?</noscript>~","",$body);*/

			//$body = preg_replace("/<img.*\/img>/s","",$body);
			//$body = preg_replace('~<img [^>]* />~',"",$body);
			//$body = preg_replace('~<img.*src.*">~',"",$body); //remove images without a close tag

			//$body = preg_replace('~<script.*>.*</script>~',"",$body); //remove scripts

			$body = preg_replace('~<img [^>]* />~',"",$body); //remove images
			$body = preg_replace('~<form.*>.*</form>~',"",$body); //remove forms

			$body = preg_replace('~<a~',"<span",$body); //convert anchors into spans
			$body = preg_replace('~</a>~',"</span>",$body);

			$body = preg_replace('~\s{2}+~'," ",$body); //reduce blanks
			//$body = preg_replace('~<!\[endif\]-->~',"",$body); //remove endifs

			$patterns = array();
			$patterns[0] = '~<title>~';
			$patterns[1] = '~</title>~';
			$title = preg_replace($patterns, "", $title);
			$body = "<div id=\"page-logo\"> {$title} </div> {$body} ";

			$lines = explode(PHP_EOL, $body);
		}
	}
?>

<html>
<head>
	<?php if(!empty($title)) { echo "<title>{$title}</title>"; } ?>
	<link rel="stylesheet" type="text/css" href="style.css">
	<script type="text/javascript" src="scripts/jquery.min.js"></script>
	<script type="text/javascript" src="scripts/jquery-ui-1.8.19.custom.min"></script>
	<script type="text/javascript" src="scripts/date.formatting.js"></script>
	<script type="text/javascript" src="scripts/jquery.textshadow.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {

			$('#welcome-logo span').effect("pulsate", { times:5 }, 1000);

			$('#welcome-logo span').textShadow({
				x: "5px",
				y: "5px",
				color: "#0000F8"
			});

			$current_page=1;
			$page_count=<?php echo ceil(count($lines)/$LINES_PER_PAGE); ?>;

			ShowDate();
			ShowCurrentPage();
 		});

		function ShowDate() {

			var date=new Date();
			$('#date-display').html(getCalendarDate()+" --- "+getClockTime());

			setTimeout("ShowDate()", 1000);

		}

 		function ShowCurrentPage() {

 			$('#page-display').html("Page: " + $current_page+ "/" +$page_count);
 			$('#paged-content').children().hide();
 			$('#page-' + $current_page).show();
 			$current_page = ($current_page < $page_count) ? $current_page+1 : 1;

 			setTimeout("ShowCurrentPage()", 6000);
 		}

	</script>
</head>
<body>
	<div id="header">
		<div id="welcome-logo"><span>Welcome to ChowFax</span></div>
		<span id="date-display"></span>
	</div>
	
	<div id="nav-bar">
		<form id="url-form" method="GET" action="index.php">
			<label for="target_url">Url</label>
			<input id="target_url" name="target_url" type="text" size="40" 
				value="<?php echo $target; ?>" /> 
			<input type="submit" value="Go!"/>
		</form>
		
		<span id="page-display"></span>
	</div>
	
<?php if(!empty($body)) { ?>

	<dl id="paged-content">
		
		<?php 

			$paged_content = "";
			$page_count = ceil(count($lines)/$LINES_PER_PAGE);

			$current_line = 0;

			for($i=1; $i<=$page_count; $i++) {

				$chunk = implode("", array_slice($lines, $current_line, $LINES_PER_PAGE));

				$paged_content = $paged_content . "<dt>Page {$i}</dt>";			
				$paged_content = $paged_content . "<dd id=\"page-{$i}\"> {$chunk} </dd>";

				$current_line = $current_line + $LINES_PER_PAGE;
			}
			echo $paged_content;
		?>
	</dl>
<?php } ?>

</body>
</html>



