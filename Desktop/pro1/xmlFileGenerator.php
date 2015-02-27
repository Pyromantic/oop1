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


    function __construct ($outputFile) {
        if (isset($outputFile))
            $this->fileName = $outputFile;
    }

    function __destruct () {

    }

    public function generateXML () {


        foreach ($this->input as $input) {
            $this->nestIn($input);
        }

        var_dump( $this->output);

      //  $this->createFile();

    }

    private function nestIn ($input) {
        if (is_array($input))
            $last = array_search(end($input), $input);
        else {
           $this->output .= $input . "\n";
            return;
        }

        $print = '';

        foreach ($input as $key => $tag) {
            if ($key != $last) {
                foreach ($tag as $tag2)
                 $print .= ' ' . $key . '="' . $tag2 . "\"" ;

            } else {
                $print = '<' . $key . $print . ">\n";
                $this->output .= $print;

                foreach ($tag as $tag2)
                    $this->nestIn($tag2);

                $print = '</' . $key . ">\n";
                 $this->output .= $print;
            }

        }

    }

    private function createFile () {    // generates an output file

        $file = fopen($this->fileName . '.xml', "w") or die ('unable to create file');

        fwrite($file, $this->output);

        fclose($file);

    }

    // setters functions

    public function setXML ($input) {
        $this->input = $input;
    }

}