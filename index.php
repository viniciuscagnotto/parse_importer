<?php header("Content-Type: text/html; charset=ISO-8859-1",true);?>
<!DOCTYPE html>
<head>
	<title>Gatópolis - Main</title>
	<meta charset="utf-8" />
	<meta name="description" content="Gatopolis - Main">
	<meta name="viewport" content="width=device-width">
</head>

<body>
	<form action="import.php" method="post" enctype="multipart/form-data">

		<div id="main">
			<p>Selecione o arquivo CSV que será importado: </p>
	  		<p><input type="file" name="csv" value="" /></p>
			<p><input type="submit" name="submit" value="Importar" /></p>
		</div>
	  
	</form>
</body>

</html>
