$(function () {
    
    var CropUploadLogger = PsLogger.inst('CropUpload').setTrace();
    /*
     * Менеджер для работы страницы загрузки картинки
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
            this.$fileSelect.change(PsUtil.safeCall(CropUpload.onFileSelected, CropUpload));
        },
        
        //Метод вызывается при выборе файла в поле выбора
        onFileSelected: function() {
            //Выбраны ли файлы?
            if (!this.$fileSelect[0].files.length) {
                CropUploadLogger.logWarn('Файл не выбран');
                return;//---
            }
            
            var readId = ++this.fileSelectCounter;
            var file = this.$fileSelect[0].files[0];
            
            CropUploadLogger.logInfo(" >> {}. Выбран файл: '{}' [{}]. Размер: {}.", readId, file.name, file.type, file.size);

            var error = this.validateSelectedFile(file);
            
            if (error) {
                CropUploadLogger.logWarn(" << {}. Файл '{}' не может быть загружен: {}.", readId, file.name, error);
                alert(error);
            } else {
                CropUploadLogger.logTrace('Начинаем чтение файла...');
                var fileReader = new FileReader();
                fileReader.addEventListener('load', function (evt) {
                    this.removeEventListener('load', this, true);
                    var img = {
                        id: readId, //Код загрузки
                        file: file, //Загруженный файл
                        data: evt.target.result  //Данные из файла
                    };
                    CropUpload.onFileReaded(img);
                }, false);
                fileReader.readAsDataURL(file);
            }
        },
        
        //Метод проверяет выбранный файл - его тип и размер
        validateSelectedFile: function(file) {
            if(!file.size) {
                return 'Пустой файл';
            }
            if(!file.type.startsWith('image/')) {
                return 'Тип файлов ['+file.type+'] не поддерживается';
            }
            return null;//---
        },
        
        //Метод вызывается, когда файл картинки был успешно прочитан
        onFileReaded: function(img) {
            if (img.id == this.fileSelectCounter) {
                CropUploadLogger.logInfo(" << {}. Файл '{}' успешно прочитан, длина data url: {}", img.id, img.file.name, img.data.length);
                //CropUploadLogger.logTrace('Содержимое: {}', dataUrl);
                this.processReadedFile(img);
            } else {
                CropUploadLogger.logWarn(" << {}. Файл '{}' успешно прочитан, но в данный момент загружается другой файл.", img.id, img.file.name);
            }
        },
        
        //Метод вызывается после того, как файл был успешно прочитан - для добавления изображения и перестроения редактора
        processReadedFile: function(img) {
            CropUploadLogger.logTrace('Определяем размер картинки.');
            var ImageObj = new Image();
            ImageObj.onload = function() {
                if (img.id == CropUpload.fileSelectCounter) {
                    img.w = ImageObj.width;
                    img.h = ImageObj.height;
                    CropUploadLogger.logInfo("Размер картинки '{}': {}x{}", img.file.name, img.w, img.h);
                }
            };
            ImageObj.src = img.data;
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
