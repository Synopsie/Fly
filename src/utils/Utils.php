<?php
declare(strict_types=1);

namespace fly\utils;

use Exception;

class Utils {

    public static function convertTime(int $time) : string {
        $hours = intdiv($time, 3600);
        $minutes = intdiv($time % 3600, 60);
        $seconds = $time % 60;

        $result = '';
        if ($hours > 0) {
            $result .= $hours . 'h ';
        }
        if ($minutes > 0) {
            $result .= $minutes . 'm ';
        }
        if ($seconds > 0 || ($hours === 0 && $minutes === 0)) {
            $result .= $seconds . 's';
        }

        return trim($result);
    }



}