<?php

require_once("Tuenti.php");

header('Content-Type: text/html; charset=UTF-8');

//Test
$miTuenti = new Tuenti('email@email.com', 'password');


if($miTuenti->login()) //Primero conectamos para obtener una sesion!
{
	$r = $miTuenti->getFriendsData();

	//Obtenemos los amigos con numero de telefono
	foreach($r as $f)
  		if($f->phone_number != '')
			echo "<br>".$f->name." ".$f->surname." Phone: ".$f->phone_number;

}
else
	echo "Hubo un error en al conectar!";
	


?>
