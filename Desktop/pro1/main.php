<?php
require 'arguments.php';              // arguments Class

try {
    $args = new arguments($argc);     // new args Class

    $args->argsProcess($argv, $argc); // process arguments

} catch (Exception $e) {
    die('Caught exception: ' . $e->getMessage() . "\n");
}




/**
 * Created by PhpStorm.
 * User: Michal
 * Date: 17/02/2015
 * Time: 13:38
 */
?>
