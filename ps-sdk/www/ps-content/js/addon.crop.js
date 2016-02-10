$(function () {
    
    var CropUploadLogger = PsLogger.inst('CropUpload').setTrace();
    
    var CropCore = {
        ContainerWidth: $('.container').width(),
        
        //Метод вычисляет высоту холдера для картинки
        calcHolderHeight: function(img) {
            var ratio = this.ContainerWidth / img.info.width;
            if (ratio>1) return img.info.height;//---
            return img.info.height * ratio;
        },
        
        showError: function(error, img) {
            alert(error);

        }
    }
    
    /*
     * Менеджер кнопки загрузки файла
     */
    var CropUpload = {
        //Кнопка загрузки файла
        $fileSelect: null,
        //Счётчик выбора файлов
        fileSelectCounter: 0,
        //Инициализация менеджера
        init: function() {
            this.$fileSelect = $('#choose-file');
            
            if (this.$fileSelect.isEmptySet()) {
                CropUploadLogger.logWarn('Не найдена кнопка загрузки картинки Crop');
                return;//----
            }
            
            //TODO - проверять, поддерживается ли FileApi
            FileAPI.event.on(this.$fileSelect[0], 'change', PsUtil.safeCall(CropUpload.onFileSelected, CropUpload));
        },
        
        //Метод вызывается при выборе файла в поле выбора
        onFileSelected: function(evt) {
            var files = FileAPI.getFiles(evt); // Retrieve file list

            //Выбраны ли файлы?
            if (!files.length) {
                CropUploadLogger.logWarn('Файл не выбран');
                return;//---
            }
            
            var readId = ++this.fileSelectCounter;
            var file = files[0];
            
            CropUploadLogger.logInfo(" >> {}. Выбран файл: '{}' [{}]. Размер: {}.", readId, file.name, file.type, file.size);

            FileAPI.getInfo(file, function(err, info) {
                var error = err ? err : CropUpload.validateSelectedFile(file, info);
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
        
        //Метод проверяет выбранный файл - его тип и размер
        validateSelectedFile: function(file, info) {
            if(!file.size) {
                return 'Пустой файл';
            }
            if(!file.type.startsWith('image/')) {
                return 'Тип файлов ['+file.type+'] не поддерживается';
            }
            if (info.width<=0 || info.height<=0) {
                return 'Некорректный размер картинки: ['+info.width+'x'+info.height;
            }
            return null;//---
        },
        
        //Метод вызывается, когда файл картинки был успешно прочитан
        onImgReady: function(img) {
            CropUploadLogger.logInfo(" << {}. Картинка '{}' принята: {}x{}", img.id, img.file.name, img.info.width, img.info.height);
            CropEditor.load(img);
        }
    }
    
    
    /*
     * Менеджер редактора видимой области картинки
     */
    var CropEditor = {
        //Холдер
        $croppHolder: $('.crop-holder'),
        //Редактор
        $cropper: null,
        //Метод загружает картинку в редактор
        load: function(img) {
            
            FileAPI.Image(img.file)
            .resize(CropCore.ContainerWidth, 600, 'width')
            //.filter('Lomo')
            .get(function (err, canvas){
                if (err) {
                    CropUploadLogger.logWarn('Ошибка обработки картинки: {}', err);
                    CropCore.showError(err, img);
                } else {
                    img.html = canvas;
                    CropEditor.makeCrop(img);
                }
            });
        },
        
        //Метод создаёт редактор для картинки
        makeCrop: function(img) {
            //Сначала закроем
            this.close();
            
            $('.crop-upload').clickClbck(function() {
                // Uploading Files - TODO
                FileAPI.upload({
                    url: './ctrl.php',
                    files: {
                        images: []
                    },
                    progress: function (evt){ /* ... */ },
                    complete: function (err, xhr){ /* ... */ }
                });
            });
            
            this.$croppHolder.empty().css('height', CropCore.calcHolderHeight(img)).append(img.html);
            
            //this.$cropper = $('<img>').attr('src', img.html.toDataURL()).appendTo();
            $(img.html).cropper({
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
            
        },
        
        //Метод закрывает редактор
        close: function() {
            if (this.$cropper) {
                this.$cropper.cropper('destroy');
                this.$cropper = null;
            }
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
