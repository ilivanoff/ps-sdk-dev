<?php

/**
 * Класс строит мост между wordpress и ps sdk, включая в работу классы sdk
 *
 * @author azazello
 */
class PsWpBridge extends AbstractSingleton {

    public function init() {
        PsLogger::inst(__CLASS__)->info(__FUNCTION__);
        PsWpPlugin::addActions();
        add_shortcode('psplugin', array($this, 'psplugin'));
    }

    public function pluginActivation() {
        PsLogger::inst(__CLASS__)->info(__FUNCTION__);
    }

    public function pluginDeactivation() {
        PsLogger::inst(__CLASS__)->info(__FUNCTION__);
    }

    public function psplugin() {
        $src = TexImager::inst()->getImgDi('\sin(\alpha+\beta)')->getRelPath();
        return "Ps plugin included. <img src='$src'/>";
    }

    /** @return PsWpBridge */
    public static function inst() {
        return parent::inst();
    }

}

?>