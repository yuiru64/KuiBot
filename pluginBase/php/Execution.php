<?php
namespace php;

class Execution {
    /** @return string|null */
    public static function exec(string $command, int $timeout = null) {
        $res = proc_open($command, [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ], $pipes);
        if (!is_resource($res))
            throw new \Exception("Not executable");
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $other = null;

        $buf = "";
        $err = "";
        $lastTime = microtime(true) * 1000;
        while (true) {
            $read = [$pipes[1], $pipes[2]];
            if (stream_select($read, $other, $other, 0, $timeout)) {
                foreach ($read as $stream) {
                    if ($stream === $pipes[1]) {
                        $buf .= stream_get_contents($pipes[1], 1024);
                    } else {
                        $err .= stream_get_contents($pipes[1], 1024);
                    }
                }
            }
            $status = proc_get_status($res);
            if (!$status["running"])
                break;
            $now = microtime(true) * 1000;
            usleep(100000);
            $timeout -= $now - $lastTime;
            $lastTime = $now;
            if ($timeout < 0)
                return null;
        }
        proc_terminate($res, 9);
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($res);
        if ($err !== "")
            throw new \Exception($err);
        return $buf;
    }
}