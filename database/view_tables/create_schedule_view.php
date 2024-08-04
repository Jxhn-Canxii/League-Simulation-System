<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateScheduleView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
            CREATE VIEW schedule_view AS
            SELECT
                s.*,
                t_home.name AS home_team_name,
                t_away.name AS away_team_name,
                se.name AS season_name,
                l.name AS league_name,
                se.type AS league_type
            FROM
                schedules s
            JOIN
                teams t_home ON s.home_id = t_home.id
            JOIN
                teams t_away ON s.away_id = t_away.id
            JOIN
                seasons se ON s.season_id = se.id
            JOIN
                leagues l ON se.league_id = l.id
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS schedule_view');
    }
}
