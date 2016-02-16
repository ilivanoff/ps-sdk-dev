/**
 * Менеджер открывает popup - окна.
 * pageIdent может быть задан в виде:
 * 1) /ps-includes/ps-popup.php?window=Plugin&ident=atom
 * 2) Plugin?ident=atom
 * Если он пуст, то откроется базовая страница ps-popup.php
 */
var popupWindowManager = {
    base: '/ps-includes/ps-popup.php',
    windowWidth: /*635*/720,
    windowHeight: 770,
    logger: PsLogger.inst('PopupWindowManager').setDebug()/*.disable()*/,
    init: function () {

        PsJquery.on({
            ctxt: this,
            item: '[pageIdent]',
            data: {
                progress: false
            },
            click: function (e, $btn, data) {
                //Не забываем, что могут быть не только ссылки, но и кнопки
                e.preventDefault();
                //Проверим, не кликает ли пользователь просто так
                if (data.progress) {
                    this.logger.logDebug('Потоплено событие открытия окна');
                    return;//---
                }
                data.progress = true;
                //Открываем окно
                this.openWindow($btn.attr('pageIdent'));
                //Не может пользователь кликнуть осознанно так быстро...
                PsUtil.startTimerOnce(function () {
                    data.progress = false;
                }, 5000);

            }
        });
    },
    windowFeatures: function () {
        return 'status=no,toolbar=no,menubar=no,scrollbars=yes' +
                ',width=' + this.windowWidth +
                ',height=' + this.windowHeight +
                ',left=' + ((screen.width - this.windowWidth) / 2) +
                ',top=' + ((screen.height - this.windowHeight) / 2 - 20);
    },
    openWindow: function (pageIdent, paramsObj, paramsStr) {
        pageIdent = pageIdent || '';
        paramsObj = paramsObj || {};
        paramsStr = paramsStr || '';
        this.logger.logInfo('Открываем всплывающее окно. Идентификатор: [{}], параметры в объекте: [{}], параметры в строке: [{}].', pageIdent, PsObjects.toString(paramsObj), paramsStr);

        var ident = '', params = '';

        var parts = pageIdent.split('?');
        switch (parts.length) {
            case 0:
                //Пустой идентификатор
                break;
            case 1:
                //Идентификатор или параметры
                if (parts[0].contains('=')) {
                    //Параметры
                    params = parts[0];
                } else {
                    //Идентификатор
                    ident = parts[0];
                }
                break;
            default:
                ident = parts[0];
                params = parts[1];
                break;
        }

        if (ident.toLowerCase() == this.base) {
            ident = '';
        }

        //Строим параметры
        var OB = {};
        if (ident) {
            OB[defs.POPUP_WINDOW_PARAM] = ident;
        }
        $.extend(OB, paramsObj || {});
        $.extend(OB, PsUrl.getParams2Obj(paramsStr));
        $.extend(OB, PsUrl.getParams2Obj(params));

        var get = PsUrl.obj2getParams(OB);
        var url = this.base + (get ? '?' : '') + get;
        var winIdent = MD5(url);
        window.open(url, winIdent, this.windowFeatures()).focus();
        this.logger.logInfo('Конечный url: [{}], идетификатор окна: [{}]', url, winIdent);
    }
}



/**
 * Менеджер для работы с TeX формулами
 */
var MathJaxManager = {
    init: function () {
        //1. Кнопка, всплывающая над формулами и позволяющая перейти к её редактированию
        PsBubble.registerBubbleStick('.TeX:not(.TeX-no-tooltip .TeX)', function (onDone, $href) {
            var hash = MathJaxManager.getTexHash($href);

            var $button = $('<button>').attr('title', 'Загрузить формулу в редактор').attr('type', 'button').addClass('imaged');
            $button.append(crIMG(CONST.IMG_FORMULA));
            $button.click(function () {
                popupWindowManager.openWindow('formula', {
                    hash: hash
                });
            });

            onDone($('<div>').addClass('tex_ctrl').append($button));
        });
    },
    isEnabled: function () {
        return typeof (window.MathJax) != 'undefined';
    },
    updateFormules: function () {
        if (this.isEnabled()) {
            window.MathJax.Hub.Typeset();
        }
    },
    getTexHash: function ($el) {
        var hash;
        if ($el.is('img')) {
            hash = $el.attr('src');
            hash = getStringEnd(hash, '/', true);
            hash = getStringStart(hash, '.', false);
        } else {
            hash = $el.data('tex');
        }
        return hash;
    },
    /*
     * 
     */
    decoded: {},
    loading: {},
    waiting: {},
    decodeJax: function (hash, callback) {
        hash = $.trim(hash);
        var key = 'f' + hash;

        if (this.decoded.hasOwnProperty(key)) {
            callback.call(this.decoded[key], this.decoded[key]);
            return;//---
        }

        if (!this.waiting.hasOwnProperty(key)) {
            this.waiting[key] = [];
        }
        this.waiting[key].push(callback);

        if (this.loading.hasOwnProperty(key)) {
            return;//---
        }
        this.loading[key] = true;

        AjaxExecutor.execute('TexDecode', {
            ctxt: this,
            hash: hash
        },
                function (tex) {
                    this.decoded[key] = tex;
                    return tex;
                },
                function (err) {
                    InfoBox.popupError(err);
                    this.decoded[key] = null;
                    return null;
                },
                function (tex) {
                    $.each(this.waiting[key], function (num, fn) {
                        fn.call(tex, tex);
                    });

                    delete this.waiting[key];
                });
    }
}

/**
 * Менеджер, отвечающий за позиционирование номеров формул по центру блоков
 */
var TexFormules = {
    init: function () {
        PsJquery.executeOnElVisible('.formula .num', function ($num) {
            var $formula = $num.extractParent('.formula');
            var nh = $num.height();
            var nf = $formula.height();
            //Приподнимем формулу, так как различные стрелочки над векторами могут вылезать из разметки
            var top = Math.floor((nf - nh) / 2) - 2;
            if (top > 0) {
                $num.css('top', top + 'px');
            }
        });
    }
}

jQuery(function () {
    popupWindowManager.init();

    MathJaxManager.init();

    TexFormules.init();
});