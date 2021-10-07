<?php

/**
 * Use this file to output reports required for the SQL Query Design test.
 * An example is provided below. You can use the `asTable` method to pass your query result to,
 * to output it as a styled HTML table.
 */

$database = 'nba2019';
require_once('vendor/autoload.php');
require_once('include/utils.php');

/*
 * Example Query
 * -------------
 * Retrieve all team codes & names
 */
echo '<h1>Example Query</h1>';
$teamSql = "SELECT * FROM team";
$teamResult = query($teamSql);
// dd($teamResult);
echo asTable($teamResult);

/*
 * Report 1
 * --------
 * Produce a query that reports on the best 3pt shooters in the database that are older than 30 years old. Only 
 * retrieve data for players who have shot 3-pointers at greater accuracy than 35%.
 * 
 * Retrieve
 *  - Player name
 *  - Full team name
 *  - Age
 *  - Player number
 *  - Position
 *  - 3-pointers made %
 *  - Number of 3-pointers made 
 *
 * Rank the data by the players with the best % accuracy first.
 */
echo '<h1>Report 1 - Best 3pt Shooters</h1>';
// write your query here
$ptShooter = "SELECT r.name,t.name,p.age,r.number,r.pos,
                CONCAT(ROUND((`3pt` / `3pt_attempted` * 100),2),'%')
                AS `3pt_accuracy`, `3pt` FROM `player_totals` AS p
                INNER JOIN `roster` AS r ON p.player_id = r.id
                INNER JOIN `team` AS t ON r.team_code = t.code
                WHERE age > 30 AND (`3pt` / `3pt_attempted` * 100) > 35
                ORDER BY `3pt_accuracy` DESC";

$ptResult = query($ptShooter);
echo asTable($ptResult);

/*
 * Report 2
 * --------
 * Produce a query that reports on the best 3pt shooting teams. Retrieve all teams in the database and list:
 *  - Team name
 *  - 3-pointer accuracy (as 2 decimal place percentage - e.g. 33.53%) for the team as a whole,
 *  - Total 3-pointers made by the team
 *  - # of contributing players - players that scored at least 1 x 3-pointer
 *  - of attempting player - players that attempted at least 1 x 3-point shot
 *  - total # of 3-point attempts made by players who failed to make a single 3-point shot.
 * 
 * You should be able to retrieve all data in a single query, without subqueries.
 * Put the most accurate 3pt teams first.
 */
echo '<h1>Report 2 - Best 3pt Shooting Teams</h1>';
// write your query here
$ptTeams = "SELECT t.name, p.`3pt`, p.`3pt_attempted` FROM `player_totals` AS p
            INNER JOIN `roster` AS r ON p.player_id = r.id
            INNER JOIN `team` AS t ON r.team_code = t.code";

$ptRes = query($ptTeams);


$team = [];
$a = 1;

foreach($ptRes as $data)
{
    if(!isset($team[$data['name']]))
    {
        $a = 1;
        $team[$data['name']]['3pt']           =  $data['3pt']; //Gather all 3pt
        $team[$data['name']]['3pt_attempt']   =  $data['3pt_attempted']; //Gather all 3pt
        $team[$data['name']]['3pt_pt_1']      =  ($data['3pt'] > 1) ?: 0;
        $team[$data['name']]['3pt_attempt_1'] =  ($data['3pt_attempted'] > 1) ?: 0;
        $team[$data['name']]['3pt_fail']      =  ($data['3pt'] == 0 && $data['3pt_attempted'] > 0 ) ? $data['3pt_attempted'] : 0;    
    }
    else
    {
        $team[$data['name']]['3pt']           +=  $data['3pt']; //Gather all 3pt
        $team[$data['name']]['3pt_attempt']   +=  $data['3pt_attempted']; //Gather all 3pt
        $team[$data['name']]['3pt_pt_1']      +=  ($data['3pt'] > 1) ?: 0;
        $team[$data['name']]['3pt_attempt_1'] +=  ($data['3pt_attempted'] > 1) ?: 0;
        $team[$data['name']]['3pt_fail']      +=  ($data['3pt'] == 0 && $data['3pt_attempted'] > 0 ) ? $data['3pt_attempted'] : 0;         
    }

    $team[$data['name']]['wholeteam']     =  $a;
    $a++;
}

$FinalResult = [];
$accuracy = [];
foreach ($team as $key => $val)
{
    $FinalResult[] = array(
                        'name' => $key,
                        '3pt_accuracy' => number_format(($val['3pt'] / $val['3pt_attempt']) * 100,2).'%',
                        '3pt' => number_format($val['3pt'],0,'',','),
                        '3pt_player_contributor' => $val['3pt_pt_1'],
                        '3pt_player_attempt' => $val['3pt_attempt_1'],
                        '3pt_total_fail' => $val['3pt_fail']
                    );

    $accuracy[$key] = number_format(($val['3pt'] / $val['3pt_attempt']) * 100,2);
}

array_multisort($accuracy, SORT_DESC, $FinalResult);

echo asTable($FinalResult);


?>