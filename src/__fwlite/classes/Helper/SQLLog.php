<?php

class Helper_SQLLog {

    private static $recognizedQueries = array(
        "INSERT INTO `sh_incoming`",
        "INSERT INTO `sh_delivery`",
    );

    public static function run() {

        $link  = DB::$db->getLink(true);
        $lines = DB::$db->getArray("SELECT `id`, `query`, `error_nr` FROM `sql_log` ORDER BY `id`", array(), MYSQL_ASSOC);
        foreach ($lines as $row) {
            foreach (self::$recognizedQueries as $rq) {

                // 1213 is error number for "Deadlock found when trying to get lock; try restarting transaction"
                // as per mysql specs
                if ((strpos($row['query'], $rq) === 0) && ($row['error_nr'] == 1213)) {
                    $result = mysql_query($row['query'], $link);
                    if ($result === true) {
                        $fields = "`req_time`, `query`, `error`, `error_nr`, `call_stack`, `tracking_data`";
                        DB::$db->query("INSERT INTO `sql_deadlock_log` ($fields) SELECT $fields FROM `sql_log` WHERE `id` = $row[id]");
                        DB::$db->query("DELETE FROM `sql_log` WHERE `id` = $row[id]");
                    }
                }
            }
        }
    }
    //--------------------------------------------------------------------------
}