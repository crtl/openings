<?php

use Crtl\Openings\OpeningsManager;
use Crtl\Openings\Exceptions\InvalidFormatException;
use PHPUnit\Framework\TestCase;

class OpeningsManagerTest extends TestCase {
    
    
    protected $_openings = [
        "Mon-Fri" => ["10:00-12:00", "14:00-19:00"],
        "Sat" => ["10:00-16:00"],
        "Sun" => []
    ];

    
    public function testCorrectlyFormatsOpenings() {
        $openingsManager = new OpeningsManager($this->_openings);

        $openings = $openingsManager->getOpenings();


        $this->assertCount(7, $openings);
        $this->assertArrayHasKey("Mon", $openings);
        $this->assertArrayHasKey("Tue", $openings);
        $this->assertArrayHasKey("Wed", $openings);
        $this->assertArrayHasKey("Thu", $openings);
        $this->assertArrayHasKey("Fri", $openings);
        $this->assertArrayHasKey("Sat", $openings);
        $this->assertArrayHasKey("Sun", $openings);

        $this->assertEmpty($openings["Sun"]);

        $openingsMon = [
            ["from" => "10:00", "to" => "12:00"],
            ["from" => "14:00", "to" => "19:00"]
        ];

        $this->assertEquals($openings["Mon"], $openingsMon);
        $this->assertEquals($openings["Tue"], $openingsMon);
        $this->assertEquals($openings["Wed"], $openingsMon);
        $this->assertEquals($openings["Thu"], $openingsMon);
        $this->assertEquals($openings["Fri"], $openingsMon);
    }

    
    public function testCorrectlySetsOpeningDefaultToEmptyArray() {
        
        $openingsManager = new OpeningsManager([]);
        
        $openings = $openingsManager->getOpenings();
        
        
        $this->assertCount(7, $openings);
        
        foreach ($openings as $day => $opening) {
            $this->assertEquals([], $opening);
        }
        
    }
    
    
    public function testCorrectlyFormatsExceptions() {
        $openingsManager = new OpeningsManager([], [
            "2017/01/01" => [],
            "2016/02/04" => ["10:00-18:00"],
            "2017/05/03-2017/05/9" => ["08:00-10:00", "22:00-23:59"],
            "2017/08/01,2017/12/24" => ["09:00-13:00"]
        ]);
        
        $exceptions = $openingsManager->getOpeningExceptions();
        
        $this->assertNotEmpty($exceptions);
        $this->assertCount(11, $exceptions);
        
    }
    
    
    public function testShouldBeOpened() {
        
        $now = new \DateTimeImmutable();
        $interval = new \DateInterval("PT1H");
        
        $from = new \DateTime("today");
        $to = new \DateTime("today 23:59:59");
        
        $date = new \DateTime("today 12:12:12");
        
        $openingsManager = new OpeningsManager([
            $now->format("D") => [sprintf("%s-%s", $from->format("H:i"), $to->format("H:i"))]
        ]);
        
        $this->assertTrue($openingsManager->isOpen());
        $this->assertTrue($openingsManager->isOpen($date));
        
    }

    public function testOpeningExceptions() {

        $manager = new OpeningsManager([
            "Mon-Fri" => "08:00-16:00"
        ], [
            "2019/12/24" => []
        ]);

        $date = \DateTime::createFromFormat("Y-m-d",  "2019/12/24");

        $this->assertFalse($manager->isOpen($date));


    }
    
    
    public function testShouldBeClosed() {
        $openingsManager = new OpeningsManager();
        $this->assertFalse($openingsManager->isOpen());
    }
    
    
    public function testShouldThrowExceptionForInvalidTimeFormat() {
        $openingsManager = new OpeningsManager();
        
        $times = [
            ["10:00-20:00", "a-b"],
            ["13:00"],
            ["14:30-WRONG"],
            "bad argument"
        ];
        
        
        foreach ($times as $time) {
            
            try {
                $openingsManager->setOpenings(["Mon" => $time]);
            } 
            catch (InvalidFormatException $ex) {
                $this->assertInstanceOf(InvalidFormatException::class, $ex);
            }
            
            try {
                $openingsManager->setOpeningExceptions(["2000/01/01" => $time]);
            } 
            catch (InvalidFormatException $ex) {
                $this->assertInstanceOf(InvalidFormatException::class, $ex);
            }
            
        }
    }
    
    public function testShouldThrowExceptionForInvalidKeyFormat() {
        $openingsManager = new OpeningsManager();
        
        //Invalid opening keys
        $openingKeys = [
            "Tes", "Abf", "01", "123", "0"
        ];
        
        //Invalid opening exception keys
        $exceptionKeys = [
            "17/01/01",     //Wrong year format
            "01/01/2017",   //Wrong order
            "2017/01",      //Missing day
            "17/1/1",       //Missing zero pad
            "wrong",
            "a/b/c",
            "aa/bb/cc"
        ];
        
        $times = ["10:00-20:00"];
        
        //Test openings
        foreach ($openingKeys as $dayName) {
            try {
                $openingsManager->setOpenings([$dayName => $times]);
            } 
            catch (InvalidFormatException $ex) {
                $this->assertInstanceOf(InvalidFormatException::class, $ex);
            }
        }
        
        //Test opening exceptions
        foreach ($exceptionKeys as $dayName) {
            try {
                $openingsManager->setOpeningExceptions([$dayName => $times]);
            } 
            catch (InvalidFormatException $ex) {
                $this->assertInstanceOf(InvalidFormatException::class, $ex);
            }
        }
        
    }
    
    
}
