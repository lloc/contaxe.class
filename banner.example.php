<?php

require_once ("contaxe.class.php");

$crq = new ContaxeRequest ();
// Bannerformat setzen
$crq->setFormat ('img');
// 468x60 anfordern
$crq->setDimension (1);
// Eigene Keywords uebergeben
$crq->setQuery ('Deine Keywords durch Kommas getrennt');
// Aufruf schicken und Antwort holen
$crs = new ContaxeResponse ($crq);
// Werbung ausgeben
echo $crs->generate ();

?>
