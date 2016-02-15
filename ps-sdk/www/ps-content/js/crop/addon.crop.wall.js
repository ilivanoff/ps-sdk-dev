$(function () {

    var CropLogger = PsLogger.inst('CropUpload').setTrace();

    var CropCore = {
        //Номер выбора
        selectId: 0,
        nextId: function () {
            return ++this.selectId;
        },
        //Контейнер левой части
        $container: $('.container'),
        //Ширина контейнера
        ContainerWidth: $('.container').width(),
        //Верхняя панель кнопок
        $buttonsTop: $('.container .top-buttons'),
        //Поле выбора файла
        $fileInput: $('input#choose-file'),
        //Поле выбора файла
        $fileInputLabel: $('.container .choose-file-label'),
        //Прогресс
        $progress: $('.container .progress'),
        //Блок для показа ошибки
        $error: $('.container .info_box.warn'),
        //Нижняя панель кнопок
        $buttonsBottom: $('.container .bottom-buttons'),
        //Кнопка отправки сообщения
        $buttonSend: $('.container .bottom-buttons button'),
        //Блок с панелью редактирования картинки
        $cropEditor: $('.crop-editor'),
        //Текст
        $cropText: $('.crop-text'),
        $cropTextArea: $('.crop-text textarea'),
        //Холдер для блока редактирования картинки
        $croppHolder: $('.crop-holder'),
        //Слайдбар переворот
        $rotateSlidebar: $('.crop-menu .rotate'),
        //Фильтры
        $presetFilters: $('#PresetFilters'),
        //Кнопки фильтров
        $presetFiltersA: $('#PresetFilters>a'),
        //Меню редактора
        $cropMenu: $('.crop-menu'),
        //Метод вычисляет высоту холдера для картинки
        calcHolderHeight: function (img) {
            var ratio = this.ContainerWidth / img.info.width;
            if (ratio > 1)
                return img.info.height;//---
            return img.info.height * ratio;
        },
        //Методы работы с ошибкой
        showError: function (error) {
            this.$error.html($.trim(error)).show();
        },
        hideError: function () {
            this.$error.hide();
        },
        //Инициализация ядра
        init: function () {
            this.progress = new PsUpdateModel(this, function (action) {
                if (action !== 'filter') {
                    this.$progress.show()
                }
                this.$fileInputLabel.uiButtonDisable();
                this.$cropTextArea.disable();
                this.$buttonSend.uiButtonDisable();
            }, function (action) {
                if (action !== 'filter') {
                    this.$progress.hide()
                }
                this.$fileInputLabel.uiButtonEnable();
                this.$cropTextArea.enable();
                this.$buttonSend.uiButtonEnable();
            });
        },
        //Прогресс
        progress: null
    }

    CropCore.init();

    // # 1.
    function MosaicMapController() {
        var $div = null;

        var onHide = function () {
            if (!$div)
                return;
            $div.remove();
            $div = null;
        }

        var onShow = function (e, $item) {
            onHide();
            var id = $item.data('id');

            if (!defs.cellowners.hasOwnProperty(id)) {
                return;//---
            }

            var ob = defs.cellowners[id];

            /*
             <div class="mosaic-popup">
             <img class="avatar" src="mmedia/avatars/u/u1/22_42x.jpg"/>
             <div class="content">
             <h5>Имя Пользователя</h5>
             <div class="message">Сообщение</div>
             </div>
             <div class="clearall"></div>
             </div>
             */

            $div = $('<div>').addClass('mosaic-popup');
            $div.append(crIMG(ob.avatar).addClass('avatar'));
            var $content = $('<div>').addClass('content').appendTo($div);
            $content.append($('<h5>').html(ob.name));
            if (ob.msg) {
                $content.append($('<div>').addClass('message').html(ob.msg));
            }
            $div.append($('<div>').addClass('clearall'));
            $div.appendTo('body').width($div.width());
            onUpdate(e);
        }
        var onUpdate = function (e) {
            $div.calculatePosition(e);
        }

        PsJquery.on({
            parent: '#mosaicmap',
            item: 'area',
            mouseenter: onShow,
            mousemove: onUpdate,
            mouseleave: onHide
        });
    }
    // # 1.


    new MosaicMapController();
});
