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
        //Метод вычисляет высоту холдера для картинки
        calcHolderHeight: function (img) {
            var ratio = this.ContainerWidth / img.info.width;
            if (ratio > 1)
                return img.info.height;//---
            return img.info.height * ratio;
        },
        //Методы работы с ошибкой
        showError: function (error) {
            this.$error.text($.trim(error)).show();
        },
        hideError: function () {
            this.$error.hide();
        },
        //Инициализация ядра
        init: function () {
            this.progress = new PsUpdateModel(this.$progress, this.$progress.show, this.$progress.hide);
        },
        //Прогресс
        progress: null
    }

    CropCore.init();

    //Контроллер всех элементов
    var CropController = new function () {

        //Текущая картинка
        var img = null;

        //
        this.isCurrent = function (id) {
            return PsIs.object(img) && img.id == (PsIs.object(id) && id.hasOwnProperty('id') ? id.id : id);
        }

        //Метод закрывает редактор
        this.close = function () {
            //Стираем информацию о текущем изображении
            img = null;
            //Прячем ошибку
            CropCore.hideError();
            //Останавливаем редактирование
            CropEditor.stopCrop();
            //Прячем редактор
            CropCore.$cropEditor.hide();
            //Прячем кнопку публикации
            CropCore.$buttonsBottom.hide();
        }

        //Метод вызывается при возниктовении ошибки
        this.onError = function (error) {
            CropCore.progress.clear();
            CropCore.showError(error);
        }

        //Метод вызывается, когда была выбрана новая картинка
        this.onImgSelected = function (selected) {
            img = selected;
            CropLogger.logInfo('Пользователь выбрал изображение: {}', selected.toString());
            CropEditor.startCrop(selected);
        }

        this.onCropReady = function () {
            //CropCore.$buttonsBottom.show();
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
                                        id: id, //Код загрузки
                                        file: file, //Загруженный файл
                                        info: info, //Информация об изображении
                                        html: canvas, //Объект HTML, по ширине подогнанный для редактора
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

            //Высота редактора должна быть равна высоте картинки
            CropCore.$croppHolder.empty().css('height', CropCore.calcHolderHeight(img)).hide().append(img.html);

            var $cropper = null;

            //Инициализируем панель
            var cropSettings = $.extend({}, this.cropSettings, {
                build: function () {
                    PsUtil.scheduleDeferred(function () {
                        CropCore.progress.stop();

                        if (!this.$cropper && CropController.isCurrent(img)) {
                            this.$cropper = $cropper;
                            CropCore.$croppHolder.show();
                            CropController.onCropReady();
                        } else {
                            $cropper.cropper('destroy');
                            $cropper = null;
                        }

                    }, CropEditor, 20);
                },
            });

            $cropper = $(img.html).cropper(cropSettings);
        },
        //Метод закрывает редактор
        stopCrop: function () {
            if (this.$cropper) {
                this.$cropper.cropper('destroy');
                this.$cropper = null;
            }
        }
    }


    //Стилизуем label
    CropCore.$fileInputLabel.button({
        icons: {
            primary: 'ui-icon-folder-open'
        }
    });

    //Слушатель выбора файла
    CropCore.$fileInput.change(PsUtil.safeCall(FileInput.processSelection, FileInput));

    CropCore.$buttonSend.button({
        icons: {
            primary: 'ui-icon-mail-closed'
        }
    });


    $('#PresetFilters a').clickClbck(function () {
        var disableFilters = this.is('.Active');
        $('#PresetFilters a').removeClass('Active');
        if (disableFilters) {
            //Отключаем фильтры
        } else {
            this.addClass('Active');
            //Включаем фильтры
        }
    });

    return;//---
    /*
     * Сделаем кнопку загрузки файлов
     * TODO - использовать в случае не поддержки FileApi
     */
    /*
     $('#file_upload').psUploadify({
     formData: {
     type: 'Crop'
     },
     onSuccess: function (ok, file) {
     alert('The file ' + file.name + ' was successfully uploaded with a response: ' + ok.path);
     },
     buttonText: 'Загрузить картинку'
     });
     */

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


    var $cropper = $('#image').cropper({
        aspectRatio: 1,
        preview: '.crop-preview, .crop-preview-small',
        responsive: false,
        background: true,
        autoCropArea: 1,
        movable: false,
        zoomable: false,
        viewMode: 1,
        crop: function (data) {

            //$('.container').append($('<img>').attr('src', $cropper.cropper('getCroppedCanvas').toDataURL()));

        },
        built: function () {
            //$cropper.cropper('disable');

            return;//---
            PsUtil.scheduleDeferred(function () {

                $('.container').append($('<img>').attr('src', $cropper.cropper('getCroppedCanvas').toDataURL()));

                AjaxExecutor.executePost('CropUpload', {
                    data: $cropper.cropper('getCroppedCanvas').toDataURL()
                },
                        function (ok) {
                            alert('OK: ' + ok);
                        })

            }, null, 1000);
        }
    });


});
