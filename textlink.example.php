<?php

require_once ("contaxe.class.php");

$crq = new ContaxeRequest ();
// Keine Keywords aus der Seite auslesen
$crq->setNoCrawl ();
// Eigene Keywords uebergeben
$crq->setQuery ('Deine Keywords durch Kommas getrennt');
// Aufruf schicken und Antwort holen
$crs = new ContaxeResponse ($crq);
// Werbung ausgeben
echo $crs->generate ();

?>
