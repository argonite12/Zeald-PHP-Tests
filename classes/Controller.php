<?php
use Illuminate\Support;  // https://laravel.com/docs/5.8/collections - provides the collect methods & collections class

use LSS\Array2Xml;
require_once('classes/Exporter.php');

class Controller extends Exporter
{

    protected $args;

    public function __construct($args)
    {
        $this->args = $args;
    }

    public function export($type, $format)
    {
        $data = [];        
        $searchArgs = ['player', 'playerId', 'team', 'position', 'country'];

        switch ($type)
        {

            case 'playerstats':
            case 'players':

                $search = $this->args->filter(function($value, $key) use ($searchArgs)
                {
                    return in_array($key, $searchArgs);
                });

                if(!empty($search->all()))
                    $data = ($type == 'playerstats') ? Exporter::getPlayerStats($search) : Exporter::getPlayers($search);
                break;

            default:
                exit("Error: No data found!");
        }

        if (!$data)
        {
            exit("Error: No data found!");
        }

        return Exporter::format($data, $format);
    }
}