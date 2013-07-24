<?php

require_once("Tuenti.php");

header('Content-Type: text/html; charset=UTF-8');

//Test
$miTuenti = new Tuenti('miemail@dominio.com', 'mipasssword');
$r = $miTuenti->getFriendsData();

//Obtenemos los amigos con numero de telefono
foreach($r as $f)
  if($f->phone_number != '')
		echo "<br>".$f->name." ".$f->surname." Phone: ".$f->phone_number;


?>
