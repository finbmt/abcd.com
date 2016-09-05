<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FootballModel extends Model
{
    public $splitColumn = "^";
    public $splitDomain = "$$";
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
        $domains = explode($this->splitDomain, $data_txt);
        $leagueDomain = explode($this->splitRecord, $domains[0]);

        $_matchData['LeagueNum'] = count($leagueDomain);
        foreach ($leagueDomain as $item) {
            $leagueItem = $this->mLeague($item);
            $_matchData['LeagueList'][$leagueItem['lId']] = $leagueItem;
        }

        $matchDomain = explode($this->splitRecord, $domains[1]);
        $_matchData['MatchCount'] = count($matchDomain);

        $allSimplifyNum = 0;
        foreach ($matchDomain as $key => $item){
            $matchItem = $this->mMatch($item);
            $matchItem['mIndex'] = $key;
            $matchItem['lLeague'] = $_matchData['LeagueList'][$matchItem['lId']];
            $_matchData['MatchList'][$matchItem['mId']] = $matchItem;
        }

        return $_matchData;
    }
    public function mLeague($info) {
        $infoArr = explode($this->splitColumn, $info);
        $arr['lId'] = $infoArr[1];
        $arr['name'] = $infoArr[0];
        $arr['type'] = $infoArr[2];
        $arr['fullName'] = $infoArr[3];
        $arr['color'] = $this->arrColor[$infoArr[1] % 16];
        $arr['matchNum'] = $infoArr[1];
        $arr['getName'] = $infoArr[3];

        return $arr;
    }

    public function mMatch($infoStr) {
        $infoArr = explode($this->splitColumn, $infoStr);

        $arr['mId'] = $infoArr[0];
        $arr['lId'] = $infoArr[1];
        $arr['State'] = $infoArr[2];
        $arr['mTime'] = $infoArr[3];
        $arr['StartTime'] = $infoArr[3];

        $arr['mTime2'] = $infoArr[4];
        if ($infoArr[4] != "")
            $arr['MatchTime'] = $this->toLocalTime($infoArr[4]);
        else
            $arr['MatchTime'] = $this->toLocalTime($infoArr[3]);
        $arr['DisplayTime'] = $this->toTimeString($infoArr[3]);

        $hName = str_replace("\\s", "", $infoArr[5]);
        $hName = str_replace("\\", "", $hName);
        $gName = str_replace("\\s", "", $infoArr[6]);
        $gName = str_replace("\\", "", $gName);
        $arr['hName'] =  $hName;
        $arr['gName'] = $gName;
        $arr['hScore'] = $infoArr[7];
        $arr['gScore'] = $infoArr[8];
        $arr['hHalfScore'] = $infoArr[9];
        $arr['gHalfScore'] = $infoArr[10];
        $arr['hRed'] = $infoArr[11];
        $arr['gRed'] = $infoArr[12];
        $arr['hYellow'] = $infoArr[13];
        $arr['gYellow'] = $infoArr[14];
        $arr['caiPiaoHao'] = $infoArr[16];
        $arr['isZhenRong'] = ($infoArr[18] == "1");
        $arr['hOrder'] = $infoArr[22] != "" ? "[" + $infoArr[22] + "]" : "";
        $arr['gOrder'] = $infoArr[23] != "" ? "[" + $infoArr[23] + "]" : "";
        $arr['explain'] = $infoArr[24];
        $arr['isTop'] = false;
        $arr['mIndex'] = 0;

        return $arr;
    }

    public function toLocalTime($time)
    {
        $date = \DateTime::createFromFormat('YmdHis', $time);
        return $sDate = $date->format('d/m/Y H:i');
    }

    public function toTimeString($time)
    {
        $date = \DateTime::createFromFormat('YmdHis', $time);
        return $date->format('H:i');
    }
}
