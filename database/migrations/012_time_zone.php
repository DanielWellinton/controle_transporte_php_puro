<?php

class TimeZone
{
    public function up(PDO $pdo): void
    {
        $timezone = $_ENV['DB_TIMEZONE'] ?? '-03:00';
        $pdo->exec("SET time_zone = ". $timezone .";");
    }
}
