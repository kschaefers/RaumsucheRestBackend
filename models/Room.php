<?php

class Room
{
    public $name;
    public $day;
    public $hour;
    public $size;
    public $computer;
    public $beamer;
    public $pool;
    public $looseSeating;
    public $video;

    public function __construct($name, $day, $hour, $size, $computer, $beamer, $pool, $looseSeating, $video){
        $this->name = $name;
        $this->day = intval($day);
        $this->hour = intval($hour);
        $this->size = intval($size);
        $this->computer = (boolean) $computer;
        $this->beamer = (boolean) $beamer;
        $this->pool = (boolean) $pool;
        $this->looseSeating = (boolean) $looseSeating;
        $this->video = (boolean) $video;
    }

    static function search($building, $day, $hour, $size, $computer, $beamer, $pool, $looseSeating, $video){

        if(empty($hour)){
            $hour = "1,2,3,4,5,6";
        }
        if(empty($day)){
            $day = intval(date("w"));
            if($day>5){
                $day = 1;
            }
        }
        $pdo = db::getPDO();
        if(!empty($building)){
            $buildings = explode(',',$building);
            foreach($buildings as $key => $building){
                $buildings[$key] = 'Name LIKE "'.$building.'%"';
            }
            $buildingString = implode(' OR ',$buildings);
        }

        $sql = sprintf('SELECT * FROM rooms WHERE Day = :day AND FIND_IN_SET(HOUR, :hour) %s %s %s %s %s %s %s',
            !empty($size)   ? 'AND size >= :size'   : null,
            !empty($computer) ? 'AND computer = :computer' : null,
            !empty($beamer) ? 'AND beamer = :beamer' : null,
            !empty($pool) ? 'AND pool = :pool' : null,
            !empty($looseSeating) ? 'AND looseSeating = :looseSeating' : null,
            !empty($video) ? 'AND video = :video' : null,
            !empty($buildingString)? ' AND ( '.$buildingString.' )' : null); //TODO: HACK!!!!

        $st = $pdo->prepare($sql);
        $st->bindParam(':day', $day);
        $st->bindParam(':hour', $hour);
        if (!empty($size)) {
            $st->bindParam(':size', $size);
        }
        if (!empty($computer)) {
            $st->bindParam(':computer', $computer);
        }
        if (!empty($beamer)) {
            $st->bindParam(':beamer', $beamer);
        }
        if (!empty($pool)) {
            $st->bindParam(':pool', $pool);
        }
        if (!empty($looseSeating)) {
            $st->bindParam(':looseSeating', $looseSeating);
        }
        if (!empty($video)) {
            $st->bindParam(':video', $video);
        }
        $st->execute();
        $result = $st->fetchAll();
        $rooms = array();
        foreach ($result as $roomArray) {
            if(array_key_exists($roomArray['Name'],$rooms)){
                $rooms[$roomArray['Name']]->hour .= ",".$roomArray['Hour'];
            }else{
                $rooms[$roomArray['Name']] = new Room($roomArray['Name'], $roomArray['Day'], $roomArray['Hour'], $roomArray['Size'], $roomArray['Computer'], $roomArray['Beamer'], $roomArray['Pool'], $roomArray['LooseSeating'], $roomArray['Video']);
            }
        }

        return array_values($rooms);
    }
}