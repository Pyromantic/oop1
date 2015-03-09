<?php
/**
 * Created by PhpStorm.
 * User: Michal
 * Date: 27/02/2015
 * Time: 19:06
 */

class xmlFileGenerator {

    private $input;         // input array
    private $fileName;      // Name of the output File, if empty => stdout
    private $output;        // output XML string
    private $nFlag;         // n Flag
    private $root;          // root tag name


    function __construct ($outputFile) {
        if (isset($outputFile))
            $this->fileName = $outputFile;
    }

    function __destruct () {

    }

    public function generateXML () {

        if (isset($this->nFlag))
            $this->output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

        if (isset($this->root))
            $this->output .= '<' . $this->root . ">\n";

        if (isset($this->input))
            foreach ($this->input as $input)
                $this->nestIn($input);          // nests into

        if (isset($this->root))
            $this->output .= '</' . $this->root . ">";

        if (isset($this->fileName))
            $this->write2File();
        else
            echo $this->output;

    }

    private function nestIn ($input) {  // nests into given array of XML tags and values
        if (is_array($input))
            $last = array_search(end($input), $input);      // get last item of array
        else {
           $this->output .= $input . "\n";                  // tag value
            return;
        }

        $print = '';

        foreach ($input as $key => $tag)
            if ($key != $last) {               // not last item, attribute expected
                foreach ($tag as $tag2)
                 $print .= ' ' . $key . '="' . $tag2 . "\"" ;

            } else {
                $print = '<' . $key . $print . ">\n";   // tag
                $this->output .= $print;

                foreach ($tag as $tag2)
                    $this->nestIn($tag2);               // nests in to print value

                $print = '</' . $key . ">\n";           // end of the tag
                 $this->output .= $print;
            }
    }

    private function write2File () {    // generates an output file

        $file = fopen($this->fileName, "w") or die ('nelze vztvořit soubor');

        fwrite($file, $this->output);

        fclose($file);
    }

    // setters functions

    public function setXML ($input) {       // sets input XML
        $this->input = $input;
    }

    public function setFilename ($name) {   // sets output file name
        $this->fileName = $name;
    }

    public function setNFlag ($flag) {      // sets n flag
        $this->nFlag = $flag;
    }

    public function setRoot ($root) {       // sets root tag
        $this->root = $root;
    }
}