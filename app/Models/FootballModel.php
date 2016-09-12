<?php

namespace App\Models;

use Faker\Provider\cs_CZ\DateTime;
use Illuminate\Database\Eloquent\Model;

class FootballModel extends Model
{
    public $splitColumn = "^";
    public $splitDomain = "$$";
    public $splitScheduleDomain = "$";
    public $splitRecord = "!";
    public $arrColor = ["#006666", "#518ed2", "#e8811a", "#949720", "#8f6dd6", "#53ac98", "#ff9966",
        "#457d1b", "#8d8abd", "#996733", "#8c8a64", "#999012", "#ff6633", "#ca00ca", "#1ba570", "#990099"];
    //

    /**
     * @return array
     * $_matchData['LeagueList'] danh sach cac giai dau
     * $_matchData['MatchList'] danh sach cac tran dau trong ngay hien tai
     */
    public function getLeagueMatch()
    {
        $_matchData = array();
        $url = "http://m.bongdalu.com/phone/Schedule_6_0.txt?flesh=0.7174544939999075";
        $data_txt = file_get_contents($url);
        if($data_txt === FALSE) {
            return;
        }
        $domains = explode($this->splitDomain, $data_txt);
        $leagueDomain = explode($this->splitRecord, $domains[0]);

        $_matchData['LeagueNum'] = count($leagueDomain);
        foreach ($leagueDomain as $item) {
            $leagueItem = $this->mLeague($item, 1);
            $_matchData['LeagueList'][$leagueItem['lId']] = $leagueItem;
        }

        $matchDomain = explode($this->splitRecord, $domains[1]);
        $_matchData['MatchCount'] = count($matchDomain);
        foreach ($matchDomain as $key => $item){
            $matchItem = $this->mMatch($item, 0);
            //$matchItem['mIndex'] = $key;
            $matchItem['lLeague'] = $_matchData['LeagueList'][$matchItem['lId']];
            $_matchData['MatchList'][$matchItem['mId']] = $matchItem;

        }

        return $_matchData;
    }

    public function mBet365($mId) {
        if (!$mId) return;

        $url = 'http://m.bongdalu.com/Ajax.aspx?type=3&id=' . $mId;

        $json_string = @file_get_contents($url);
        if($json_string === FALSE) {
            return;
        }
        $data = json_decode($json_string, TRUE);

        if (!isset($data['HandicapList'][1])) return;
        $mBet365 = $data['HandicapList'][1];
        $output['Up'] = $mBet365['Up'];
        $output['Goal'] = $this->goal2GoalT($mBet365['Goal']);
        $output['Down'] = $mBet365['Down'];

        /*unset($mBet365_first['RUp']);
        unset($mBet365_first['RGoal']);
        unset($mBet365_first['RDown']);
        unset($mBet365_first['Index']);
        unset($mBet365_first['Comp']);
        unset($mBet365_first['CId']);
        unset($mBet365_first['OddsId']);*/

        return $output;
    }

    public function mLeague($info) {
        $infoArr = explode($this->splitColumn, $info);
        $arr['lId'] = $infoArr[1];
        $arr['name'] = $infoArr[0];
        //$arr['type'] = $infoArr[2];
        $arr['fullName'] = $infoArr[3];
        $arr['color'] = $this->arrColor[$infoArr[1] % 16];
        //$arr['matchNum'] = $infoArr[1];
        //$arr['getName'] = $infoArr[3];

        return array($arr, $infoArr[1]);
    }

    /**
     * @param $infoStr
     * @param $type 0 tỷ số / 1 schedule / 2 detail
     * @param null $mId
     * @return mixed
     */
    public function mMatch($infoStr, $type, $mId = null) {
        $infoArr = explode($this->splitColumn, $infoStr);
        $isDetail = false;
        //dd($infoArr);
        if (isset($mId))
        {
            if ($mId == $infoArr[0]){
                $isDetail = true;
            } else {
                return;
            }
        }

        $arr['id'] = $infoArr[0];//mId
        $arr['league_id'] = $infoArr[1];//lId
        //$arr['State'] = $infoArr[2];
        $arr['date'] = $this->toDateString($infoArr[3]);
        $arr['start'] = $this->toTimeString($infoArr[3]);
        $arr['status'] = $this->getMatchState($infoArr[2], $infoArr[3]);
        //$arr['mTime'] = $infoArr[3];
        //$arr['mTime2'] = $infoArr[4];

        /*$arr['StartTime'] = $this->toLocalTime($infoArr[3]);
        if ($infoArr[4] != "")
            $arr['MatchTime'] = $this->toLocalTime($infoArr[4]);
        else
            $arr['MatchTime'] = $this->toLocalTime($infoArr[3]);*/

        //$arr['DisplayDate'] = $this->toDateString($infoArr[3]);
        //$arr['DisplayTime'] = $this->toTimeString($infoArr[3]);

        $hName = str_replace("\\s", "", $infoArr[5]);
        $hName = str_replace("\\", "", $hName);
        $gName = str_replace("\\s", "", $infoArr[6]);
        $gName = str_replace("\\", "", $gName);
        $arr['hName'] =  $hName;
        $arr['gName'] = $gName;
        $arr['hScore'] = $infoArr[7];
        $arr['gScore'] = $infoArr[8];

        if ($isDetail) {
            $arr['hHalfScore'] = $infoArr[9];
            $arr['gHalfScore'] = $infoArr[10];
            $arr['hRed'] = $infoArr[11];
            $arr['gRed'] = $infoArr[12];
            $arr['hYellow'] = $infoArr[13];
            $arr['gYellow'] = $infoArr[14];

            $mBet365 = $this->mBet365($mId);

            if ($mBet365) {
                $arr['Up'] = $mBet365['Up'];
                $arr['Goal'] = $mBet365['Goal'];
                $arr['Down'] = $mBet365['Down'];
            }
        }

        //$arr['caiPiaoHao'] = $infoArr[16];
        //$arr['isZhenRong'] = ($infoArr[18] == "1");
        if($type == 0)
        {
            $arr['hOrder'] = $infoArr[22] != "" ? "[" + $infoArr[22] + "]" : "";
            $arr['gOrder'] = $infoArr[23] != "" ? "[" + $infoArr[23] + "]" : "";
        }

        //$arr['explain'] = $infoArr[24];
        //$arr['isTop'] = false;
        //$arr['mIndex'] = 0;
        //$arr['mState'] = $this->getMatchState($arr['State']);
        if ($type == 1)
        {
            $arr['hOrder'] = $infoArr[19] != "" ? "[" + $infoArr[19] + "]" : "";
            $arr['gOrder'] = $infoArr[20] != "" ? "[" + $infoArr[20] + "]" : "";
            $arr['odds'] = $this->goal2GoalT($infoArr[17]);
            $arr['hMoney'] = $infoArr[16];
            $arr['gMoney'] = $infoArr[18];
        }

        return $arr;
    }

    public function getMatchState($mState, $startTime = '') {
        $ms = "";
        switch ($mState) {
            case 4:
                $ms = "Ot";
                break; //加时 - thêm giờ
            case 3:
                $ms = "Part2";
                break; //下半场 Hiệp 2
            case 2:
                $ms = "H/T";
                break; //中场
            case 1:
                $ms = "Part1";
                break; //上半场 Hiệp 1
            case 0:
                $ms = "&nbsp"; // chưa bắt đầu
                break;
            case -1:
                $ms = "&nbsp";
                break; //完 hoàn thành
            case -10:
                $ms = "Cancel";
                break; //取消 hủy bỏ
            case -11:
                $ms = "pend.";
                break; //待定 tạm dừng
            case -12:
                $ms = "Abd";
                break; //腰砍 cắt
            case -13:
                $ms = "Pause";
                break; //中断 dừng
            case -14:
                $ms = "Postp.";
                break; //推迟 hoãn
        }

        if ($mState == 1) {
            date_default_timezone_set("Asia/Bangkok");
            $now = new \DateTime();
            $serverTime = $now->getTimestamp()/ 1000;
            $startTime = \DateTime::createFromFormat('YmdHis', $startTime)->getTimestamp()/ 1000;

            $df = ($serverTime - $startTime) / 60;
            $df = intval($df);
            if ($df <= 0) {
                $ms = "1'";
            } else if ($df <= 45) {
                $ms = $df + "'";
            } else {
                $ms = "45+'";
            }
        } else if ($mState == 3) {
            date_default_timezone_set("Asia/Bangkok");
            $now = new \DateTime();
            $serverTime = $now->getTimestamp()/ 1000;
            $startTime = \DateTime::createFromFormat('YmdHis', $startTime)->getTimestamp()/ 1000;

            $df = ($serverTime - $startTime) / 60 + 46;
            //由于不确定它计算出来的数据一定准确，所以做多几个判断
            $df = intval($df);

            if ($df <= 46) {
                $ms = "46'";
            } else if ($df <= 90) {
                $ms = $df + "'";
            } else {
                $ms = "90+'";
            }
        }

        return $ms;
    }

    public function toLocalTime($str)
    {
        //2016 09 11 11 00 00
        $time = substr($str, -6);
        $hours = substr($time, 0, 2);
        $min = substr($time, 2, 2);
        $time_str = $hours . ":" . $min;

        $date = substr($str, 8);
        $year = substr($date, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);

        return $day . '/' . $month . '/' . $year . ' ' . $time_str;
    }

    public function toTimeString($time)
    {
        //2016 09 11 11 00 00
        $time = substr($time, -6);
        $hours = substr($time, 0, 2);
        $min = substr($time, 2, 2);
        return $hours . ":" . $min;
    }

    public function toDateString($time)
    {
        $date = \DateTime::createFromFormat('YmdHis', $time);
        return $date->format('d/m/Y');
    }

    public function goal2GoalT($goal) {
        //handicap conversion
        $GoalCn = ["0", "0/0.5", "0.5", "0.5/1", "1", "1/1.5", "1.5", "1.5/2", "2", "2/2.5", "2.5", "2.5/3", "3", "3/3.5", "3.5", "3.5/4", "4", "4/4.5", "4.5", "4.5/5", "5", "5/5.5", "5.5", "5.5/6", "6", "6/6.5", "6.5", "6.5/7", "7", "7/7.5", "7.5", "7.5/8", "8", "8/8.5", "8.5", "8.5/9", "9", "9/9.5", "9.5", "9.5/10", "10", "10/10.5", "10.5", "10.5/11", "11", "11/11.5", "11.5", "11.5/12", "12", "12/12.5", "12.5", "12.5/13", "13", "13/13.5", "13.5", "13.5/14", "14"];
        $GoalCn2 = ["0", "0/-0.5", "-0.5", "-0.5/-1", "-1", "-1/-1.5", "-1.5", "-1.5/-2", "-2", "-2/-2.5", "-2.5", "-2.5/-3", "-3", "-3/-3.5", "-3.5", "-3.5/-4", "-4", "-4/-4.5", "-4.5", "-4.5/-5", "-5", "-5/-5.5", "-5.5", "-5.5/-6", "-6", "-6/-6.5", "-6.5", "-6.5/-7", "-7", "-7/-7.5", "-7.5", "-7.5/-8", "-8", "-8/-8.5", "-8.5", "-8.5/-9", "-9", "-9/-9.5", "-9.5", "-9.5/-10", "-10"];

        if ($goal === "")
            return "";
        else {
            if ($goal >= 0) return $GoalCn[intval($goal * 4)];
            else return $GoalCn2[abs(intval($goal * 4))];
        }
    }
}
