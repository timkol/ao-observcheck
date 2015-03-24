<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CheckOutput {
    private $checkInput;
    private $name;
    private $valid = false;
    private $message;
    private $image;
    
    public function __construct(CheckInput $checkInput, $name, $valid, $message, $image) {
        $this->checkInput = $checkInput;
        $this->name = (string) $name;
        $this->valid = (bool) $valid;
        $this->message = (string) $message;
        $this->image = (string) $image;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getImage() {
        return $this->image;
    }
    
    public function isValid() {
        return $this->valid;
    }
}

class CheckInput {
    private $observations = array();
    private $time_diffs = array();
    
    public function __construct(Observation $obsA, Observation $obsB, Observation $obsC, DateTime $tAB, DateTime $tBC) {
        $this->observations['A'] = $obsA;
        $this->observations['B'] = $obsB;
        $this->observations['C'] = $obsC;
        $this->time_diffs['AB'] = $tAB;
        $this->time_diffs['BC'] = $tBC;
    }
    
    public function getObservation($i) {
        return $this->observations[$i];
    }
    
    public function getTimeDifference($i) {
        return $this->time_diffs[$i];
    }
}

class Observation {
    private $position;
//    private $date; //not neccessary?
    private $t1;
    private $t2;
    private $tAvg;
    private $az;
    
    public function __construct(Position $position, DateTime $t1, DateTime $t2, DateTime $tAvg, $azimuth) {
        $this->position = $position;
        $this->t1 = $t1;
        $this->t2 = $t2;
        $this->tAvg = $tAvg;
        $this->az = $azimuth;
    }
    
    public function getPosition() {
        return $this->position;
    }
    
    public function getTime1() {
        return $this->t1;
    }
    
    public function getTime2() {
        return $this->t2;
    }
    
    public function getAvgTime() {
        return $this->tAvg;
    }
    
    public function getAzimuth() {
        return $this->az;
    }
}

class Position {
    private $lat;
    private $lon;
    
    private $street;
    private $town;
    private $zip;
    
    public function __construct($lat, $lon, $street, $town, $zip) {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->street = $street;
        $this->town = $town;
        $this->zip = $zip;
    }
    
    public function getLatitude() {
        return $this->lat;
    }
    
    public function getLongitude() {
        return $this->lon;
    }
}

class ObservcheckModel {
    private $data = array();
    private $connection;
    private $checkers = array();
    
    public function __construct($connection) {
        $this->connection = $connection;
        
        $this->loadData();
    }
    
    public function Process() {
        $this->checkData();
        //var_dump($this->data);
    }

    private function loadData() {
        $res = $this->connection->query("SELECT * FROM AO_home_observ limit 1");
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
                $observations[$i] = new Observation($position, $t1, $t2, $tAvg, $row["obs".$i."_azim"]);
            }
            
            $tAB = new DateTime($row["dtime_ab"]);
            $tBC = new DateTime($row["dtime_bc"]);
            $input = new CheckInput($observations[1], $observations[2], $observations[3], $tAB, $tBC);
            
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


    public function RegisterChecker(ICheckable $checker) {
        $this->checkers[] = $checker; //TODO array -> set (deduplication)
    }
}

interface ICheckable {
    public function Check(CheckInput $input, &$outputObject);    
}

class ComputationChecker implements ICheckable {
    private $input;
    private $outputObject;
    
    public function Check(CheckInput $input, &$outputObject) {
        $this->input = $input;
        $this->outputObject = &$outputObject;
        
        $this->checkAverage('A');
        $this->checkAverage('B');
        $this->checkAverage('C');
        
        $this->checkDifference('AB');
        $this->checkDifference('BC');
    }
    
    private function checkAverage($i) {
        $name = "Výpočet průměru $i";
        
        $observation = $this->input->getObservation($i);
        $t1 = $observation->getTime1()->getTimestamp();
        $t2 = $observation->getTime2()->getTimestamp();
        $tAvg = $observation->getAvgTime()->getTimestamp();
        
        if(abs(($t2+$t1)/2.0 - $tAvg) <= 1) {
            $this->outputObject[] = new CheckOutput($this->input, $name, true, "Výpočet průměru pozorování $i je správně.", null);
        }
        else {
            $tPoz = date("H:i:s", $tAvg);
            $tNas = date("H:i:s", ($t2+$t1)/2);
            $this->outputObject[] = new CheckOutput($this->input, $name, false, "Výpočet průměru pozorování $i je špatně. \n Hodnota pozorovatele: $tPoz \n Naše hodnota: $tNas", null);
        }
    }
    
    private function checkDifference($i) {
        $name = "Výpočet rozdílu $i";
        
        $tA = $this->input->getObservation($i[0])->getAvgTime();
        $tB = $this->input->getObservation($i[1])->getAvgTime();
        $tAB = $this->input->getTimeDifference($i);
        
        $seconds_their = ($tAB->format("H")*60 + $tAB->format("i"))*60 + $tAB->format("s");
        
        $diff = $tA->diff($tB);
        $seconds_our = (($diff->h*60 + $diff->i)*60 + $diff->s)/$diff->days;
        
        if($seconds_our == $seconds_their) {
            $this->outputObject[] = new CheckOutput($this->input, $name, true, "Výpočet rozdílu pozorování $i je správně.", null);
        }
        else {
            $tNas = date("H:i:s", $seconds_our);
            $tPoz = $tAB->format("H:i:s");
            $this->outputObject[] = new CheckOutput($this->input, $name, false, "Výpočet rozdílu pozorování $i je špatně. \n Hodnota pozorovatele: $tPoz \n Naše hodnota: $tNas", null);
        }
    }
}

class SunriseChecker implements ICheckable {
    private $input;
    private $outputObject;
    
    public function Check(CheckInput $input, &$outputObject) {
        $this->input = $input;
        $this->outputObject = $outputObject;
        
        $this->checkSunriseTime('A');
    }
    
    private function checkSunriseTime($i) {
        $observation = $this->input->getObservation($i);
        $t1 = $observation->getTime1();
        $t2 = $observation->getTime2();
        
        $lat = $observation->getPosition()->getLatitude();
        $lon = $observation->getPosition()->getLongitude();

        $real2 = date_sun_info($t1->getTimestamp(), $lat, $lon);
        foreach ($real2 as $key => $val) {
            echo "$key: " . date("H:i:s", $val) . "\n";
        }
        
        var_dump($this->getSunriseData($t1, $observation->getPosition(), 0, true));
        var_dump($this->getSunriseData($t1, $observation->getPosition(), 0, false));
    }
    
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
    
    private function dateTimeToJD(DateTime $date) {
        return $this->unixToJD($date->getTimestamp());
    }
    
    private function unixToJD($timestamp) {
        return $timestamp / 86400.0 + 2440587.5;
    }
    
    private function JDToUnix($jd) {
        return ($jd - 2440587.5)*86400.0;
    }
    
    private function localSiderealToMean($jd, $sidereal, $longitude, $timezone) {
        $jd0 = round($jd) - 0.5;
        $T = ($jd0 - 2451545.0) / 36525.0;
        $S0 = 6.697374558 + $T*(2400.05133691 + $T*(0.000025862 - 0.0000000017*$T));
        $S0 = $S0 - 24.0*floor($S0 / 24.0);
        
        $time = ($sidereal - $S0 + $timezone - $longitude/15.0) / 1.0027379093;
        return $time - 24.0*floor($time / 24.0);
    }
    
    private function getSunLambda($jd) {
        $n = $jd - 2451545.0;
        $L = 280.460 + 0.9856474*$n; //mean longitude
        $g = 357.528 + 0.9856003*$n; //mean anomally
         
        $L_corr = $L - 360.0*floor($L/360.0);
        $g_rad = $g/180.0*pi();
         
        return $L_corr + 1.915*sin($g_rad) + 0.020*sin(2*$g_rad); //in degrees
    }
    
    private function getObliquity($jd) {
        $n = $jd - 2451545.0;
        return 23.439 - 0.0000004*$n; //obliquity of ecliptic in degrees
    }
    
    private function getSunDec($lambda, $epsilon) {
        return asin(sin($lambda/180.0*pi())*sin($epsilon/180.0*pi()))/pi()*180.0; //in degrees
    }
    
    private function getSunRA($lambda, $epsilon) {
        $alpha0 = atan(tan($lambda/180.0*pi())*cos($epsilon/180.0*pi()))/pi()*180.0;
        return $alpha0 + 90.0*floor($lambda/90.0) - 90.0*floor($alpha0/90.0); //in degrees
    }
    
    private function getLocalHourAngle($dec, $h, $lat, $rise) {
        $lat_rad = $lat / 180.0*pi();
        $dec_rad = $dec / 180.0*pi();
        $t = acos(sin($h/180.0*pi())/(cos($dec_rad)*cos($lat_rad))-tan($lat_rad)*tan($dec_rad))*180.0/pi(); //in degrees
        
        if($rise) {
            $t = 360 - $t;
        }
        
        return $t;
    }
    
    private function getRiseSiderealTime($RA, $t) {
        $Theta = ($t + $RA)/15.0; //in hours
        return $Theta - 24.0*floor($Theta / 24.0);
    }
    
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