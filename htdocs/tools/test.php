<?php

$test = 'bla [img]http://johannesvg.nl/comic.php?tekst=alles is mis met comic sans mis met Jan Jeap[/img] [img]bla[/img]';

echo $test.'<br /><br />';

$pattern = "/\[img\]http:\/\/johannesvg.nl\/comic.php\?tekst=(.+?)\[\/img\]/is";
$replacement = '$1';

echo preg_replace($pattern, $replacement, $test);

?>
