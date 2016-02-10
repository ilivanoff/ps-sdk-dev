$(function () {
    //Сделаем кнопку загрузки файлов
    $('#file_upload').psUploadify({
        formData: {
            type: 'Crop'
        },
        onSuccess: function (ok, file) {
            alert('The file ' + file.name + ' was successfully uploaded with a response: ' + ok.path);
        },
        buttonText: 'Загрузить картинку'
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

            $('.container').append($('<img>').attr('src', $cropper.cropper('getCroppedCanvas').toDataURL()));

        },
        built: function () {
            //$cropper.cropper('disable');

            PsUtil.scheduleDeferred(function () {

                $('.container').append($('<img>').attr('src', $cropper.cropper('getCroppedCanvas').toDataURL()));

                AjaxExecutor.execute('CropUpload', {
                    data: $cropper.cropper('getCroppedCanvas').toDataURL()
                },
                        function (ok) {
                            alert('OK: ' + ok);
                        })

            }, null, 1000);
        }
    });


});
