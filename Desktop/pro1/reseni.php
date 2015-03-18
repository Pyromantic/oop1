<?php
$file = fopen("rozsireni", "w") or die ('KURWA FIX NENDE TO');

fwrite($file,"ORD\nLOG\n");

fclose($file);