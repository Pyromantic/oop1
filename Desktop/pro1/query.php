<?php

//XQR:xhodan08

class query {

    const QUE = 3;           // query unique elements
    const FIRST = 0;         // represents first element of array

    private $query;          // parsed SQL query
    private $input;          // parsed XML
    private $output;         // applied SQL on parsed XML


    function __construct () {
        $this->output = NULL;
    }

    function __destruct () {

    }



    public function applyQuery () {

        if (empty($this->query['FROM']))
            return;

        $this->applyFrom();         // apply FROM Command

        $this->applySelect();       // apply SELECT Command

        if (isset($this->query['LIMIT']) && isset($this->output))
            $this->applyLimit();    // apply LIMIT Command

        if (isset($this->query['WHERE']) && isset($this->output))
            $this->applyWhere();    // apply WHERE Command

        if (isset($this->query['ORDER']) && isset($this->output))
            $this->applyOrder();    // apply ORDER Command

    }


    private function applyFrom () {         // apply SQL FROM, result stored in $output
        if ($this->query['FROM'] == 'ROOT') {
            $this->output = $this->input;
            return;
        }
        $this->digDataByFrom($this->input, $this->query['FROM']);
    }

    private function digDataByFrom ($input) {    //  iterates through given array
        if (!is_array($input))
            return;

        foreach($input as $tag)
            if (isset($tag[$this->query['FROM']])) {
                $this->output[] = $tag;
                return;
            } else $this->digDataByFrom($tag);

    }


    private function applySelect () {       // apply SQL SELECT, result stored in $output
        $this->input = $this->output;       // sets newly parsed input

        $this->output = NULL;

        $this->digDataBySelect($this->input,$this->query['SELECT']);
    }

    private function digDataBySelect ($input, $seek) {  //  iterates through given array
        if (!is_array($input))
            return;

        foreach($input as $tag) {
            if (isset($tag[$seek])) {
                $this->output[] = $tag;
            } else $this->digDataBySelect($tag, $seek);
        }

    }


    private function applyLimit () {    // apply SQL LIMIT Command
        $this->input = $this->output;

        $this->output = NULL;

        foreach ($this->input as $item)
            if ($this->query['LIMIT']--)
                $this->output[] = $item;
            else
                break;
    }


    private function applyWhere () {    // apply SQL WHERE Command
        $rejected = array();
        foreach ($this->query['WHERE'] as $sets) {
            if (isset($sets['NOT'])) {
                $this->whereNests($sets, $rejected);
            } else {
                foreach ($sets as $actual)
                    $this->whereNests($actual, $rejected);
            }
        }
    }

    private function whereNests ($actual, $rejected) {
            $negation = $actual['NOT'];             // get negation

            unset($actual['NOT']);                  // unset NOT element

            $affection = $actual['AFFECTION'];      // nonfunctional, saved for later versions
            unset($actual['AFFECTION']);

            if ($affection == 'AND') {
                $this->input = $this->output;
                $this->output = NULL;
                $rejected = NULL;
            } else {
                $this->input = $rejected;
            }

            if ($negation) {
                foreach ($this->input as $tag)      // iterate through actual tag
                    if (!$this->digDataByWhere($tag, $actual))
                        $this->output[] = $tag;
                    else
                        $rejected[] = $tag;
            } else {
                foreach ($this->input as $tag)      // iterate through actual tag
                    if ($this->digDataByWhere($tag, $actual))
                        $this->output[] = $tag;
                    else
                        $rejected[] = $tag;
            }
    }


    private function digDataByWhere ($input, $actual) {  //  iterates through given array

        $found = false;

        foreach ($input as $tag)
            if ($this->whereCondition($tag, $actual))    // apply where condition on actual tag
                return true;
            else {
                if (!is_array($tag))
                    return false;
                $found = $this->digDataByWhere($tag, $actual);
            }
        return $found;
    }

    private function whereCondition ($input, $actual) {
        if (!is_array($input))
            return false;

        $result = false;

        $condition =  function ($value) use ($actual) {       // evaluates condition
            $operator = end($actual);
            $key = key($actual);

            if ($key == 'CONTAINS') {                       // if CONTAINS
               if (strpos($value, $actual['CONTAINS']) !== false)
                   return true;
                else
                   return false;
            }

            return eval('return ' .
                    $value  . $key .  $operator
                     . ';');
        };

        foreach (array_slice($actual, 0, -1) as $index)
            if (isset($input[$index])) {
                foreach ($input[$index] as $value)
                    if ($condition($value))
                        $result = true;
            }

        return $result;
    }


    private function applyOrder ()
    {    // apply SQL query ORDER BY command

        $this->input = $this->output;
        $this->output = NULL;

        $order = array();

        foreach ($this->input as $inputTag)
            $this->orderNests($order, $inputTag);

        switch ($this->query['ORDER']['DIRECTION']) {   // sorts array elements
            case 'ASC' :
                arsort($order);
                break;

            case 'DESC' :
                asort($order);
                break;
        }

        $position = 1;

        foreach ($order as $key => $tag) { // apply sorted array to output
            $this->orderAssignNests($this->input[$key], $position);
            $this->output[$key] = $this->input[$key];
            ++$position;
        }
    }

    private function orderNests (&$order, $input) {     //  nests through given arrays, return when sought element is found
        $element = $this->query['ORDER']['ELEMENT'];

        foreach($input as $tag)
            if (isset($tag[$element])) {
                  $order[] = $tag[$element][0];
                return;
            } else {
                if (!is_array($tag))
                    return;
                $this->orderNests($order, $tag);
            }


    }

    private function orderAssignNests (&$input, $position) {
        if (!is_array($input))
            return;

        if (empty($input['order'])) {

            $tmp['order'] = array(0 => $position);
            $input = $tmp + $input;

            end($input);
            $last = &$input[key($input)];   // next tag is last

            foreach ($last as &$tag) {
                if (!is_array($tag))
                    continue;
                $this->orderAssignNests($tag, $position);
            }
        }
    }


    public function parseQuery ($query) {   // parse Query and sets individual elements

        $query = $this->prepareQuery($query);       // prepares query

        $count = count($query);                     // counts elements of query

        $i = self::FIRST;                           // index of query field

        $inc = function () use (&$i, $count) {      // increments index of query filed
            return ($i == $count) ? die(4) : ++$i;
        };

        $affection = function ($element) use ($inc) {   // checks affection

            $inc();

            if ($element == 'OR' || $element == 'AND')
                return $element;

            throw new Exception('Neplatne logicke spojeni (AND/OR) v SQL query');
        };
        $rule = 0;
        for (; $rule <= self::QUE; ++$rule)    // iterates over elements of query
            switch ($rule) {
                case 0 && $query[$i] == 'SELECT' :

                    $this->query['SELECT'] = $query[$inc()];
                    $inc();

                    break;

                case 1 :
                    if ($query[$i] != 'LIMIT')
                        continue;

                    $getLimit = function ($element) {       // checks if limit is integer
                        if (!ctype_digit($element))
                            throw new Exception('SQL LIMIT prikaz musi byt celo-ciselny');
                        return intval($element);
                    };

                    $this->query['LIMIT'] = $getLimit ($query[$inc()]);
                    $inc();

                    break;

                case 2 && $query[$i] == 'FROM' :

                    $element = $query[$inc()];

                    if (($element == 'ORDER') ||
                        ($element == 'WHERE'))
                        return;

                    $this->query['FROM'] = $element;

                    $inc();
                    break;


                case 3 :
                        if ($query[$i] != 'WHERE')
                            continue;
                    $inc();

                    $brackets = 0;

                    $this->query['WHERE'][] = $this->conditionHandle
                    ($inc, $query, $i, $brackets, 'AND', $affection);

                    break;

                default :

                    throw new Exception('Neznamy query prikaz/pozice prikazu');

                    break;
            }

        for (;$i < $count;)                             // iterates over elements of query
            switch ($query[$i]) {

                case 'AND' &&  $rule == 3 :

                    $inc();

                    $brackets = 0;

                     array_unshift($this->query['WHERE'] , $this->conditionHandle
                    ($inc, $query, $i, $brackets, 'AND', $affection));

                    break;

                case 'OR' && $rule == 3 :

                    $inc();

                    $brackets = 0;

                    $this->query['WHERE'][] = $this->conditionHandle
                    ($inc, $query, $i, $brackets, 'OR', $affection);


                    break;

                case 'ORDER' :
                        if ($query[$inc()] != 'BY')
                            throw new Exception('SQL query error, ocekavany BY, misto toho ' . $query[$i]);

                        $this->query['ORDER']['ELEMENT'] = $query[$inc()];       // order by ,int var expected

                        $this->query['ORDER']['DIRECTION'] = $query[$inc()];     // desc / asc

                        $inc();

                        break;
                    break;

                default :
                    throw new Exception('nezname query prikaz');
                    break;
            }

        if ($i != $count)                               // asks for end
            throw new Exception('SQL query error, ocekavany konec query po ' . $query[$i]);
    }

    private function prepareQuery ($query) {        // parse input query

        $query = explode(" ", trim($query));        // parse input query

        $query = array_filter($query, 'strlen');    // remove empty fields

        $field = array();

        foreach ($query as &$item) {                // separates left brackets (
            if (($position = strpos($item, '(')) !== false) {
                while (($position = strpos($item, '(')) !== false) {

                    $tmp = substr($item, 0, $position);

                    if (isset($tmp[0]))
                        $field[] = $tmp;

                    $field[] = '(';

                    $item = substr($item, $position + 1);
                }
                $tmp = substr($item, 0, $position);

                if (isset($tmp[0]))
                    $field[] = $tmp;

                $field[] = substr($item, $position);
            }
             else
                $field[] = $item;
        }

        $query = $field;
        $field = array();

        foreach ($query as &$item) {                 // separates right brackets )
            if (($position = strpos($item, ')')) !== false) {
                while (($position = strpos($item, ')')) !== false) {

                    $tmp = substr($item, 0, $position);

                    if (isset($tmp[0]))
                        $field[] = $tmp;

                    $item = substr($item, $position + 1);
                    $field[] = ')';

                }

                $tmp = substr($item, $position + 1);
                if (isset($tmp[0]))
                    $field[] = $tmp;
            } else
                $field[] = $item;
        }

        return $field;
    }

    private function conditionHandle ($inc, $query, &$i, &$brackets, $defaultAffection, $affection) {

        if ($query[$i][self::FIRST] === '(') {   // handles brackets
            $inc();

            $brackets++;

            $condition = $this->conditionHandle  // recursion
            ($inc, $query, $i, $brackets, $defaultAffection, $affection);

            if (!$brackets) return $condition;

            while ($query[$i] == ')') {
                --$brackets;
                $inc();
                if (!$brackets) return $condition;
            }

            $tmp = $condition;
            $condition = NULL;
            $condition[] = $tmp;

            $tmp = $this->conditionHandle  // recursion
            ($inc, $query, $i, $brackets, NULL, $affection);

            if ($tmp['AFFECTION'] == 'AND')
                array_unshift($condition, $tmp);                      // push condition
            else
                $condition[] = $tmp;

            return $condition;
        }

        $negation = false;

        while ($query[$i] == 'NOT') {            // handles negation
            $negation = !$negation;
            $inc();
        }

        if (empty($defaultAffection))
            $defaultAffection = $affection($query[$i]);

        $condition = $this->getCondition($inc, $query, $i, $brackets);  // gets condition

        $condition['NOT'] = $negation;                                  // sets negation

        $condition['AFFECTION'] = $defaultAffection;                    // sets affection

        $defaultAffection = NULL;

        return $condition;
    }

    private function getCondition ($inc, $query, &$i, &$brackets) {

        $result = array_reverse(explode ('.', $query[$i]));  // sets first operand + attributes

        if (count($result) > 2)
            throw new Exception('Neplatny pocet operandu v podmince');

        $inc();

        if ($query[$i] == 'CONTAINS') {
            $inc();
            if (($query[$i][0] != "\"") && ($query[$i][strlen($query[$i])-1] != "\""))
                throw new Exception('CONTAINS Element musi byt retezec');
            $result['CONTAINS'] = trim($query[$i], "\"");

            $inc();

            return $result;
        }

        $operand = NULL;

        switch ($query[$i]) {
            case '<' :
            case '>' :
                $operand = $query[$i];
                break;
            case '=' :
                $operand = $query[$i] . '=';
                break;
            default :
                throw new Exception ('SQL neznamy operand ' .  $query[$i]);
        }

        $inc();

        $value = NULL;

        if (($query[$i][self::FIRST] != "\"") && ($query[$i][strlen($query[$i])-1] != "\""))
            $value = trim($query[$i], "\"");
        else
            $value = (ctype_digit($query[$i])) ? intval($query[$i]) : $query[$i];

        $result[$operand] = $value;

        $inc();

        if ($query[$i] == ')') {
            --$brackets;
            $inc();
        }

        return $result;
    }

    // public setters functions

    public function setParsedXml ($parsedXml) {       // sets XML parser
        $this->input = $parsedXml;
    }

    // public getters functions

    public function getOutputXML () {
        return $this->output;
    }
}