<?php

/**
 * Класс хранит настройки запуска процесса cron
 *
 * @author azazello
 */
class PsCronProcessConfig {

    private $lastExecuted;

    function __construct($lastExecuted) {
        $this->lastExecuted = $lastExecuted;
    }

    public function getLastExecuted() {
        return $this->lastExecuted;
    }

}

?>
