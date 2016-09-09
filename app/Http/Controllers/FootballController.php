<?php namespace App\Http\Controllers;

use App\Models\FootballModel;
use GuzzleHttp\Psr7\Response;
use Sunra\PhpSimple\HtmlDomParser;
use Validator;
use Gravatar;
use Input;

class FootballController extends Controller {

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
        $dom = HtmlDomParser::file_get_html('http://www.bongdalu.com/giai-ngoai-hang-anh-fixtures/');
        foreach($dom->find('table.table_live') as $element)
            $result = $element->plaintext;
        dd($dom);
    }
	/**
	 * Show scores
	 *
	 * @return Response
	 */
	public function getlist()
	{
        $fbModel = new FootballModel();
        $_matchData = array();
        $url = "http://m.bongdalu.com/Ajaxs/ScheduleAjax.aspx?date=2016-09-06";
        $data_txt = file_get_contents($url);
        $domains = explode($fbModel->splitScheduleDomain, $data_txt);
        $leagueDomain = explode($fbModel->splitRecord, $domains[0]);

        $_leagueData['LeagueNum'] = count($leagueDomain);
        foreach ($leagueDomain as $item) {
            $leagueItem = $fbModel->mLeague($item);
            $_leagueData['LeagueList'][$leagueItem[1]] = $leagueItem[0];
        }

        $matchDomain = explode($fbModel->splitRecord, $domains[1]);
        $_matchData['MatchCount'] = count($matchDomain);

        foreach ($matchDomain as $key => $item){
            $matchItem = $fbModel->mMatch($item);
            //$matchItem['mIndex'] = $key;
            $_matchData['MatchList'][$matchItem['mId']] = $matchItem;
        }

        $tmp = array();
        foreach ($_matchData['MatchList'] as $key => $item)
        {
            $tmp[$item['lId']][] = $item;
        }

        $output = array();
        foreach($tmp as $key => $item)
        {
            $output[] = array(
                'League' => $_leagueData['LeagueList'][$key],
                'Match' => $item
            );
        }
        /*$de = $fbModel->mBet365('1224307');
        dd($de);*/

        return \Illuminate\Support\Facades\Response::json([
            'message' => $output
        ], 200);
	}
}
