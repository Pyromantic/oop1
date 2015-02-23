<?php
/**
 * Created by PhpStorm.
 * User: eee
 * Date: 2/21/15
 * Time: 10:15 AM
 */

class xmlParser {

    private $file;          // entire file as string
    private $output;        // output string
    private $query;         // SQL query


    function __construct () {

    }

    function __destruct () {

    }

    public function parseXml () {
        $this->parseXmlHeader();

    }

    public function saveOutput ($outputFile) {


    }

    private function parseXmlHeader () {    // process XML header

    }

    private function getQueryCommand () {       // returns next query command
        static $i = 0;
        $retval = $this->query[$i];
        $i++;
        return $retval;
    }


    // setters functions

    public function setQuery ($query) {     // sets and parse input query
        $this->query = explode(" ", $query);
    }

    public function setInputFile ($input) {     // sets input as string

        $file = fopen($input, 'r') or die ('Soubor ' . $input . ' nelze otevřít' . "\n");

        $this->file = fread($file, filesize($input));

        fclose($file);
    }

    // getter functions
}