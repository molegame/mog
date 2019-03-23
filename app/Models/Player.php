<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\Paginator;
use GuzzleHttp\Client;

class Player extends Model
{
    protected static $cmd = 10003;

    /**
     * Paginate the given query.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @throws \InvalidArgumentException
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->getPerPage();

        $offset = max(0, ($page - 1) * $perPage);
        $count = $perPage * 3;
        $params = json_encode([
            "funId"     => "QUERY_ROLE_INFO",
            "role_id"   => Request::get('id') ?: '',
            "nickname"  => Request::get('name') ?: '',
        ]);
        $client = new Client();
        $res = $client->request('GET', config('game.gm.url'), [
            'timeout' => 10,
            'query' => [
                'CmdId' => static::$cmd,
                'params' => $params
            ]
        ]);
        $data = json_decode($res->getBody(), true);

        $results = array();
        if (isset($data) && $data['res'] == 0) {
            $total = 1;
            array_push($results, $data);
        }
        $total = count($results);

        return new LengthAwarePaginator(static::hydrate($results), $total, $perPage, $page,[
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName
        ]);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static|static[]
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function findOrFail($id, $columns = ['*'])
    {
        $params = json_encode([
            "funId"     => "QUERY_ROLE_INFO",
            "role_id"   => $id
        ]);
        $client = new Client();
        $res = $client->request('GET', config('game.gm.url'), [
            'connect_timeout' => 10,
            'query' => [
                'CmdId' => static::$cmd,
                'params' => $params
            ]
        ]);
        $data = json_decode($res->getBody(), true);
        
        return static::newFromBuilder($data);
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array|\Closure  $column
     * @param  mixed   $operator
     * @param  mixed   $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this;
    }

    public static function with($relations)
    {
        return new static;
    }
}