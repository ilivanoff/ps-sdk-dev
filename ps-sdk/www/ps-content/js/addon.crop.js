$(function () {

    var CropUploadLogger = PsLogger.inst('CropUpload').setTrace();

    var CropCore = {
        //Ширина контейнера
        ContainerWidth: $('.container').width(),
        //Блок с панелью редактирования картинки
        $boxEditor: $('.box-editor'),
        //Холдер для блока редактирования картинки
        $croppHolder: $('.crop-holder'),
        //Верхняя панель кнопок
        $buttonsTop: $('.top-buttons'),
        //Нижняя панель кнопок
        $buttonsBottom: $('.bottom-buttons'),
        //Метод вычисляет высоту холдера для картинки
        calcHolderHeight: function (img) {
            var ratio = this.ContainerWidth / img.info.width;
            if (ratio > 1)
                return img.info.height;//---
            return img.info.height * ratio;
        },
        showError: function (error, img) {
            alert(error);
        },
        
        init: function() {
            this.updateModel = new PsUpdateModel(CropCore, CropCore.umStart, CropCore.umStop);
        },
        
        loadingDiv: null,
        updateModel: null,
        
        umStart: function() {
            this.loadingDiv = loadingMessageDiv().insertAfter(this.$buttonsTop);
        },
        umStop: function() {
            this.loadingDiv.remove();
        }
    }
    
    CropCore.init();

    $('.choose-file-label').button({
        icons: {
            primary: 'ui-icon-folder-open'
        }
    });

    $('.top-buttons .close').button({
        icons: {
            primary: 'ui-icon-closethick'
        }
    }).button('disable');

    $('.bottom-buttons button').button({
        icons: {
            primary: 'ui-icon-mail-closed'
        }
    });

    /*
     * Менеджер кнопки загрузки файла
     */
    var CropUpload = {
        //Кнопка загрузки файла
        $fileSelect: null,
        //Счётчик выбора файлов
        fileSelectCounter: 0,
        //Инициализация менеджера
        init: function () {
            this.$fileSelect = $('#choose-file');

            if (this.$fileSelect.isEmptySet()) {
                CropUploadLogger.logWarn('Не найдена кнопка загрузки картинки Crop');
                return;//----
            }

            //TODO - проверять, поддерживается ли FileApi
            FileAPI.event.on(this.$fileSelect[0], 'change', PsUtil.safeCall(CropUpload.onFileSelected, CropUpload));
        },
        //Метод вызывается при выборе файла в поле выбора
        onFileSelected: function (evt) {
            var files = FileAPI.getFiles(evt); // Retrieve file list

            //Выбраны ли файлы?
            if (!files.length) {
                CropUploadLogger.logWarn('Файл не выбран');
                return;//---
            }

            var readId = ++this.fileSelectCounter;
            var file = files[0];

            CropUploadLogger.logInfo(" >> {}. Выбран файл: '{}' [{}]. Размер: {}.", readId, file.name, file.type, file.size);

            var error = this.validateFile(file);
            if (error) {
                CropUploadLogger.logWarn(" << {}. Файл '{}' не может быть загружен: {}.", readId, file.name, error);
                CropCore.showError(error);
                return;//---
            }

            FileAPI.getInfo(file, function (err, info) {
                error = err ? err : CropUpload.validateFileInfo(file, info);
                if (error) {
                    CropUploadLogger.logWarn(" << {}. Файл '{}' не может быть загружен: {}.", readId, file.name, error);
                    CropCore.showError(error, img);
                } else {
                    var img = {
                        id: readId, //Код загрузки
                        file: file, //Загруженный файл
                        info: info, //Информация об изображении
                        html: null  //Объект HTML
                    };
                    CropUpload.onImgReady(img);
                }
            });
        },
        //Метод выполняет превалидацию файла
        validateFile: function (file) {
            if (!file.size) {
                return 'Пустой файл';
            }
            if (!file.type.startsWith('image/')) {
                return 'Тип файлов [' + file.type + '] не поддерживается';
            }
            return null;//---
        },
        //Метод проверяет выбранный файл - его тип и размер
        validateFileInfo: function (file, info) {
            if (info.width <= 0 || info.height <= 0) {
                return 'Некорректный размер картинки: [' + info.width + 'x' + info.height;
            }
            return null;//---
        },
        //Метод вызывается, когда файл картинки был успешно прочитан
        onImgReady: function (img) {
            CropUploadLogger.logInfo(" << {}. Картинка '{}' принята: {}x{}", img.id, img.file.name, img.info.width, img.info.height);
            CropEditor.load(img);
        }
    }


    /*
     * Менеджер редактора видимой области картинки
     */
    var CropEditor = {
        //Тукущая картирка, загруженная в редактор
        img: null,
        //Редактор
        $cropper: null,
        //Код загрузки
        loadId: 0,
        //Метод загружает картинку в редактор
        load: function (img) {
            FileAPI.Image(img.file).resize(CropCore.ContainerWidth, 600, 'width')
            .get(function (err, canvas) {
                if (err) {
                    CropUploadLogger.logWarn('Ошибка обработки картинки: {}', err);
                    CropCore.showError(err, img);
                } else {
                    img.html = canvas;
                    CropEditor.img = img;
                    CropEditor.initEditor();
                }
            });
        },
        //Метод создаёт редактор для картинки
        initEditor: function () {
            this.startCrop();
        },
        
        //Метод начинает редактирование картинки в crop
        startCrop: function() {
            //Уберём кнопку публикации
            CropCore.$buttonsBottom.hide();
            
            //Сначала закроем текущий редактор
            this.stopCrop();
            
            CropCore.updateModel.start();

            //Покажем редактор
            CropCore.$boxEditor.show();
            
            //Высота редактора должна быть равна высоте картинки
            CropCore.$croppHolder.empty().css('height', CropCore.calcHolderHeight(this.img)).hide().append(this.img.html);
            
            //Код загрузки
            var loadId = ++this.loadId;
            
            //Инициализируем панель
            this.$cropper = $(this.img.html).cropper({
                aspectRatio: 1,
                preview: '.crop-preview',
                responsive: false,
                background: true,
                autoCropArea: 1,
                movable: false,
                zoomable: false,
                viewMode: 1,
                built: function () {
                    CropCore.updateModel.stop();
                    if (loadId == CropEditor.loadId) {
                        CropCore.$croppHolder.show();
                        PsUtil.scheduleDeferred(CropEditor.onCropReady, CropEditor, 20);
                    }
                }
            });
        },
        //Метод закрывает редактор
        stopCrop: function () {
            if (this.$cropper) {
                this.$cropper.cropper('destroy');
                this.$cropper = null;
            }
        },

        //Метод вызывается, когда редактор готов к работе
        onCropReady: function() {
            CropCore.$buttonsBottom.show();
        }
    }

    CropUpload.init();

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
