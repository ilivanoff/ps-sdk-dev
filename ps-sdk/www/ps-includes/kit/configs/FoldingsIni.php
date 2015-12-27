<?php

/**
 * Класс обрабатывает foldings.ini
 *
 * @author azazello
 */
final class FoldingsIni extends AbstractIni {

    const GROUP_FOLDINGS = 'foldings';

    private static $rel;

    public static function foldingsRel() {
        return isset(self::$rel) ? self::$rel : self::$rel = PsCheck::arr(self::getGroup(self::GROUP_FOLDINGS));
    }

    private static $abs;

    public static function foldingsAbs() {
        return isset(self::$abs) ? self::$abs : self::$abs = DirManager::relToAbs(self::foldingsRel());
    }

}

?>