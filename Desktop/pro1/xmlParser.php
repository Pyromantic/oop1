<?php
/**
 * Created by PhpStorm.
 * User: eee
 * Date: 2/21/15
 * Time: 10:15 AM
 */

class xmlParser {

    private $inputFront;    // input front
    private $input;         // actual processing XML
    private $i;             // actual position in input
    private $output;        // output XML string
    private $query;         // SQL query


    function __construct () {

    }

    function __destruct () {

    }

    public function parseXml () {           // process XML

        $this->setNextInput();              // sets input

        $this->i = 0;                       // sets position index

        $this->parseXmlHeader();            // checks XML header

        $tag = $this->getNextTag();         // gets next XML tag

        var_dump($tag);
    }

    public function saveOutput ($outputFile) {


    }

    private function parseXmlHeader () {    // process XML header

        while (isset($this->input[$this->i])) {       // search for prefix <?
            if ($this->input[$this->i] == '<') {
                if ($this->input[++$this->i] == '?') {
                    ++$this->i; break;
                } else throw new Exception('not a valid xml header ');
            } else ++$this->i;                        // incrementation
        }
        while (isset($this->input[$this->i])) {       // search for postfix ? >
            if ($this->input[$this->i] == '?') {
                if ($this->input[++$this->i] == '>') {
                    ++$this->i; break;
                } else throw new Exception('not a valid xml header');
            } else ++$this->i;
        }

    }

    private function getNextTag () {       // returns next XML tag in given input

        while (isset($this->input[$this->i])) {       // search for prefix
            if ($this->input[$this->i] == '<') {

                $tag = $this->determinateTag();       // get Tag

                $tagName = $this->getTagAttribute();  // get tags attribute

                $tagValue = $this->getTagValue();     // get value of attribute

                $this->skip2EOT();                    // skips to end of the tag

                echo $tag . ' ' . $tagName . ' ' . $tagValue;

                if ($tagName = "name")
                    $table = $tagValue;
                exit;

            } else ++$this->i;                        // incrementation
        }
    }

    private function determinateTag () {   // returns name of a XML tag
        $tag = '';
        while (trim($this->input[++$this->i]) != "")       // while not whitespace
                $tag .= $this->input[$this->i];
        return $tag;
    }

    private function getTagAttribute () {        // gets tag name attribute

        while (trim($this->input[$this->i]) == "")   // skip white spaces
            ++$this->i;

        $tagName = "";
        --$this->i;                                  // preparation

        while ($this->input[++$this->i] != '=')      // save name of the attribute
            $tagName .= $this->input[$this->i];

        ++$this->i;                                  // skip '='

        return $tagName;
    }

    private function getTagValue () {

        if ($this->input[$this->i] != "\"") throw new Exception('invalid XML format');  // prefix check

        $value = "";

        while ($this->input[++$this->i] != "\"")     // save value of the attribute
            $value .= $this->input[$this->i];

        ++$this->i;                                  // skip '"'

        return $value;
    }

    private function skip2EOT () {                    // skips to end of the actual tag
        while ($this->input[$this->i] != ">")
            ++$this->i;
        ++$this->i;
    }

    private function getQueryCommand () {       // returns next query command
        static $i = 0;
        $retval = $this->query[$i];
        $i++;
        return $retval;
    }

    private function setNextInput () {      // sets active input

        $file = fopen($this->inputFront, 'r') or die ('Soubor ' . $this->inputFront . ' nelze otevřít' . "\n");

        $this->input = fread($file, filesize($this->inputFront));

        fclose($file);
    }

    // setters functions

    public function setQuery ($query) {     // sets and parse input query
        $this->query = explode(" ", $query);
    }

    public function add2InputFront ($input) {     // adds input to front
            $this->inputFront = $input;
    }

    // getter functions
}