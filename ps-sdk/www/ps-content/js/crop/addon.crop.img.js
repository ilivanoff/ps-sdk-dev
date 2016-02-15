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
                CropEditor.setEnabled(false);
            }, function (action) {
                if (action !== 'filter') {
                    this.$progress.hide()
                }
                this.$fileInputLabel.uiButtonEnable();
                this.$cropTextArea.enable();
                this.$buttonSend.uiButtonEnable();
                CropEditor.setEnabled(true);
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
            //Тукст для ввода сообщения
            CropCore.$cropText.hide();
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
            ImageFilters.enable();
            CropCore.$cropText.show();
            CropCore.$buttonsBottom.show();
        }

        //Применение фильтров
        this.filterApply = function (callback) {
            CropEditor.startCrop(img, callback, true);
        }

        //Сабмит формы
        this.submit = function (text) {
            CropLogger.logInfo("Submitting {} with text: '{}'", img.toString(), text);

            CropCore.progress.start();

            AjaxExecutor.executePost('CropUpload', {
                //img: img.file,
                imgo: img.canvas.toDataURL(),
                imgf: CropEditor.getImgCanvas().toDataURL(),
                imgc: CropEditor.getCropCanvas().toDataURL()
            },
                    function (ok) {
                    }, function (err) {
                CropCore.showError(err);
            }, function () {
                CropCore.progress.stop();
            });
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
        //Объект {$cropper, $holder}
        crop: null,
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
                    /*
                     ,crop: function(e) {
                     $('.crop-preview').empty().each(function() {
                     $(this).append($(e.target).cropper('getCroppedCanvas'));
                     });
                     }*/
        },
        //Метод начинает редактирование картинки в crop
        startCrop: function (img, onDone, rebuild) {

            //Запускаем прогресс
            CropCore.progress.start();

            //Клонируем canvas
            var canvas = img.canvasClone();

            //Если есть фильтр - применим его
            var filter = ImageFilters.filter();

            //Обезопасим функцию обратного вызова
            onDone = PsUtil.safeCall(onDone);

            //У нас может быть старый crop, с которого копируются настройки
            var cropOld = null;

            //Перестраиваем? Тогда сохраним старый crop, с которого скопируем потом настройки
            if (rebuild) {
                cropOld = this.crop;
                //cropOld.setEnabled(false);
            } else {
                //Уничтожаем текущий crop
                this.stopCrop();
            }

            //Инициализируем новый
            var cropNew = {
                canvas: canvas,
                $cropper: null,
                $holder: $('<div>').addClass('crop-holder').hide().appendTo(CropCore.$cropEditor).css('height', CropCore.calcHolderHeight(img)).append(canvas),
                destroy: function () {
                    if (this.$cropper) {
                        this.$cropper.cropper('destroy');
                    }
                    this.$holder.remove();
                },
                setEnabled: function (enabled) {
                    if (this.$cropper) {
                        this.$cropper.cropper(enabled ? 'enable' : 'disable');
                    }
                }
            };

            //Покажем редактор
            CropCore.$cropEditor.show();

            var onCanvasReady = function () {
                //Инициализируем панель
                var cropSettings = $.extend({}, CropEditor.cropSettings, {
                    cropBoxData: cropOld ? cropOld.$cropper.cropper('getCropBoxData') : null,
                    built: function () {
                        PsUtil.scheduleDeferred(function () {
                            CropCore.progress.stop();

                            if (CropController.isCurrent(img)) {
                                this.stopCrop();
                                this.crop = cropNew;
                                this.crop.setEnabled(CropEditor.enabled);
                                this.crop.$holder.show();
                                onDone();
                                CropController.onCropReady();
                            } else {
                                cropNew.destroy();
                                onDone();
                            }

                        }, CropEditor);
                    }
                });

                cropNew.$cropper = $(canvas).cropper(cropSettings);
            }

            //Применяем фильтр
            if (filter) {
                Caman(canvas, function () {
                    if (CropController.isCurrent(img)) {
                        this[filter]();
                        this.render(onCanvasReady);
                    }
                });
            } else {
                onCanvasReady();
            }
        },
        //Метод закрывает редактор
        stopCrop: function () {
            if (this.crop) {
                this.crop.destroy();
                this.crop = null;
            }
        },
        //Включает/отключает перетастивание
        setEnabled: function (enabled) {
            this.enabled = enabled;
            if (this.crop) {
                this.crop.setEnabled(enabled);
            }
        },
        //Метод получает canvas с данными
        getCropCanvas: function () {
            return this.crop.$cropper.cropper('getCroppedCanvas');
        },
        //Метод получает canvas с данными
        getImgCanvas: function () {
            return this.crop.canvas;
        }
    }

    //Фильтры
    var ImageFilters = {
        init: function () {
            CropCore.$presetFiltersA.clickClbck(function () {
                if (CropCore.progress.isStarted() || this.is('.disabled')) {
                    return;//---
                }
                CropCore.$presetFiltersA.not(this.toggleClass('active')).removeClass('active').addClass('disabled');

                CropCore.progress.start('filter');

                //Отключаем фильтры
                CropController.filterApply(function () {
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
        },
        filter: function () {
            return getHrefAnchor(CropCore.$presetFiltersA.filter('.active'));
        }
    }

    ImageFilters.init();

    //Трансформация картинки
    /*
     var ImageTransform = {
     init: function() {
     CropCore.$rotateSlidebar.slider({
     value: 60,
     orientation: 'horizontal',
     min: 0,
     max: 360,
     range: 'min',
     animate: true
     })
     }
     }
     
     ImageTransform.init();
     */



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

    //Кнопка отправки сообщения
    CropCore.$buttonSend.button({
        icons: {
            primary: 'ui-icon-mail-closed'
        }
    }).click(function () {
        var text = CropCore.$cropTextArea.val();
        if (PsIs.empty(text)) {
            CropCore.$cropTextArea.focus();
            return;//---
        }
        CropController.submit(text);
    });

    return;//---

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
