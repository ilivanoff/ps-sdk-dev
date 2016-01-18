<?php

/**
 * Класс строит мост между wordpress и ps sdk, включая в работу последний
 *
 * @author azazello
 */
class PSWPBridge extends AbstractSingleton {

    public function init() {
        PsLogger::inst(__CLASS__)->info(__FUNCTION__);
    }

    public function pluginActivation() {
        PsLogger::inst(__CLASS__)->info(__FUNCTION__);
    }

    public function pluginDeactivation() {
        PsLogger::inst(__CLASS__)->info(__FUNCTION__);
    }

    /** @return PSWPBridge */
    public static function inst() {
        return parent::inst();
    }

}

?>
