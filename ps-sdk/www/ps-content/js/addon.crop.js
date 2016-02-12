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
        //Холдер для блока редактирования картинки
        $croppHolder: $('.crop-holder'),
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
                CropEditor.disable();
            }, function (action) {
                if (action !== 'filter') {
                    this.$progress.hide()
                }
                this.$fileInputLabel.uiButtonEnable();
                CropEditor.enable();
            });
        },
        //Прогресс
        progress: null
    }

    CropCore.init();

    //Если браузер не поддерживает FileApi - показываем ошибку и выходим
    if (!PsCore.hasFileApi) {
        CropCore.showError('К сожалению Ваш браузер устарел и не поддерживает FileApi:(');
        return;//---
    }

    //Контроллер всех элементов
    var CropController = new function () {

        //Текущая картинка
        var img = null;

        //Проверка, является ли картинка текущей
        this.isCurrent = function (id) {
            return PsIs.object(img) && img.id == (PsIs.object(id) && id.hasOwnProperty('id') ? id.id : id);
        }

        //Метод закрывает редактор
        this.close = function () {
            //Стираем информацию о текущем изображении
            img = null;
            //Прекращает прогресс
            CropCore.progress.clear();
            //Прячем ошибку
            CropCore.hideError();
            //Останавливаем редактирование
            CropEditor.stopCrop();
            //Прячем редактор
            CropCore.$cropEditor.hide();
            //Прячем кнопку публикации
            CropCore.$buttonsBottom.hide();
            //Отключаем фильтры
            ImageFilters.disable();
        }

        //Метод вызывается при возниктовении ошибки
        this.onError = function (error) {
            this.close();
            CropCore.showError(error);
        }

        //Метод вызывается, когда была выбрана новая картинка
        this.onImgSelected = function (selected) {
            img = selected;
            CropLogger.logInfo('Пользователь выбрал изображение: {}', selected.toString());
            CropEditor.startCrop(selected);
        }

        this.onCropReady = function () {
            ImageFilters.enable()
            //CropCore.$buttonsBottom.show();
        }

        //Применение фильтров
        this.filterApply = function (filter, callback) {

            if (filter) {

            }

            PsUtil.startTimerOnce(callback, 2000);
        }
    }

    //Работа с новым выбранным файлом
    var FileInput = {
        //Обработка выбора
        processSelection: function (evt) {

            var files = FileAPI.getFiles(evt); // Retrieve file list

            //Выбраны ли файлы?
            if (!files.length) {
                CropLogger.logWarn('Файл не выбран');
                return;//---
            }

            CropController.close();

            CropCore.progress.start();

            var id = CropCore.nextId();
            var file = files[0];

            CropLogger.logInfo("$ {}. Выбран файл: '{}' [{}]. Размер: {}.", id, file.name, file.type, file.size);

            var error = this.validateFile(file);
            if (error) {
                CropLogger.logWarn(" ! {}. Файл '{}' не может быть загружен: {}.", id, file.name, error);
                CropController.onError(error);
                return;//---
            }

            FileAPI.getInfo(file, function (err, info) {
                error = err ? err : FileInput.validateFileInfo(info);
                if (error) {
                    CropLogger.logWarn(" ! {}. Файл '{}' не может быть загружен: {}.", id, file.name, error);
                    CropController.onError(error);
                } else {
                    //Подгоним ширину изображения под редактор
                    FileAPI.Image(file).resize(CropCore.ContainerWidth, 600, 'width')
                            .get(function (err, canvas) {
                                if (err) {
                                    CropController.onError('Ошибка обработки изображения: ' + err);
                                } else {
                                    var img = {
                                        id: id,     //Код загрузки
                                        file: file,   //Загруженный файл
                                        info: info,   //Информация об изображении
                                        canvas: canvas, //Объект HTML, по ширине подогнанный для редактора
                                        canvasClone: function () {
                                            return PsCanvas.clone(this.canvas);
                                        },
                                        toString: function () {
                                            return this.id + ".'" + this.file.name + "' [" + this.file.type + "] (" + this.info.width + "x" + this.info.height + ")";
                                        }
                                    };
                                    CropCore.progress.stop();
                                    CropController.onImgSelected(img);
                                }
                            });
                }
            });
        },
        //Метод выполняет превалидацию файла
        validateFile: function (file) {
            if (!file.size) {
                return 'Пустой файл';
            }
            if (!file.type.startsWith('image/')) {
                return 'Данный тип файлов не поддерживается';
            }
            return null;//---
        },
        //Метод проверяет выбранный файл - его тип и размер
        validateFileInfo: function (info) {
            if (!PsIs.object(info) || !PsIs.number(info.width) || !PsIs.number(info.height)) {
                return 'Не удалось получить размер изображения';
            }
            if (info.width <= 0 || info.height <= 0) {
                return 'Некорректный размер изображения: [' + info.width + 'x' + info.height;
            }
            return null;//---
        }
    }

    /*
     * Менеджер редактора видимой области картинки
     */
    var CropEditor = {
        //Редактор
        $cropper: null,
        //Включено ли редактирование
        enabled: true,
        //Настройки редактора
        cropSettings: {
            aspectRatio: 1,
            preview: '.crop-preview',
            responsive: false,
            background: true,
            autoCropArea: 1,
            movable: false,
            zoomable: false,
            viewMode: 1
        },
        //Метод начинает редактирование картинки в crop
        startCrop: function (img) {
            //Сначала закроем текущий редактор
            this.stopCrop();

            //Запускаем прогресс
            CropCore.progress.start();

            //Покажем редактор
            CropCore.$cropEditor.show();

            //Клонируем canvas
            var canvas = img.canvasClone();

            //Высота редактора должна быть равна высоте картинки
            CropCore.$croppHolder.empty().css('height', CropCore.calcHolderHeight(img)).hide().append(canvas);

            var $cropper = null;

            //Инициализируем панель
            var cropSettings = $.extend({}, this.cropSettings, {
                build: function () {
                    PsUtil.scheduleDeferred(function () {
                        CropCore.progress.stop();

                        if (!this.$cropper && CropController.isCurrent(img)) {
                            this.$cropper = $cropper;
                            this.setEnabled(this.enabled);
                            CropCore.$croppHolder.show();
                            CropController.onCropReady();
                        } else {
                            $cropper.cropper('destroy');
                            $cropper = null;
                        }

                    }, CropEditor, 20);
                },
            });

            $cropper = $(canvas).cropper(cropSettings);
        },
        //Метод закрывает редактор
        stopCrop: function () {
            if (this.$cropper) {
                this.$cropper.cropper('destroy');
                this.$cropper = null;
            }
        },
        setEnabled: function (enabled) {
            this.enabled = enabled;
            if (this.$cropper) {
                this.$cropper.cropper(enabled ? 'enable' : 'disable');
            }
        },
        disable: function () {
            this.setEnabled(false);
        },
        enable: function () {
            this.setEnabled(true);
        }
    }

    //Фильтры
    var ImageFilters = {
        init: function () {
            CropCore.$presetFiltersA.clickClbck(function (href, $a) {
                if (CropCore.progress.isStarted() || this.is('.disabled')) {
                    return;//---
                }
                var addFilter = !this.is('.active');
                CropCore.$presetFiltersA.removeClass('active').not($a.toggleClass('active', addFilter)).addClass('disabled');

                CropCore.progress.start('filter');

                //Отключаем фильтры
                CropController.filterApply(addFilter ? null : href, function () {
                    ImageFilters.enable();
                    CropCore.progress.stop();
                });

            });
        },
        disable: function () {
            CropCore.$presetFiltersA.addClass('disabled');
        },
        enable: function () {
            CropCore.$presetFiltersA.removeClass('disabled');
        }
    }

    ImageFilters.init();

    //Показываем меню справа
    CropCore.$cropMenu.setVisibility(true);

    //Стилизуем label
    CropCore.$fileInputLabel.button({
        icons: {
            primary: 'ui-icon-folder-open'
        }
    });

    //Слушатель выбора файла
    CropCore.$fileInput.change(PsUtil.safeCall(FileInput.processSelection, FileInput));

    //Закрываем
    CropController.close();

    //Покажем кнопку загрузки файла
    CropCore.$buttonsTop.show();

    return;//---

    CropCore.$buttonSend.button({
        icons: {
            primary: 'ui-icon-mail-closed'
        }
    });

    $('.crop-upload').clickClbck(function () {
        var canvas = CropEditor.$cropper.cropper('getCroppedCanvas');
        Caman(canvas, function () {
            this.vintage();
            $('.container').append(canvas);
        });

        return;//--

        // Uploading Files - TODO
        FileAPI.upload({
            url: './ctrl.php',
            files: {
                images: []
            },
            progress: function (evt) { /* ... */
            },
            complete: function (err, xhr) { /* ... */
            }
        });
    });

});
