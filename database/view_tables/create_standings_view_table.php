<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStandingsViewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the standings view
        DB::statement("
        CREATE VIEW standings_view AS
        SELECT
            team_id,
            team_name,
            team_acronym,
            conference_id,
            conference_name,
            wins,
            losses,
            total_home_score,
            total_away_score,
            home_ppg,
            away_ppg,
            score_difference,
            season_id,
            conference_rank,
            overall_rank
        FROM (
            SELECT
                teams.id AS team_id,
                teams.name AS team_name,
                teams.acronym AS team_acronym,
                teams.conference_id AS conference_id,
                conferences.name AS conference_name,
                COALESCE(SUM(CASE
                                WHEN schedules.home_score > schedules.away_score AND schedules.home_id = teams.id THEN 1
                                WHEN schedules.away_score > schedules.home_score AND schedules.away_id = teams.id THEN 1
                                ELSE 0
                            END), 0) AS wins,
                COALESCE(SUM(CASE
                                WHEN schedules.home_score < schedules.away_score AND schedules.home_id = teams.id THEN 1
                                WHEN schedules.away_score < schedules.home_score AND schedules.away_id = teams.id THEN 1
                                ELSE 0
                            END), 0) AS losses,
                COALESCE(SUM(CASE WHEN schedules.home_id = teams.id THEN schedules.home_score ELSE 0 END), 0) AS total_home_score,
                COALESCE(SUM(CASE WHEN schedules.away_id = teams.id THEN schedules.away_score ELSE 0 END), 0) AS total_away_score,
                ROUND(COALESCE(SUM(CASE WHEN schedules.home_id = teams.id THEN schedules.home_score ELSE 0 END), 0) /
                      NULLIF(COUNT(CASE WHEN schedules.home_id = teams.id THEN 1 END), 0), 2) AS home_ppg,
                ROUND(COALESCE(SUM(CASE WHEN schedules.away_id = teams.id THEN schedules.away_score ELSE 0 END), 0) /
                      NULLIF(COUNT(CASE WHEN schedules.away_id = teams.id THEN 1 END), 0), 2) AS away_ppg,
                ABS(COALESCE(SUM(CASE
                                    WHEN schedules.home_id = teams.id THEN schedules.home_score - schedules.away_score
                                    WHEN schedules.away_id = teams.id THEN schedules.away_score - schedules.home_score
                                    ELSE 0
                                END), 0)) AS score_difference,
                schedules.season_id,
                RANK() OVER (PARTITION BY teams.conference_id, schedules.season_id ORDER BY wins DESC, score_difference DESC) AS conference_rank,
                RANK() OVER (PARTITION BY schedules.season_id ORDER BY wins DESC, score_difference DESC) AS overall_rank
            FROM
                teams
            LEFT JOIN
                schedules ON teams.id = schedules.home_id OR teams.id = schedules.away_id
            LEFT JOIN
                seasons ON schedules.season_id = seasons.id
            LEFT JOIN
                conferences ON teams.conference_id = conferences.id
            WHERE
                schedules.round NOT IN ('round_of_32','round_of_16','quarter_finals', 'semi_finals', 'finals') -- Exclude certain rounds
            GROUP BY
                teams.id, teams.name, teams.acronym, teams.conference_id, conferences.name, schedules.season_id
        ) AS standings;

        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the standings view
        DB::statement('DROP VIEW IF EXISTS standings_view');
    }
}
