<?php
/**
 * Created by PhpStorm.
 * User: Michal
 * Date: 17/02/2015
 * Time: 15:31
 */

class arguments             // process and store given arguments
{
    private $input;         // input file
    private $output;        // output file
    private $query;         // query
    private $nFlag;         // n flag
    private $root;          // root element

    function __construct ($argCount) {   // class construct
        if ($argCount < 2) {             // input args count test
            throw new Exception('Nebyl zadán žádný argument');
        }
        $this->nFlag = false;
        $this->root = NULL;
    }

    function __destruct () {        // class destruct

    }

    public function argsProcess ($argmnts, $argCount) {                     // arguments processing

        for ($i = 1; $i < $argCount; $i++) {

            if (substr($argmnts[$i], 0, 2) == '--') {                       // prefix check

                $arg = substr($argmnts[$i], 2);

                switch (substr($arg, 0, strpos($arg, '='))) {               // detect parameter
                    case 'help' :                                           // help

                        if ($argCount > 2)
                            throw new Exception('parameter help nelze kombinovat s jinými parametry');

                        echo 'this is help yayaya im lorde ' . "\n";
                        exit;
                        break;

                    case 'input' :

                        if (isset($this->input))
                            throw new Exception('parametr ' . substr($arg, 0, strpos($arg, '=')) . ' nelze zadat vícenásobně');

                        $this->input = substr($arg, strpos($arg, '=') + 1); // get input file name

                        $this->fileCheck($this->input);                     // checks if file is correct

                        break;

                    case 'output' :

                        if (isset($this->output))
                            throw new Exception('parametr ' . substr($arg, 0, strpos($arg, '=')) . ' nelze zadat vícenásobně');

                        $this->output = substr($arg, strpos($arg, '=') + 1); // get output file name

                        break;

                    case 'query' :

                        if (isset($this->query))
                            throw new Exception('parametr ' . substr($arg, 0, strpos($arg, '=')) . ' nelze zadat vícenásobně');

                        $this->query = substr($arg, strpos($arg, '=') + 1);      // store first command of query

                        $this->fillingQuery($i, $argmnts, $argCount);               // store rest of the query

                        break;

                    case 'qf' :

                        if (isset($this->query))
                            throw new Exception('parametr query nelze zadat vícenásobně');

                        $fileName = substr($arg, strpos($arg, '=') + 1); // store path to query filet

                        $this->fillingQueryByFile($fileName);

                        break;

                    case 'root':

                        if (isset($this->root))
                            throw new Exception('parametr ' . substr($arg, 0, strpos($arg, '=')) . ' nelze zadat vícenásobně');

                        $this->root = substr($arg, strpos($arg, '=') + 1); // store root element

                        break;

                    default :
                        throw new Exception('zadán neznámý parameter ' . $arg);
                        break;
                }
            } else {
                if ($argmnts[$i] == '-n')
                    $this->nFlag = true;
                else throw new Exception('zadán neznámý parameter ' . $argmnts[$i]);
            }
        }
        if (empty($this->query))
            throw new Exception('nebyl zadán parametr query, ani qf');
    }

    private function fileCheck ($file) {    // check if file is correct
        if (!file_exists($file))
            throw new Exception('zadaný vstupní soubor neexistuje');

        if (pathinfo($file, PATHINFO_EXTENSION) != 'xml')
            throw new Exception('zadaný vstupní soubor nemá příponu xml');
    }

    private function fillingQuery (&$i, $argmnts, $argCount) {      // filling query
        for (++$i;$i < $argCount; $i++) {                           // checks if it's possible to move on to next arg
            if ((empty($argmnts[$i][1]))  ||                        // very complex condition
                ((($argmnts[$i][0] == '-') && ($argmnts[$i][1] == '-')) ||
                    (($argmnts[$i][0]== '-') && ($argmnts[$i][1] == 'n')))) {
                $i--;
                break;
            }

            $this->query = $this->query . ' ' . $argmnts[$i];   // add command to query
        }
    }

    private function fillingQueryByFile ($fileName) {

        $file = fopen($fileName, 'r') or die ('Soubor ' . $fileName . ' nelze otevřít' . "\n");

        $this->query = fread($file, filesize($fileName));

        fclose($file);
    }

    // getters of private vars

    public function getQuery () {
        return $this->query;
    }

    public function getInput () {
        return $this->input;
    }

    public function getOutput () {
        return $this->output;
    }

    public function getNFlag () {
        return $this->nFlag;
    }

    public function getRoot () {
        return $this->root;
    }
}
