<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameStatisticsCounters extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE `websockets_statistics_entries`
            CHANGE `peak_connection_count` `peak_connections_count` INT UNSIGNED NOT NULL DEFAULT 0,
            CHANGE `websocket_message_count` `websocket_messages_count` INT UNSIGNED NOT NULL DEFAULT 0,
            CHANGE `api_message_count` `api_messages_count` INT UNSIGNED NOT NULL DEFAULT 0'
        );
    }

    public function down()
    {
        DB::statement('ALTER TABLE `websockets_statistics_entries`
            CHANGE `peak_connections_count` `peak_connection_count` INT UNSIGNED NOT NULL DEFAULT 0,
            CHANGE `websocket_messages_count` `websocket_message_count` INT UNSIGNED NOT NULL DEFAULT 0,
            CHANGE `api_messages_count` `api_message_count` INT UNSIGNED NOT NULL DEFAULT 0'
        );
    }
}
