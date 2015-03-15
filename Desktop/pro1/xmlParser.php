<?php

//XQR:xhodan08

class xmlParser {

    private $inputFront;    // input front
    private $input;         // actual processing XML
    private $i;             // actual position in input
    private $output;        // parsed XML

    function __construct () {

    }

    function __destruct () {

    }

    public function parseXml () {           // process XML

        $this->setNextInput();              // sets input

        $this->i = 0;                       // sets position index

        $this->parseXmlHeader();            // checks XML header

        $this->output = array();

        $this->output[] = $this->getNextTag();  // gets next XML tag
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
        while (trim($this->input[$this->i]) == "")      // skip whitespaces
            ++$this->i;

        if ($this->input[$this->i] == '<') {            // checks for next attribute

            $tag = array();

            $tagName = $this->determinateTag();             // get tag

            $tagAttrName = $this->getTagAttribute();        // get tags attribute

            while (isset($tagAttrName)) {

                $tagAttrValue = $this->getAttributeValue(); // get value of attribute

                $tag[$tagAttrName][] = $tagAttrValue;       // store into array

                $tagAttrName = $this->getTagAttribute();    // get tags attribute
            }

            $value = $this->getTagValue();                  // gets tag value

            if (isset($value))
                $tag[$tagName][] = $value;
            else
            while ($this->input[$this->i + 1] != '/') {
                $tag[$tagName][] = $this->getNextTag();

                while (trim($this->input[$this->i]) == "")      // skip whitespaces
                    ++$this->i;
            }

            $actualTag = $this->getEndTag();                // gets expected end tag

            if ($actualTag != $tagName)
                throw new Exception('invalid XML format, expected end of the tag ' .
                    $tagName . ' got ' . $actualTag . ' instead');

            return $tag;
        }
        else throw new Exception ('invalid input');
    }

    private function determinateTag () {   // returns name of a XML tag
        $tag = '';
        while ((isset($this->input[++$this->i])) &&
            (trim($this->input[$this->i]) != "") &&
            ($this->input[$this->i] != '>'))       // while not whitespace
            $tag .= $this->input[$this->i];
        return $tag;
    }

    private function getTagAttribute () {        // gets tag name attribute

        while (trim($this->input[$this->i]) == "")   // skip white spaces
            ++$this->i;

        $tagName = "";

        if ($this->input[$this->i] == ">") {
            ++$this->i;                             // skips >
            return NULL;
        }

        --$this->i;                                  // preparation

        while ($this->input[++$this->i] != '=')      // save name of the attribute
            $tagName .= $this->input[$this->i];

        ++$this->i;                                  // skips '='

        return $tagName;
    }

    private function getAttributeValue () {

        if ($this->input[$this->i] != "\"")
            throw new Exception('invalid XML format, expected attribute value');  // prefix check

        $value = NULL;

        while ($this->input[++$this->i] != "\"")     // save value of the attribute
            $value .= $this->input[$this->i];

        ++$this->i;                                  // skip '"'

        return $value;
    }

    private function getTagValue () {

        while (trim($this->input[$this->i]) == "")  // skip whitespaces
            ++$this->i;

        $value = NULL;

        --$this->i;                                 // preparation

        while ($this->input[++$this->i] != "<")     // save value of the attribute
            $value .= $this->input[$this->i];

        return $value;
    }

    private function getEndTag () {
        if ($this->input[++$this->i] != '/')
            throw new Exception('invalid XML format, expected end of the tag');

        $tag = '';

        while ($this->input[++$this->i] != ">")
            $tag .= $this->input[$this->i];

        $this->i++;
        return $tag;
    }

    private function setNextInput () {      // sets active input

        $file = fopen($this->inputFront, 'r') or die (2);

        $this->input = fread($file, filesize($this->inputFront));

        fclose($file);
    }

    // public setters functions

    public function add2InputFront ($input) {     // adds input to front
        $this->inputFront = $input;
    }

    // public getter functions

    public function getParsedXml () {
        return $this->output;
    }

}