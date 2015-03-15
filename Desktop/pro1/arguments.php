<?php

//XQR:xhodan08

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
        $this->input = NULL;
        $this->output = NULL;
    }

    function __destruct () {        // class destruct

    }

    public function argsProcess ($inputArgs, $argCount) {   // arguments processing

        for ($i = 1; $i < $argCount; $i++) {

            if (substr($inputArgs[$i], 0, 2) == '--') {         // prefix check

                $arg = substr($inputArgs[$i], 2);

                switch (substr($arg, 0, strpos($arg, '='))) {   // detect parameter

                    case 'input' :  // input

                        if (isset($this->input))
                            throw new Exception('parametr ' . substr($arg, 0, strpos($arg, '=')) . ' nelze zadat vícenásobně');

                        $this->input = substr($arg, strpos($arg, '=') + 1);  // get input file name

                        $this->fileCheck($this->input);          // checks if file is correct

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

                        $this->fillingQuery($i, $inputArgs, $argCount);          // store rest of the query

                        break;

                    case 'qf' :

                        if (isset($this->query))
                            throw new Exception('parametr query nelze zadat vícenásobně');

                        $fileName = substr($arg, strpos($arg, '=') + 1); // store path to query file

                        $this->fillingQueryByFile($fileName);

                        break;

                    case 'root':

                        if (isset($this->root))
                            throw new Exception('parametr ' . substr($arg, 0, strpos($arg, '=')) . ' nelze zadat vícenásobně');

                        $this->root = substr($arg, strpos($arg, '=') + 1); // store root element

                        break;

                    default :
                        if ($arg == 'help') {
                            if ($argCount > 2)
                                throw new Exception('parameter help nelze kombinovat s jinymi parametry');

                            echo self::help;
                        }

                        throw new Exception('zadan neznamy parameter ' . $arg);
                        break;
                }
            } else {
                if ($inputArgs[$i] == '-n')
                    $this->nFlag = true;
                else throw new Exception('zadán neznámý parameter ' . $inputArgs[$i]);
            }
        }

        if (empty($this->query))
            throw new Exception('nebyl zadán parametr query, ani qf');

        if (empty($this->input))
            $this->readInput();

    }

    private function readInput () {        // read standard input
         $this->input = file_get_contents('php://stdin');
    }

    private function fileCheck ($file) {    // check if file is correct
        if (!file_exists($file))
            die (2);
    }

    private function fillingQuery (&$i, $inputArgs, $argCount) {      // filling query
        for (++$i;$i < $argCount; $i++) {                           // checks if it's possible to move on to next arg
            if ((empty($inputArgs[$i][1]))  ||                      // very complex condition
                ((($inputArgs[$i][0] == '-') && ($inputArgs[$i][1] == '-')) ||
                    (($inputArgs[$i][0]== '-') && ($inputArgs[$i][1] == 'n')))) {
                $i--;
                break;
            }

            $this->query = $this->query . ' ' . $inputArgs[$i];   // add command to query
        }
    }

    private function fillingQueryByFile ($fileName) {

        $file = fopen($fileName, 'r') or die (2);

        $this->query = fread($file, filesize($fileName));

        fclose($file);
    }

    // public getters functions

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

    // constants

    const help = "•--help viz spolecne zadani vsech uloh
• --input=filename zadanu vstupni soubor ve formatu XML
• --output=filename zadany vystupni soubor ve formátu XML s obsahem podle zadaného dotazu
• --query='dotaz' zadany dotaz v dotazovacím jazyce definovaném nize (v pripade zadani timto zpusobem nebude dotaz obsahovat symbol apostrof)
• --qf=filename dotaz v dotazovacim jazyce definovaném nize zadany v externim textovem souboru (nelze kombinovat s --query)
• -n negenerovat XML hlavicku na vystup skriptu
• --root=element jmeno paroveho korenoveho elementu obalujici vysledky.
Pokud nebude zadan, tak se vysledky neobaluji korenovým elementem, ac to porusuje validitu XML.";
}
