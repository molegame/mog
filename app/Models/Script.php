<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;

class Script extends Model
{
    protected static $cmd = 10002;
    /**
     * 服务器类型
     */
    public static $servers = [
        1 => 'client',  // 客户端 
        2 => 'scene',   // 场景服
        3 => 'session', // 会话服
    ];

    public function setZonesAttribute($value)
    {
        $this->attributes['zones'] = implode(';', $value);
    }

    /**
     * 执行脚本
     */
    public function perform()
    {
        return self::performScript($this->content, $this->server, $this->zones);
    }

    public static function performScript($script, $server, $zones)
    {
        $cmd = 'PERFORM_SCRIPTCMD';
        $params = [
            'type' => $server,
            'content' => $script,
            'zones' => $zones,
        ];
        $client = new Client();
        $res = $client->request('GET', config('game.url'), [
            'timeout' => 10,
            'query' => [
                'CmdId' => static::$cmd,
                'params' => json_encode([$cmd, $params])
            ]
        ]);
        $data = json_decode($res->getBody(), true);

        return $data;
    }
}
