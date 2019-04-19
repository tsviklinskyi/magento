<?php
class TSG_Callcenter_Test_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    public function binaryGap($str)
    {
        $maxBinaryGap = 0;
        $first1 = strpos($str, '1');
        if ($first1 !== false) {
            $nextStr = substr($str,$first1 + 1);
            $next1 = strpos($nextStr, '1');
            while ($next1 !== false) {
                if ($next1 > $maxBinaryGap) {
                    $maxBinaryGap = $next1;
                }
                $nextStr = substr($nextStr,$next1 + 1);
                $next1 = strpos($nextStr, '1');
            }
        }
        return $maxBinaryGap;
    }

    public function testBinaryGap()
    {
        $str = '1000010001';
        $this->assertEquals($this->checkBinaryGap($str), $this->binaryGap($str));
    }

    public function checkBinaryGap($str)
    {
        $binaryGap = 0;
        switch ($str){
            case '1000010001' :
                $binaryGap = 4;
                break;
            case '100000001' :
                $binaryGap = 7;
                break;
            case '10000000' :
                $binaryGap = 0;
                break;
            case '000000' :
                $binaryGap = 0;
                break;
            case '111111111' :
                $binaryGap = 0;
                break;
            case '00000001' :
                $binaryGap = 0;
                break;
            case '1001000001' :
                $binaryGap = 5;
                break;
        }
        return $binaryGap;
    }
}