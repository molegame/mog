<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use App\Facades\Game;
use GuzzleHttp\Client;

class ZoneController extends Controller
{
    protected static $cmd = 10001;

    public function index()
    {
        $client = new Client();
        $res = $client->request('GET', config('game.gm.url'), [
            'timeout' => 10,
            'query' => [
                'CmdId' => static::$cmd
            ]
        ]);
        $zones = json_decode($res->getBody(), true);
        
        $result = ['items' => [], 'selected' => 0];
        if ($zones) {
            $result['items'] = $zones;
        }
        $zoneSelected = Game::getZone();
        if ($zoneSelected != 0 && !in_array($zoneSelected, $zones)) {
            Game::setZone(0);
        }
        $result["selected"] = Game::getZone();
        
        Log::debug($result);
        return $result;
    }

    public function select()
    {
        Game::setZone((int)Input::get('_zone', 0));
    }
}