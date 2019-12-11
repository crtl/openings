<?php

namespace Crtl\Openings;

use Crtl\Openings\Exceptions\InvalidFormatException;

/**
 *
 * @author Marvin Petker <marvin.petker@borntocreate.de>
 */
class OpeningsManager {
    
    protected $openings;
    protected $openingExceptions;
    
    const OPENING_DATE_FORMAT = "D";
    const EXCEPTION_DATE_FORMAT = "Y/m/d";
    const TIME_FORMAT = "H:i";
    
    /**
     * 
     * @param array $openings
     * @param array $exceptions
     */
    public function __construct(Array $openings = [], Array $exceptions = []) {
        $this->setOpenings($openings);
        $this->setOpeningExceptions($exceptions);
    }
    
    
    /**
     * Checks if is opened at current time
     * @param \DateTime $date
     * @return boolean
     */
    public function isOpen(\DateTime $date = null) {
        
        if (!$date) {
            $date = new \DateTime();
        }
        
        $dateStr = $date->format(self::EXCEPTION_DATE_FORMAT);
        $dayStr = $date->format(self::OPENING_DATE_FORMAT);
        $timeStr = $date->format(self::TIME_FORMAT);

        $exceptions = $this->getOpeningException($dateStr);
        
        
        if ($exceptions !== null) {
            $openings = $exceptions;
        }
        else {
            $openings = $this->getOpening($dayStr);
        }


        foreach ($openings as $opening) {
            if ($timeStr >= $opening["from"] && $timeStr <= $opening["to"]) {
                return true;
            }
        }
        
        return false;
    }
    
    
    /**
     * 
     * @param array $openings
     */
    public function setOpenings(Array $openings) {

        $this->openings = $this->formatOpenings($openings, self::OPENING_DATE_FORMAT, [
            "Mon" => [], 
            "Tue" => [], 
            "Wed" => [],
            "Thu" => [],
            "Fri" => [], 
            "Sat" => [], 
            "Sun" => []
        ]);
        
    }
    
    
    /**
     * 
     * @param array $exceptions
     */
    public function setOpeningExceptions(Array $exceptions = []) {
        $this->openingExceptions = $this->formatOpenings($exceptions, self::EXCEPTION_DATE_FORMAT);
    }
    
    
    /**
     * 
     * @return array
     */
    public function getOpenings() {
        return $this->openings;
    }
    
    
    /**
     * 
     * @param string $day
     * @return array
     */
    public function getOpening($day) {
        return $this->openings[$day];
    }
    
    
    /**
     * 
     * @return array
     */
    public function getOpeningExceptions() {
        return $this->openingExceptions;
    }
    
    
    /**
     * Returns the exception for given date or null if it doesnt exists
     * @param string $date
     * @return array | null
     */
    public function getOpeningException($date) {
        return isset($this->openingExceptions[$date]) ? $this->openingExceptions[$date] : null;
    }
    
    
    /**
     * Formats all openings
     * @param array $openings
     * @param string $keyFormat 
     * @param array $defaults
     */
    public function formatOpenings(Array $openings, $keyFormat, Array $defaults = []) {
        
        $formatted = [];
        
        foreach ($openings as $key => $times) {
            $formatted = array_merge($formatted, $this->formatOpening($key, $times, $keyFormat));
        }
        
        return array_merge($defaults, $formatted);
    }
    
    
    /**
     * Formats a single opening
     * @param string $dayName
     * @param array|string $times
     * @return array
     */
    public function formatOpening($dayName, $times, $format) {
        if (strstr($dayName, "-")) {
            //Generate DayRange
            $dayNames = explode("-", $dayName);
            $from = \DateTime::createFromFormat($format, $dayNames[0]);
            $to = \DateTime::createFromFormat($format, $dayNames[1]);
            
            if (!$from || !$to) {
                throw new InvalidFormatException(
                    sprintf('Supplied key range format \'%1$s\' does not match required format \'%2$s-%2$s\'.', $dayName, $format));
            }
            
            $dayNames = $this->createDateRange($from, $to, $format);
        }
        else if (strstr($dayName, ",")) {
            $dayNames = explode(",", $dayName);
        }
        else {
            $dayNames = [$dayName];
        }
        
        $formattedTimes = [];
        
        if (!$times) {
            $times = [];
        }
        else if (!is_array($times)) {
            $times = [$times];
        }
        
        foreach ($times as $time) {
            $formattedTimes[] = $this->formatTime(trim($time));
        }
        
        
        return array_fill_keys($dayNames, $formattedTimes);
    }
    
    
    /**
     * Formats time range and returns array with keys from and to
     * @param string $time
     * @return array
     * @throws InvalidFormatException
     */
    protected function formatTime($time) {
        $split = explode("-", $time);
        $from = isset($split[0]) ? $split[0] : false;
        $to = isset($split[1]) ? $split[1] : false;
        
        
        if ( !\DateTime::createFromFormat(self::TIME_FORMAT, $from) || !\DateTime::createFromFormat(self::TIME_FORMAT, $to)) {
            throw new InvalidFormatException(
                sprintf('Supplied time string \'%1$s\' does not match required format \'%2$s-%2$s\'.', $time, self::TIME_FORMAT));
        }
        
        return [
            "from" => $from,
            "to" => $to
        ];
    }
    
    
    /**
     * Helper to create date range array in given format including $from and $to
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string $format
     * @return array
     */
    protected function createDateRange(\DateTime $from, \DateTime $to, $format) {
        if ($to < $from) {
            $to->add(new \DateInterval("P1W"));
        }
        
        $period = new \DatePeriod($from, new \DateInterval("P1D"), $to->add(new \DateInterval("P1D")));
            
        $range = [];
        foreach ($period as $date) {
            $range[] = $date->format($format);
        }
        
        return $range;
    }

    
}
