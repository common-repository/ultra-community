jQuery( document ).ready(function($){

    var requestAdditionalParams = {action : '', profiledUserSlug: UltraComm.profiledUserSlug, ajaxRequestNonce : UltraComm.ajaxRequestNonce};
    var ajaxLoader = $('<div class="uc-ajax-loader"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div>');
    var notice = $('<p class = "uc-notice" />');

    $('.uc-tooltip:not(.tooltipstered)').tooltipster(
        {
            side:"bottom",
            animation: 'fade',
            theme: 'tooltipster-borderless',
            maxWidth: 250
        }
    );

    $('.uc-tooltip-group-type:not(.tooltipstered)').tooltipster(
        {
            side:"right",
            animation: 'fade',
            theme: 'tooltipster-shadow',
            contentAsHTML: true,
            maxWidth: 755, interactive: true // ,trigger: 'click'
        }
    );

    $('#ultracomm-edit-profile-form').submit(function(evt){
        evt.preventDefault();
        var frmElement = $(this);
        requestAdditionalParams.action = 'updateUserProfile';
        var noticeElm =   frmElement.find('p.uc-notice').removeClass('uc-notice-error').removeClass('uc-notice-success').attr('style', '');

        noticeElm.parent().toggleClass('uc-hidden', true);

        frmElement.find('div.uc-ajax-loader').toggleClass('uc-hidden', false);

        frmElement.find('textarea[id^="ultracommeditor"]').each(function () {
            tinyMCE.get($(this).prop('id')) ? $(this).val(tinyMCE.get($(this).prop('id')).getContent()) : null;
        });


        var requestData = frmElement.serialize() + '&' + $.param(requestAdditionalParams);
        $.post(UltraComm.ajaxUrl, requestData).always(function( resp ) {

            frmElement.find('div.uc-ajax-loader').toggleClass('uc-hidden', true);
            noticeElm.parent().removeClass('uc-hidden');
            if(!resp.success){
                noticeElm.addClass('uc-notice-error').text(resp.data.message);
                return;
            }

            noticeElm.addClass('uc-notice-success').text(resp.data.message).delay(1000).fadeOut(1500).queue(function () {$(this).dequeue(); noticeElm.parent().addClass('uc-hidden');});

        });

    });


    $('#ultracomm-account-general-settings-form').submit(function(evt){
        evt.preventDefault();
        var frmElement = $(this);
        requestAdditionalParams.action = 'updateUserAccountSettings';
        var noticeElm =   frmElement.find('p.uc-notice').removeClass('uc-notice-error').removeClass('uc-notice-success').attr('style', '');

        noticeElm.parent().toggleClass('uc-hidden', true);

        frmElement.find('div.uc-ajax-loader').toggleClass('uc-hidden', false);

        var requestData = frmElement.serialize() + '&' + $.param(requestAdditionalParams);
        $.post(UltraComm.ajaxUrl, requestData).always(function( resp ) {

            frmElement.find('div.uc-ajax-loader').toggleClass('uc-hidden', true);
            noticeElm.parent().removeClass('uc-hidden');
            if(!resp.success){
                noticeElm.addClass('uc-notice-error').text(resp.data.message);
                return;
            }

            noticeElm.addClass('uc-notice-success').text(resp.data.message).delay(1000).fadeOut(1500).queue(function () {$(this).dequeue(); noticeElm.parent().addClass('uc-hidden');});

        });
    });

    $('#ultracomm-account-change-password-form').submit(function(evt){
        evt.preventDefault();
        var frmElement = $(this);
        requestAdditionalParams.action = 'updateUserPassword';
        var noticeElm =   frmElement.find('p.uc-notice').removeClass('uc-notice-error').removeClass('uc-notice-success').attr('style', '');

        noticeElm.parent().toggleClass('uc-hidden', true);

        frmElement.find('div.uc-ajax-loader').toggleClass('uc-hidden', false);

        var requestData = frmElement.serialize() + '&' + $.param(requestAdditionalParams);
        $.post(UltraComm.ajaxUrl, requestData).always(function( resp ) {

            frmElement.find('div.uc-ajax-loader').toggleClass('uc-hidden', true);
            noticeElm.parent().removeClass('uc-hidden');
            if(!resp.success){
                noticeElm.addClass('uc-notice-error').text(resp.data.message);
                return;
            }

            noticeElm.addClass('uc-notice-success').text(resp.data.message).delay(1000).fadeOut(1500).queue(function () {$(this).dequeue(); noticeElm.parent().addClass('uc-hidden');});

        });

    });

    $('#ultracomm-groups-create-group-form').submit(function(evt){
        evt.preventDefault();
        var frmElement = $(this);
        requestAdditionalParams.action = 'saveUserGroup';
        var noticeElm =   frmElement.find('p.uc-notice').removeClass('uc-notice-error').removeClass('uc-notice-success').attr('style', '');

        noticeElm.parent().toggleClass('uc-hidden', true);

        frmElement.find('div.uc-ajax-loader').toggleClass('uc-hidden', false);

        var requestData = frmElement.serialize() + '&' + $.param(requestAdditionalParams);
        $.post(UltraComm.ajaxUrl, requestData).always(function( resp ) {

            frmElement.find('div.uc-ajax-loader').toggleClass('uc-hidden', true);
            noticeElm.parent().removeClass('uc-hidden');
            if(!resp.success){
                noticeElm.addClass('uc-notice-error').text(resp.data.message);
                return;
            }

            noticeElm.addClass('uc-notice-success').text(resp.data.message).delay(1000).fadeOut(1500).queue(function () {$(this).dequeue(); noticeElm.parent().addClass('uc-hidden');});

        });

    });


    $('a[id^="btn-uc-action-delete-group"]').magnificPopup({

        midClick: true,
        mainClass: 'mfp-fade',
        removalDelay: 500,
        type:'inline',

        callbacks : {
            beforeOpen : function(){ajaxLoader.remove(); notice.remove(); notice.attr('class',function(i, c){return c.replace(/(^|\s)uc-notice-\S+/g, '');});},
            open : function(){
                this.content.find('button.uc-button-danger').one('click', function(evt){$.magnificPopup.instance.close();});


                this.content.find('button.uc-button-primary').one('click', function(evt){

                    $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);

                    requestAdditionalParams.action  = 'deleteUserGroup';
                    requestAdditionalParams.groupId = $.magnificPopup.instance.st.el.data('groupid');

                    $.post(UltraComm.ajaxUrl, requestAdditionalParams).always(function( resp ) {
                        ajaxLoader.remove();

                        notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                        if (resp.success)
                        {
                            notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                $(this).dequeue();
                                $.magnificPopup.instance.close();
                                $.magnificPopup.instance.st.el.closest('div.uc-content-list-holder').addClass('uc-hidden').fadeIn(1000).delay(2000).queue(function (){$(this).dequeue();});
                            });
                        }

                    });

                });

            }

        }



    }).attr('data-mfp-src', '#uc-confirmation-popup-delete-group');






    $('#uc-delete-avatar-button').click(function(evt){
        evt.preventDefault();
        requestAdditionalParams.action = 'deleteUserAvatar';
        $.post(UltraComm.ajaxUrl, requestAdditionalParams).always(function( resp ) {
            if(!resp.success){
                return;
            }
            $('#uc-user-avatar').prop('src', resp.data.userAvatarUrl);
        });
    });

    $('#uc-delete-cover-button').click(function(evt){
        evt.preventDefault();
        requestAdditionalParams.action = 'deleteUserCover';
        $.post(UltraComm.ajaxUrl, requestAdditionalParams).always(function( resp ) {
            if(!resp.success){
                return;
            }
            $('#uc-user-cover').prop('src', resp.data.userCoverUrl);
        });
    });


    var uploaderAdditionalData = {
        uploadRequestNonce : UltraComm.uploadRequestNonce,
        profiledUserSlug : UltraComm.profiledUserSlug,
        action : ''
    };

    $('#uc-avatar-fileupload').fileupload({
        dataType: 'json',
        autoUpload: false,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        maxFileSize: 999000,
        disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
        previewMaxWidth: 1200,
        previewMaxHeight: 1200,
        imageMinWidth: 200,
        imageMinHeight: 200,
        imageMaxWidth: 500,
        imageMaxHeight: 500,
        previewThumbnail: false,
        previewCanvas : true,
        imageCrop:true
    }).on('fileuploadadd', function (e, data) {

        $('.uc-progress-bar .uc-progress').css('width','0').css('display', 'none');

        uploaderAdditionalData.action = 'uc-uploadProfileAvatar';

    }).on('fileuploaddone', function (e, data) {
        $.each(data.result.files, function (index, file) {
            if (file.url) {
                $('#uc-user-avatar').prop('src', file.url);
                $.jAlert('current').closeAlert();
            }

            else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('.uc-progress-bar .uc-progress').css('width', progress + '%').css('display', 'block');
    }).on('fileuploadprocessalways', function (e, data) {

        var index = data.index,
            file = data.files[index];

        if(!file.preview)
            return;

        $.jAlert({
            'title':'Upload Avatar',
            'size': {'width': "'" + file.preview.clientWidth  + "px'", 'height': "'" + file.preview.clientHeight  + "px'"},
            'content' : '<div class="uch uc-alert"><div class="uc-g"><div class="uc-u-1"><div id="uc-modal-upload-holder"></div><div class="uc-progress-bar"><span class="uc-bar"><span class="uc-progress"></span></span></div></div></div></div>',

            onOpen: function(alertObj){
                data.context = $('#uc-modal-upload-holder');
                var index = data.index,
                    file = data.files[index];

                data.context.append(file.preview);

                var imgNode = data.context.find('canvas, img');

                $(imgNode[0]).cropper({
                        dragCrop: false,
                        aspectRatio: 1.0,
                        zoomable: false,
                        rotatable: false,
                        minWidth : 200,
                        done: function(data){

                            uploaderAdditionalData.cropX      = Math.round(data.x);
                            uploaderAdditionalData.cropY      = Math.round(data.y);
                            uploaderAdditionalData.cropWidth  = Math.round(data.width);
                            uploaderAdditionalData.cropHeight = Math.round(data.height);

                        }

                    });

                $.jAlert('current').centerAlert();
            },

            btns : [
                {
                    'text': 'Start Upload',
                    'closeAlert': false,
                    'class': 'uc-button uc-button-primary',
                    'onClick': function (evt, btn) {
                        evt.preventDefault();
                        data.formData = uploaderAdditionalData;
                        data.submit();

                    }
                }

            ]

        });

    });




    $('#uc-cover-fileupload').fileupload({
        dataType: 'json',
        autoUpload: false,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        maxFileSize: 999000,
        disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
        previewMaxWidth: 1200,
        previewMaxHeight: 500,
        imageMinWidth: 1000,
        //imageMinHeight: 1000,
        imageMaxWidth: 1200,
        imageMaxHeight: 400,
        previewThumbnail: false,
        previewCanvas : true,
        imageCrop:true
    }).on('fileuploadadd', function (e, data) {

        $('.uc-progress-bar .uc-progress').css('width','0').css('display', 'none');

        uploaderAdditionalData.action = 'uc-uploadProfileCover';

    }).on('fileuploaddone', function (e, data) {
        $.each(data.result.files, function (index, file) {
            if (file.url) {
                $('#uc-user-cover').prop('src', file.url);
                $.jAlert('current').closeAlert();
            }

            else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('.uc-progress-bar .uc-progress').css('width', progress + '%').css('display', 'block');
    }).on('fileuploadprocessalways', function (e, data) {

        var index = data.index,
            file = data.files[index];

        if(!file.preview)
            return;

        $.jAlert({
            'title':'Upload Cover Photo',
            'size': {'width': "'" + file.preview.clientWidth  + "px'", 'height': "'" + file.preview.clientHeight  + "px'"},
            'content' : '<div class="uch uc-alert"><div class="uc-g"><div class="uc-u-1"><div id="uc-modal-upload-holder"></div><div class="uc-progress-bar"><span class="uc-bar"><span class="uc-progress"></span></span></div></div></div></div>',

            onOpen: function(alertObj){
                data.context = $('#uc-modal-upload-holder');
                var index = data.index,
                    file = data.files[index];

                data.context.append(file.preview);

                var imgNode = data.context.find('canvas, img');

                $(imgNode[0]).cropper({
                    dragCrop: false,
                    //aspectRatio: 1.0,
                    zoomable: false,
                    rotatable: false,
                    minWidth : 1000,
                    maxWidth : 1200,
                    maxHeight : 400,
                    done: function(data){

                        uploaderAdditionalData.cropX      = Math.round(data.x);
                        uploaderAdditionalData.cropY      = Math.round(data.y);
                        uploaderAdditionalData.cropWidth  = Math.round(data.width);
                        uploaderAdditionalData.cropHeight = Math.round(data.height);

                    }

                });

                $.jAlert('current').centerAlert();
            },

            btns : [
                {
                    'text': 'Start Upload',
                    'closeAlert': false,
                    'class': 'uc-button uc-button-primary',
                    'onClick': function (evt, btn) {
                        evt.preventDefault();
                        data.formData = uploaderAdditionalData;
                        data.submit();

                    }
                }

            ]

        });

    });



});
