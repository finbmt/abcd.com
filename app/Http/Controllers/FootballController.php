<?php namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Models\FootballModel;
use GuzzleHttp\Psr7\Response;
use Sunra\PhpSimple\HtmlDomParser;
use Validator;
use Gravatar;
use Input;

class FootballController extends Controller {
    //private const URL_TY_SO = 'http://m.bongdalu.com/phone/Schedule_6_0.txt?flesh=0.7174544939999075';
    public $URL_TY_SO = 'http://abcd.com/public/Schedule_6_0_201609100105.txt';
    public $URL_SCHEDULE = 'http://m.bongdalu.com/Ajaxs/ScheduleAjax.aspx';
    public $URL_DETAIL_AJAX = 'http://m.bongdalu.com/Ajax.aspx?type=1&id=';
    public $URL_ODDS = 'http://m.bongdalu.com/txt/goalBf31.xml';

    public function index ()
    {
        $url = "http://abcd.com/public/list.json";
        $json = file_get_contents($url);
        $array = json_decode($json, true);
        //dd($array);
        return view('football.show-scores',[
        		'total_scores'             => null,
                'scores'             => $array['message']
        	]);
    }

    public function demo ()
    {
        $dom = HtmlDomParser::file_get_html('http://m.bongdalu.com/EventDetail.htm?scheid=1311924');
        foreach($dom->find('.content') as $element)
            $result = $element->plaintext;
        dd($dom);
    }
	/**
	 * Show scores
	 *
	 * @return Response
	 */
	public function getList()
	{
        $output = $this->_getList($this->URL_TY_SO, 0);

        return \Illuminate\Support\Facades\Response::json([
            'message' => '',
            'result' => $output
        ], 200);
	}

    /**
     * Show schedule
     *
     * @return Response
     */
    public function getSchedule($date = null)
    {
        $param = '';
        if (isset($date))
        {
            $param = '?date=' . $date;
        } else {
            $date = date("Y-m-d");
            $param = '?date=' . $date;
        }

        if (!preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/",$date))
        {
            return \Illuminate\Support\Facades\Response::json([
                'message' => 'Error : date wrong',
                'result' => ''
            ], 400);
        }

        $url = $this->URL_SCHEDULE . $param;

        $output = $this->_getList($url, 1);
        return \Illuminate\Support\Facades\Response::json([
            'message' => '',
            'result' => $output
        ], 200);
    }

    /**
     * API003
     * Get detail match
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function getdetail(\Illuminate\Http\Request $request)
    {
        $mId = $request->input('id');
        $date = $request->input('date');
        if (!isset($mId) || !isset($date) || !ctype_digit($mId) || !preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/",$date))
        {
            return \Illuminate\Support\Facades\Response::json([
                'message' => 'Error : id or date wrong',
                'result' => ''
            ], 400);
        }

        $fbModel = new FootballModel();
        $_matchData = array();

        $url = $this->URL_SCHEDULE . '?date=' . $date;

        $data_txt = file_get_contents($url);
        $domains = explode($fbModel->splitScheduleDomain, $data_txt);
        $matchDomain = explode($fbModel->splitRecord, $domains[1]);
        $matchItem = array();
        foreach ($matchDomain as $key => $item) {
            $info = explode($fbModel->splitColumn, $item);
            if ($mId == $info[0]){
                $matchItem = $fbModel->mMatch($item, 2, $mId);
                break;
            } else {
                continue;
            }
        }
        $output['Match'] = $matchItem;

        $url_ajax = $this->URL_DETAIL_AJAX . $mId;
        $json_content = file_get_contents($url_ajax);
        if ($json_content) {
            $data = json_decode($json_content, TRUE);
            $output['TechStatistic'] = $data['TechStatistic'];
            $output['DetailList'] = $data['DetailList'];
        }

        return \Illuminate\Support\Facades\Response::json([
            'message' => '',
            'result' => $output
        ], 200);
    }

    /**
     * API004
     * Get status of match
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function getstatus(\Illuminate\Http\Request $request)
    {
        $mId = $request->input('id');
        $date = $request->input('date');
        if (!isset($mId) || !isset($date) || !ctype_digit($mId) || !preg_match("/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/",$date))
        {
            return \Illuminate\Support\Facades\Response::json([
                'message' => 'Error : id or date wrong',
                'result' => ''
            ], 400);
        }

        $fbModel = new FootballModel();
        $url = $this->URL_SCHEDULE . '?date=' . $date;
        $data_txt = file_get_contents($url);
        $domains = explode($fbModel->splitScheduleDomain, $data_txt);
        $matchDomain = explode($fbModel->splitRecord, $domains[1]);

        foreach ($matchDomain as $key => $item) {
            $info = explode($fbModel->splitColumn, $item);
            if ($mId == $info[0]){
                $output['status'] = $fbModel->getMatchStatus($info[2], $info[3]);
                break;
            } else {
                continue;
            }
        }

        if (!isset($output['status']))
        {
            return \Illuminate\Support\Facades\Response::json([
                'message' => 'Error : id or date wrong',
                'result' => ''
            ], 400);
        }

        return \Illuminate\Support\Facades\Response::json([
            'message' => '',
            'result' => $output
        ], 200);
    }

    /**
     * @param $url
     * @param int $type : 0 tỷ số, 1 shedule
     * @return array
     */
    private function _getList($url, $type = 0) {
        $fbModel = new FootballModel();
        $splitDomain = $fbModel->splitScheduleDomain;
        if ($type == 0)
        {
            $splitDomain = $fbModel->splitDomain;
        }

        $_matchData = array();
        $data_txt = file_get_contents($url);
        $domains = explode($splitDomain, $data_txt);
        $leagueDomain = explode($fbModel->splitRecord, $domains[0]);

        // get list leagues
        $_leagueData['LeagueNum'] = count($leagueDomain);
        foreach ($leagueDomain as $item) {
            $leagueItem = $fbModel->mLeague($item);
            $_leagueData['LeagueList'][$leagueItem[1]] = $leagueItem[0];
        }

        // get list Odds
        if ($type == 0) {
            $data_xml = simplexml_load_file($this->URL_ODDS);
            $_oddsData = array();
            foreach ($data_xml->match->m as $value) {
                $itemOdds = explode(',', $value);
                $item = array();
                $item['odds'] = $fbModel->goal2GoalT($itemOdds[2]);
                $item['hMoney'] = $itemOdds[3];
                $item['gMoney'] = $itemOdds[4];
                $_oddsData[$itemOdds[0]] = $item;
            }
        }

        $matchDomain = explode($fbModel->splitRecord, $domains[1]);
        $_matchData['MatchCount'] = count($matchDomain);
        //dd($matchDomain);
        foreach ($matchDomain as $key => $item)
        {
            if ($type == 0)
            {
                $matchItem = $fbModel->mMatch($item, 0);
            } else {
                $matchItem = $fbModel->mMatch($item, 1);
            }

            if ($type == 0 && array_key_exists($matchItem['id'], $_oddsData))
            {
                $matchItem = array_merge($matchItem, $_oddsData[$matchItem['id']]);
            }

            $_matchData['MatchList'][$matchItem['id']] = $matchItem;
        }

        $tmp = array();
        foreach ($_matchData['MatchList'] as $key => $item)
        {
            $tmp[$item['league_id']][] = $item;
        }

        $output = array();
        foreach($tmp as $key => $item)
        {
            $output[] = array(
                'League' => $_leagueData['LeagueList'][$key],
                'Match' => $item
            );
        }

        return $output;
    }
}
