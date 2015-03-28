<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CheckOutput {
    /**
     *
     * @var CheckInput 
     */
    private $checkInput;
    
    /**
     *
     * @var string 
     */
    private $name;
    
    /**
     *
     * @var bool 
     */
    private $valid = false;
    
    /**
     *
     * @var string 
     */
    private $message;
    
    /**
     *
     * @var string 
     */
    private $image;
    
    /**
     * 
     * @param CheckInput $checkInput
     * @param string $name
     * @param bool $valid
     * @param string $message
     * @param string $image
     */
    public function __construct(CheckInput $checkInput, $name, $valid, $message, $image) {
        $this->checkInput = $checkInput;
        $this->name = (string) $name;
        $this->valid = (bool) $valid;
        $this->message = (string) $message;
        $this->image = (string) $image;
    }
    
    /**
     * 
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * 
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }
    
    /**
     * 
     * @return string
     */
    public function getImage() {
        return $this->image;
    }
    
    /**
     * 
     * @return bool
     */
    public function isValid() {
        return $this->valid;
    }
}

class CheckInput {
    /**
     *
     * @var Observation[] 
     */
    private $observations = array();
    
    /**
     *
     * @var DateTime[] 
     */
    private $time_diffs = array();
    
    /**
     *
     * @var string 
     */
    private $eval;


    /**
     * 
     * @param Observation $obsA
     * @param Observation $obsB
     * @param Observation $obsC
     * @param DateTime $tAB
     * @param DateTime $tBC
     * @param string $evaluation
     */
    public function __construct(Observation $obsA, Observation $obsB, Observation $obsC, DateTime $tAB, DateTime $tBC, $evaluation) {
        $this->observations['A'] = $obsA;
        $this->observations['B'] = $obsB;
        $this->observations['C'] = $obsC;
        $this->time_diffs['AB'] = $tAB;
        $this->time_diffs['BC'] = $tBC;
        $this->eval = $evaluation;
    }
    
    /**
     * 
     * @param string $i
     * @return Observation
     */
    public function getObservation($i) {
        return $this->observations[$i];
    }
    
    /**
     * 
     * @param string $i
     * @return DateTime
     */
    public function getTimeDifference($i) {
        return $this->time_diffs[$i];
    }
    
    /**
     * 
     * @return sting
     */
    public function getEvaluation() {
        return $this->eval;
    }
}

class Observation {
    /**
     *
     * @var Position 
     */
    private $position;
    
    /**
     *
     * @var DateTime 
     */
    private $t1;
    
    /**
     *
     * @var DateTime 
     */
    private $t2;
    
    /**
     *
     * @var DateTime 
     */
    private $tAvg;
    
    /**
     *
     * @var float 
     */
    private $az;
    
    /**
     *
     * @var string 
     */
    private $note;
    
    /**
     * 
     * @param Position $position
     * @param DateTime $t1
     * @param DateTime $t2
     * @param DateTime $tAvg
     * @param float $azimuth
     * @param string $note
     */
    public function __construct(Position $position, DateTime $t1, DateTime $t2, DateTime $tAvg, $azimuth, $note) {
        $this->position = $position;
        $this->t1 = $t1;
        $this->t2 = $t2;
        $this->tAvg = $tAvg;
        $this->az = $azimuth;
        $this->note = $note;
    }
    
    /**
     * 
     * @return Position
     */
    public function getPosition() {
        return $this->position;
    }
    
    /**
     * 
     * @return DateTime
     */
    public function getTime1() {
        return $this->t1;
    }
    
    /**
     * 
     * @return DateTime
     */
    public function getTime2() {
        return $this->t2;
    }
    
    /**
     * 
     * @return DateTime
     */
    public function getAvgTime() {
        return $this->tAvg;
    }
    
    /**
     * 
     * @return float
     */
    public function getAzimuth() {
        return $this->az;
    }
    
    /**
     * 
     * @return string
     */
    public function getNote() {
        return $this->note;
    }
}

class Position {
    /**
     *
     * @var float 
     */
    private $lat;
    
    /**
     *
     * @var float 
     */
    private $lon;
    
    /**
     *
     * @var string 
     */
    private $street;
    
    /**
     *
     * @var string 
     */
    private $town;
    
    /**
     *
     * @var string 
     */
    private $zip;
    
    /**
     * 
     * @param float $lat
     * @param float $lon
     * @param string $street
     * @param string $town
     * @param string $zip
     */
    public function __construct($lat, $lon, $street, $town, $zip) {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->street = $street;
        $this->town = $town;
        $this->zip = $zip;
    }
    
    /**
     * 
     * @return float
     */
    public function getLatitude() {
        return $this->lat;
    }
    
    /**
     * 
     * @return float
     */
    public function getLongitude() {
        return $this->lon;
    }
}

class CSVPresenter {
    /**
     *
     * @var array 
     */
    private $raw_data;
    
    /**
     *
     * @var array 
     */
    private $outputData = array();
    
    /**
     * 
     * @param array $data
     */
    public function Render($data) {
        $this->raw_data = $data;
        $this->prepareData();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');
        
        $output = fopen('php://output', 'w');
        
        foreach ($this->outputData as $row) {
            fputcsv($output, $row);
        }        
    }
    
    private function prepareData() {
        //head
        $this->outputData[0][] = "Pupil ID";
        $this->outputData[0][] = "Obs. A Note";
        $this->outputData[0][] = "Obs. B Note";
        $this->outputData[0][] = "Obs. C Note";
        $this->outputData[0][] = "Evaluation";
        
        $cols = array();
        $temp = array_values($this->raw_data)[0]['output'];
        foreach ($temp as $column => $output) {
            $cols[] = $column;
            $this->outputData[0][] = $column;
        }
        
        //body
        foreach ($this->raw_data as $pupID => $row) {
            $outputRow = array();
            
            $outputRow[] = $pupID;
            $outputRow[] = $row['input']->getObservation('A')->getNote();
            $outputRow[] = $row['input']->getObservation('B')->getNote();
            $outputRow[] = $row['input']->getObservation('C')->getNote();
            $outputRow[] = $row['input']->getEvaluation();
            
            foreach ($cols as $column) {
                $outputRow[] = $row['output'][$column]->getMessage();
            }
            
            $this->outputData[] = $outputRow;
        }
    }
}

class ObservcheckModel {
    
    /**
     *
     * @var array 
     */
    private $data = array();
    
    /**
     *
     * @var type mysqli
     */
    private $connection;
    
    /**
     *
     * @var type ICheckable[]
     */
    private $checkers = array();
    
    /**
     * 
     * @param mysqli $connection
     */
    public function __construct(mysqli $connection) {
        $this->connection = $connection;
        
        $this->loadData();
    }
    
    /**
     * 
     * @return array
     */
    public function Process() {
        $this->checkData();
        return $this->data;
    }

    private function loadData() {
        $this->connection->query("SET names utf8");
        $res = $this->connection->query("SELECT * FROM AO_home_observ");
        while($row = $res->fetch_assoc()) {
            for($i=1; $i<=3; $i++){
                $dt = new DateTime($row["obs".$i."_dt"]);
                $day = $dt->format('j');
                $month = $dt->format('n');
                $year = $dt->format('Y');
                
                $t1 = new DateTime($row["obs".$i."_t1"]);
                $t2 = new DateTime($row["obs".$i."_t2"]);
                $tAvg = new DateTime($row["obs".$i."_tav"]);
                
                $t1->setDate($year, $month, $day);
                $t2->setDate($year, $month, $day);
                $tAvg->setDate($year, $month, $day);
                $position = new Position($row["obs".$i."_gps_lat"], $row["obs".$i."_gps_lon"], $row["obs".$i."_a_str"], $row["obs".$i."_a_place"], $row["obs".$i."_a_zip"]);
                $observations[$i] = new Observation($position, $t1, $t2, $tAvg, $row["obs".$i."_azim"], $row["obs".$i."_a_note"]);
            }
            
            $tAB = new DateTime($row["dtime_ab"]);
            $tBC = new DateTime($row["dtime_bc"]);
            $input = new CheckInput($observations[1], $observations[2], $observations[3], $tAB, $tBC, $row["obs_eval"]);
            
            $this->data[$row['observ_pupil_ID']]['input'] = $input;
        }
    }
    
    private function checkData() {
        foreach ($this->data as &$pupil) {
            $this->runCheckers($pupil['input'], $pupil['output']);
        }
    }

    private function runCheckers(CheckInput $input, &$output) {
        foreach ($this->checkers as $checker) {
            $checker->Check($input, $output);
        }
    }

    /**
    * 
    * @param ICheckable $checker
    */
    public function RegisterChecker(ICheckable $checker) {
        $this->checkers[] = $checker; //TODO array -> set (deduplication)
    }
}

interface ICheckable {
    /**
     * 
     * @param CheckInput $input
     * @param CheckOutput[] $outputObject
     */
    public function Check(CheckInput $input, &$outputObject);    
}

class ComputationChecker implements ICheckable {
    
    /**
     *
     * @var CheckInput 
     */
    private $input;
    
    /**
     *
     * @var CheckOutput[] 
     */
    private $outputObject;
    
    /**
     * 
     * @param CheckInput $input
     * @param CheckOutput[] $outputObject
     */
    public function Check(CheckInput $input, &$outputObject) {
        $this->input = $input;
        $this->outputObject = &$outputObject;
        
        $this->checkAverage('A');
        $this->checkAverage('B');
        $this->checkAverage('C');
        
        $this->checkDifference('AB');
        $this->checkDifference('BC');
    }
    
    /**
     * 
     * @param string $i
     */
    private function checkAverage($i) {
        $name = "Výpočet průměru $i";
        
        $observation = $this->input->getObservation($i);
        $t1 = $observation->getTime1()->getTimestamp();
        $t2 = $observation->getTime2()->getTimestamp();
        $tAvg = $observation->getAvgTime()->getTimestamp();
        
        if(abs(($t2+$t1)/2.0 - $tAvg) <= 1) {
            $this->outputObject[$name] = new CheckOutput($this->input, $name, true, "Výpočet průměru pozorování $i je správně.", null);
        }
        else {
            $tPoz = date("H:i:s", $tAvg);
            $tNas = date("H:i:s", ($t2+$t1)/2);
            $this->outputObject[$name] = new CheckOutput($this->input, $name, false, "Výpočet průměru pozorování $i je špatně. \n Hodnota pozorovatele: $tPoz \n Naše hodnota: $tNas", null);
        }
    }
    
    /**
     * 
     * @param string $i
     * @return void
     */
    private function checkDifference($i) {
        $name = "Výpočet rozdílu $i";
        
        $tA = $this->input->getObservation($i[0])->getAvgTime();
        $tB = $this->input->getObservation($i[1])->getAvgTime();
        $tAB = $this->input->getTimeDifference($i);
        
        $seconds_their = ($tAB->format("H")*60 + $tAB->format("i"))*60 + $tAB->format("s");
        
        $diff = $tA->diff($tB);
        if($diff->days == 0) {
            $this->outputObject[$name] = new CheckOutput($this->input, $name, false, "Výpočet rozdílu pozorování $i je špatně. Nulový odstup mezi dny.", null);
            return;
        }
        $seconds_our = (($diff->h*60 + $diff->i)*60 + $diff->s)/$diff->days;
        
        if($seconds_our == $seconds_their) {
            $this->outputObject[$name] = new CheckOutput($this->input, $name, true, "Výpočet rozdílu pozorování $i je správně.", null);
        }
        else {
            $tNas = date("H:i:s", $seconds_our);
            $tPoz = $tAB->format("H:i:s");
            $this->outputObject[$name] = new CheckOutput($this->input, $name, false, "Výpočet rozdílu pozorování $i je špatně. \n Hodnota pozorovatele: $tPoz \n Naše hodnota: $tNas", null);
        }
    }
}

class SunriseChecker implements ICheckable {
    
    /**
     *
     * @var CheckInput 
     */
    private $input;
    
    /**
     *
     * @var CheckOutput[] 
     */
    private $outputObject;
    
    /**
     * 
     * @param CheckInput $input
     * @param CheckOutput[] $outputObject
     */
    public function Check(CheckInput $input, &$outputObject) {
        $this->input = $input;
        $this->outputObject = &$outputObject;
        

        $this->checkSunrise('A');
        $this->checkSunrise('B');
        $this->checkSunrise('C');
    }
    
    /**
     * 
     * @param string $i
     * @param string $riseLabel
     * @param int $obsAvg timestamp of observed sunrise/sunset
     * @param int $realAvg timestamp of computed sunrise/sunset
     */
    private function checkSunriseTime($i, $riseLabel, $obsAvg, $realAvg) {
        $name = "Čas východu/západu $i";
        
        if(abs($obsAvg - $realAvg) < 120) {
            $this->outputObject[$name] = new CheckOutput($this->input, $name, true, "Čas $riseLabel $i je správně.", null);
        }
        else {
            $tNas = date("H:i:s", $realAvg);
            $tPoz = date("H:i:s", $obsAvg);
            $this->outputObject[$name] = new CheckOutput($this->input, $name, false, "Čas $riseLabel $i je špatně. \n Hodnota pozorovatele: $tPoz \n Naše hodnota: $tNas", null);
        }
    }
    
    /**
     * 
     * @param string $i
     * @param string $riseLabel
     * @param int $obsDiff timestamp of duration observed sunrise/sunset
     * @param int $realDiff timestamp of duration of computed sunrise/sunset
     */
    private function checkSunriseDuration($i, $riseLabel, $obsDiff, $realDiff) {
        $name = "Trvání východu/západu $i";
        
        if(abs($obsDiff - $realDiff) < 30) {
            $this->outputObject[$name] = new CheckOutput($this->input, $name, true, "Trvání $riseLabel $i je správně.", null);
        }
        else {
            $tNas = date("H:i:s", $realDiff);
            $tPoz = date("H:i:s", $obsDiff);
            $this->outputObject[$name] = new CheckOutput($this->input, $name, false, "Trvání $riseLabel $i je špatně. \n Hodnota pozorovatele: $tPoz \n Naše hodnota: $tNas", null);
        }
    }
    
    /**
     * 
     * @param string $i
     * @param string $riseLabel
     * @param float $obsAz observed azimuth in degrees
     * @param float $realAz computed azimuth in degrees
     */
    private function checkSunriseAzimuth($i, $riseLabel, $obsAz, $realAz) {
        $name = "Azimut $i";
        
        if(abs($obsAz-$realAz) < 5){
            $this->outputObject[$name] = new CheckOutput($this->input, $name, true, "Azimut $riseLabel $i je správně.", null);
        }
        else {
            $this->outputObject[$name] = new CheckOutput($this->input, $name, false, "Azimut $riseLabel $i je špatně. \n Hodnota pozorovatele: ".round($obsAz)."° \n Naše hodnota: ".round($realAz)."°", null);
        }
    }

    /**
     * 
     * @param string $i observation identifier
     */
    private function checkSunrise($i) {
        $observation = $this->input->getObservation($i);
        $t1 = $observation->getTime1();
        $t2 = $observation->getTime2();
        
        $data_upper = $this->getSunriseData($t1, $observation->getPosition(), 0, true);
        $data_lower = $this->getSunriseData($t2, $observation->getPosition(), 0, false);
        
        $riseLabel = (($data_lower['rise'])?"východu":"západu");
        
        $obsAvg = 0.5*($t1->getTimestamp() + $t2->getTimestamp());
        $realAvg = 0.5*($data_lower['time']->getTimestamp() + $data_upper['time']->getTimestamp());
        $this->checkSunriseTime($i, $riseLabel, $obsAvg, $realAvg);
        
        $obsDiff = abs($t1->getTimestamp() - $t2->getTimestamp());
        $realDiff = abs($data_lower['time']->getTimestamp() - $data_upper['time']->getTimestamp());
        $this->checkSunriseDuration($i, $riseLabel, $obsDiff, $realDiff);
        
        $obsAz = $observation->getAzimuth();
        $realAz = 0.5*($data_lower['azimuth']+$data_upper['azimuth']);
        $this->checkSunriseAzimuth($i, $riseLabel, $obsAz, $realAz);
//        $lat = $observation->getPosition()->getLatitude();
//        $lon = $observation->getPosition()->getLongitude();
//        $real2 = date_sun_info($t1->getTimestamp(), $lat, $lon);
//        foreach ($real2 as $key => $val) {
//            echo "$key: " . date("H:i:s", $val) . "\n";
//        }
        
//        var_dump($this->getSunriseData($t1, $observation->getPosition(), 0, true));
//        var_dump($this->getSunriseData($t2, $observation->getPosition(), 0, false));
    }
    
    /**
     * 
     * @param DateTime $time Approximate time of sunrise/sunset
     * @param Position $position Position of observer
     * @param float $Ha Apparent altitude of Sun's limb in degrees
     * @param bool $upperLimb Upper or lower limb of Sun
     * @return array
     */
    private function getSunriseData(DateTime $time, Position $position, $Ha, $upperLimb) {
        $SD = 16.3/60;
        
        $R = 1/tan(($Ha+7.31/($Ha+4.4))/180*pi())/60;
        //$R = 42; //todo
        $Ho = $Ha - $R + (($upperLimb)?(-$SD):$SD);

        $timezone = 1; //CET timezone
        
        $rise = true; //specifies sunrise/sunset
        if($time->format('H') > 12) {
            $rise = false;
        }
        
        $lat = $position->getLatitude();
        $lon = $position->getLongitude();        
        $jd = $this->dateTimeToJD($time);
        
        $epsilon = $this->getObliquity($jd);
        $lambda = $this->getSunLambda($jd);
        $ra = $this->getSunRA($lambda, $epsilon);
        $dec = $this->getSunDec($lambda, $epsilon);
        $LHA = $this->getLocalHourAngle($dec, $Ho, $lat, $rise);
        $theta = $this->getRiseSiderealTime($ra, $LHA);

        $Az = $this->getAzimuth($LHA, $dec, $Ho, $lat);        
        $riseTime = $this->localSiderealToMean($jd, $theta, $lon, $timezone);

        $hour = floor($riseTime);
        $min = floor(($riseTime - $hour)*60.0);
        $sec = round((($riseTime - $hour)*60.0 - $min)*60.0);
        $riseDateTime = new DateTime($time->format('Y-m-d ').$hour.':'.$min.':'.$sec);
        
        return array(
            'azimuth' => $Az,
            'time' => $riseDateTime,
            'rise' => $rise
        );
    }
    
    /**
     * 
     * @param DateTime $date
     * @return float Julian Date
     */
    private function dateTimeToJD(DateTime $date) {
        return $this->unixToJD($date->getTimestamp());
    }
    
    /**
     * 
     * @param int $timestamp Unix timestamp
     * @return float Julian Date
     */
    private function unixToJD($timestamp) {
        return $timestamp / 86400.0 + 2440587.5;
    }
    
    /**
     * 
     * @param float $jd Julian Date
     * @return int Unix timestamp
     */
    private function JDToUnix($jd) {
        return ($jd - 2440587.5)*86400.0;
    }
    
    /**
     * 
     * @param float $jd Julian Date (approximately)
     * @param float $sidereal Local sidereal time in hours
     * @param float $longitude Observer's longitude in degrees
     * @param float $timezone
     * @return float Local mean sun time in hours
     */
    private function localSiderealToMean($jd, $sidereal, $longitude, $timezone) {
        $jd0 = round($jd) - 0.5;
        $T = ($jd0 - 2451545.0) / 36525.0;
        $S0 = 6.697374558 + $T*(2400.05133691 + $T*(0.000025862 - 0.0000000017*$T));
        $S0 = $S0 - 24.0*floor($S0 / 24.0);
        
        $time = ($sidereal - $S0 + $timezone - $longitude/15.0) / 1.0027379093;
        return $time - 24.0*floor($time / 24.0);
    }
    
    /**
     * 
     * @param float $jd Julian Date
     * @return float Sun's ecliptic longitude in degrees
     */
    private function getSunLambda($jd) {
        $n = $jd - 2451545.0;
        $L = 280.460 + 0.9856474*$n; //mean longitude
        $g = 357.528 + 0.9856003*$n; //mean anomally
         
        $L_corr = $L - 360.0*floor($L/360.0);
        $g_rad = $g/180.0*pi();
         
        return $L_corr + 1.915*sin($g_rad) + 0.020*sin(2*$g_rad); //in degrees
    }
    
    /**
     * 
     * @param float $jd Julian Date
     * @return float Obliquity of ecliptic in degrees
     */
    private function getObliquity($jd) {
        $n = $jd - 2451545.0;
        return 23.439 - 0.0000004*$n; //obliquity of ecliptic in degrees
    }
    
    /**
     * 
     * @param float $lambda Sun's ecliptic longitude in degrees
     * @param float $epsilon Obliquity of ecliptic in degrees
     * @return float Sun's declination in degrees
     */
    private function getSunDec($lambda, $epsilon) {
        return asin(sin($lambda/180.0*pi())*sin($epsilon/180.0*pi()))/pi()*180.0; //in degrees
    }
    
    /**
     * 
     * @param float $lambda Sun's ecliptic longitude in degrees
     * @param float $epsilon Obliquity of ecliptic in degrees
     * @return float Sun's right ascension in degrees
     */
    private function getSunRA($lambda, $epsilon) {
        $alpha0 = atan(tan($lambda/180.0*pi())*cos($epsilon/180.0*pi()))/pi()*180.0;
        return $alpha0 + 90.0*floor($lambda/90.0) - 90.0*floor($alpha0/90.0); //in degrees
    }
    
    /**
     * 
     * @param float $dec Sun's declination in degrees
     * @param float $h Sun's altitude in degrees
     * @param float $lat Observer's latitude in degrees
     * @param bool $rise Sunrise (true) or sunset (false)
     * @return float Local hour angle in degrees
     */
    private function getLocalHourAngle($dec, $h, $lat, $rise) {
        $lat_rad = $lat / 180.0*pi();
        $dec_rad = $dec / 180.0*pi();
        $t = acos(sin($h/180.0*pi())/(cos($dec_rad)*cos($lat_rad))-tan($lat_rad)*tan($dec_rad))*180.0/pi(); //in degrees
        
        if($rise) {
            $t = 360 - $t;
        }
        
        return $t;
    }
    
    /**
     * 
     * @param float $RA Sun's right ascension in degrees
     * @param float $t Local hour angle in degrees
     * @return float Local sidereal time in hours
     */
    private function getRiseSiderealTime($RA, $t) {
        $Theta = ($t + $RA)/15.0; //in hours
        return $Theta - 24.0*floor($Theta / 24.0);
    }
    
    /**
     * 
     * @param float $t Local hour angle in degrees
     * @param float $dec Sun's declination in degrees
     * @param float $h Sun's altitude in degrees
     * @param float $lat Observer's latitude in degrees
     * @return float Sun's azimuth in degrees
     */
    private function getAzimuth($t, $dec, $h, $lat) {
        $dec_rad = $dec / 180*pi();
        $t_rad = $t / 180*pi();
        $h_rad = $h / 180*pi();
        $lat_rad = $lat / 180*pi();
        
        $sinA = sin($t_rad)*cos($dec_rad)/cos($h_rad);
        $cosA = (cos($t_rad)*cos($dec_rad)*sin($lat_rad) - sin($dec_rad)*cos($lat_rad)) / cos($h_rad);
        
        $A0 = acos($cosA)/pi() * 180;
        if ($sinA > 0) {
            return $A0;
        }
        else {
            return 360 - $A0;
        }
    }
}

class PositionChecker implements ICheckable {
        
    /**
     *
     * @var CheckInput 
     */
    private $input;
    
    /**
     *
     * @var CheckOutput[] 
     */
    private $outputObject;
    
    /**
     * 
     * @param CheckInput $input
     * @param CheckOutput[] $outputObject
     */
    public function Check(CheckInput $input, &$outputObject) {
        $this->input = $input;
        $this->outputObject = &$outputObject;
        
        $this->checkPositionInvariance();
    }
    
    private function checkPositionInvariance() {
        $name = "Stálost pozorovacího místa";
        $allowedDistance = 200;
        
        $posA = $this->input->getObservation('A')->getPosition();
        $posB = $this->input->getObservation('B')->getPosition();
        $posC = $this->input->getObservation('C')->getPosition();
        
        $dist['AB'] = $this->getDistance($posA, $posB);
        $dist['BC'] = $this->getDistance($posC, $posB);
        $dist['AC'] = $this->getDistance($posA, $posC);
        
        $maxDist = max($dist);
        if($maxDist > $allowedDistance) {
            $this->outputObject[$name] = new CheckOutput($this->input, $name, false, "Vzdálenost min. 2 poz. míst je ".round($maxDist)." m.", null);
        }
        else {
            $this->outputObject[$name] = new CheckOutput($this->input, $name, true, "Vzdálenost poz. míst je v toleranci.", null);
        }
    }
    
    /**
     * 
     * @param Position $posA
     * @param Position $posB
     * @return float Distance in metres
     */
    private function getDistance(Position $posA, Position $posB) {
        $R = 6371000;
        
        $latA = $posA->getLatitude()/180*pi();
        $latB = $posB->getLatitude()/180*pi();
        $lonA = $posA->getLongitude()/180*pi();
        $lonB = $posB->getLongitude()/180*pi();
        
        return acos(sin($latA)*sin($latB) + cos($latA)*cos($latB)*cos($lonA-$lonB))*$R;
    }
}