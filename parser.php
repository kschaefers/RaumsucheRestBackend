<?php
require 'vendor/phpSimpleDom/simple_html_dom.php';
require 'db.php';

class parser
{
    private $url = "http://jonathan.sv.hs-mannheim.de/~c.reiser/";

    public function parse()
    {
        $pdo = db::getPDO();
        $pdo->beginTransaction();
        $pdo->exec("TRUNCATE TABLE rooms");
        $html = file_get_html($this->url);

        $trArray = $html->find('table[bgcolor="#FFFFFF"] tr');

        foreach ($trArray as $trKey => $tr) {
            //skip header row
            if($trKey == 0){
                continue;
            }

            $tdArray = $tr->find('td');
            foreach($tdArray as $tdKey => $td){
                //skip hour column
                if($tdKey == 0){
                    continue;
                }

                $aArray = $td->find('a');
                foreach($aArray as $a){
                    if(strpos($a->plaintext,"[") === false) {
                        $roomArray = explode('&nbsp;', $a->plaintext);
                        $options = substr($roomArray[2], 0, strlen($roomArray[2]) - 1);
                        $st = $pdo->prepare(
                            "INSERT INTO rooms SET
                        Name = :name,
                        Size = :size,
                        Day = :day,
                        Hour = :hour,
                        Computer = :computer,
                        Beamer = :beamer,
                        Pool = :pool,
                        LooseSeating = :looseSeating,
                        Video = :video"
                        );
                        $st->execute(array(
                            ':name' => $roomArray[0],
                            ':size' => substr($roomArray[1], 1, strlen($roomArray[1]) - 2),
                            ':day' => $tdKey,
                            ':hour' => $trKey,
                            ':computer' => ((strpos($options, 'C') !== false) ? 1 : 0),
                            ':beamer' => ((strpos($options, 'B') !== false) ? 1 : 0),
                            ':pool' => ((strpos($options, 'P') !== false) ? 1 : 0),
                            ':looseSeating' => ((strpos($options, 'L') !== false) ? 1 : 0),
                            ':video' => ((strpos($options, 'V') !== false) ? 1 : 0),
                        ));
                    }
                }
            }
        }
        $pdo->commit();
    }
}

$parser = new Parser();
$parser->parse();