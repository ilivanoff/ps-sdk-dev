$(function() {
    var $BODY = $('.APTables');
    
    /*
     * Выбор элементов
     */
    var $BUTTONS = $BODY.find('.controls button');

    $BUTTONS.first().button({
        text: true,
        icons: {
            primary: 'ui-icon-disk'
        }
    }).click(function() {
        //Можно импортировать настройки. Запросим подтверждение экспорта
        var $tab = $('#APTables-tab>.tab:visible');
        var type = $tab.data('type');
        PsDialogs.confirm('Вы подтверждаете сохранение для <b>'+type+'</b>.', function() {
            doExport(type, $tab);
        });
    });
    
    $BUTTONS.last().button({
        text: false,
        icons: {
            primary: 'ui-icon-refresh'
        }
    }).click(function() {
        $BUTTONS.uiButtonDisable();
        locationReload();
    });
    
    //Экспорт
    var doExport = function(type, $TAB) {
        $BUTTONS.uiButtonDisable();

        var callAjax = function(action, data) {
            data.scope = type.removeLastCharIf('.ini');
            data.action = action;
            AdminAjaxExecutor.executePost('ConfigFilesSave', data , function() {
                InfoBox.popupSuccess('Настройки успешно сохранены');
                locationReload();
            }, function(err) {
                InfoBox.popupError(err);
                $BUTTONS.uiButtonEnable();
            });
        }

        /*
         * Сохранение содержимого .ini фалов
         */
        if (type.endsWith('.ini')) {
            //Сохраняем ini файл
            callAjax('saveIni', {
                content: $TAB.find('textarea').val()
            });
            return;//---
        }
    }
})