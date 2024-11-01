; jQuery( document ).ready(function($) {
    var requestParams = {ajaxRequestNonce: UltraCommAdmin.ajaxRequestNonce};
    var ajaxLoader = $('<div class="uc-ajax-loader" style="margin:70px auto;"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div>');
    var notice = $('<p class = "uc-notice" />');
    var generalError = UltraCommAdmin.generalErrorMessage;

    var popupInstance =  $.magnificPopup.instance;

    initEventListeners();

    $('button.btn-uc-form-add-new-field').click(function (evt) {
        evt.preventDefault();
        var clickedButton = $(this);
        popupInstance.open({
            midClick: true,
            mainClass: 'mfp-fade',
            // removalDelay: 10,
            alignTop: true,
            items: {
                src: $('#uc-popup-form-fields-type-list').clone(),
            },
            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();
                },

                close: function () {
                    popupInstance.content.find('.uc-popup-footer').removeClass('uc-hidden');

                },
                afterClose : function () {
                    initEventListeners();
                },
                open: function () {

                    popupInstance.content.find('.uc-popup-footer').addClass('uc-hidden');
                    popupInstance.content.find('.uc-popup-content').empty().append(ajaxLoader);

                    requestParams.action = 'getAllAvailableFormFieldsType';
                    requestParams.formCustomPostId = clickedButton.data('postid');
                    $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                        ajaxLoader.remove();
                        if (typeof resp.success === 'undefined') {
                            notice.addClass('uc-notice-error').text(generalError).appendTo(popupInstance.content.find('.uc-popup-content'));
                            return;
                        }

                        if (!resp.success && resp.data.message) {
                            notice.addClass('uc-notice-error').text(resp.data.message).appendTo(popupInstance.content.find('.uc-popup-content'));
                            return;
                        }

                        popupInstance.content.find('.uc-popup-content').empty().append(resp.data.message);

                        popupInstance.content.find('div.uc-popup-content ul li button').click(function (evt) {
                            evt.preventDefault();
                            $.magnificPopup.instance.content.find('.uc-popup-content').empty().append(ajaxLoader);

                            requestParams.action = 'getFieldTypeSettings';
                            requestParams.formFieldType    = $(this).data('type');
                            requestParams.formCustomPostId = clickedButton.data('postid');
                            requestParams.registerFormCustomPostId  = $(this).data('registerformcustompostid');
                            requestParams.registerFormFieldUniqueId = $(this).data('registerformfielduniqueid');

                            $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {

                                ajaxLoader.remove();
                                if (typeof resp.success === 'undefined') {
                                    notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                                    return;
                                }

                                if (!resp.success && resp.data.message) {
                                    notice.addClass('uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                                    return;
                                }

                                var formContent = $(resp.data.message);
                                handleFormFieldTypeForm(formContent, clickedButton.data('postid'));

                            });

                        });

                    });
                }
            }

        });
    });


    $('button.btn-uc-add-new-profile-header-field').click(function(evt){

        evt.preventDefault();
        var clickedButton = $(this);
        popupInstance.open({
            midClick: true,
            mainClass: 'mfp-fade',
            removalDelay: 100,
            alignTop: true,
            items: {
                src: '<div class="uc-popup-wrapper uc-modal-md"><div class="uch"><div class="uc-g"><div class="uc-u-1-1 uc-popup-title"><h5> </h5></div><div class="uc-u-1-1 uc-popup-content"></div></div></div></div>'
            },
            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();

                },

                close: function () {
                    popupInstance.content.find('.uc-popup-footer').removeClass('uc-hidden');
                },

                afterClose : function () {
                    initEventListeners();
                },

                open: function () {

                    popupInstance.content.find('.uc-popup-footer').addClass('uc-hidden');
                    popupInstance.content.find('.uc-popup-content').empty().append(ajaxLoader);

                    requestParams.action = 'getProfileHeaderFields';
                    requestParams.formCustomPostId = clickedButton.data('postid');

                    $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {

                        ajaxLoader.remove();
                        if (typeof resp.success === 'undefined') {
                            notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                            return;
                        }

                        if (!resp.success && resp.data.message) {
                            notice.addClass('uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                            return;
                        }

                        popupInstance.content.find('.uc-popup-content').empty().append($(resp.data.message));
                        popupInstance.content.find('.uc-popup-content').find('button').click(function(evt){

                            requestParams.action = 'saveHeaderProfileField';
                            requestParams.formCustomPostId = clickedButton.data('postid');
                            requestParams.fieldUniqueId    = $(this).data('fielduniqueid');
                            requestParams.formFieldType    = $(this).data('type');

                            $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {

                                $('#uc-profile-header-fields-list-' + requestParams.formCustomPostId).replaceWith(resp.data.message);

                                notice.hide().fadeIn(500).delay(500).queue(function () {$(this).dequeue();popupInstance.close();});

                            });

                        });

                    });



                }
            }

        });


    });

    function handleFormFieldTypeForm(fieldTypeForm, formCustomPostId)
    {

        popupInstance.contentContainer.find('div.uc-popup-wrapper').removeClass('uc-modal-md uc-modal-lg').addClass('uc-modal-lg');
        popupInstance.content.find('.uc-popup-content').empty().append(fieldTypeForm);
        popupInstance.content.find('.uc-popup-footer').removeClass('uc-hidden');

        var fieldTypeDisplayName = fieldTypeForm.find('#FieldTypeClass').data('popuptitle');
        if(fieldTypeDisplayName){
            popupInstance.content.find('.uc-popup-title h5').html(fieldTypeDisplayName);
        }

        initEventListeners();

        //var fieldIconValue = popupInstance.content.find('input[name = "FontAwesomeIcon"]').val();
        //
        //$('.uc-font-awesome-icons-list').asIconPicker({
        //    tooltip: false
        //});
        //
        //if((typeof fieldIconValue !== 'undefined') && fieldIconValue)
        //{
        //    fieldIconValue.indexOf('fa-') !== 0 ? fieldIconValue = 'fa-' + fieldIconValue : null;
        //    $('.uc-font-awesome-icons-list').asIconPicker('set', fieldIconValue);
        //}
        //else
        //{
        //    $('.uc-font-awesome-icons-list').asIconPicker('clear');
        //}

        var frontEndVisibilityUserRoles = popupInstance.content.find('#FrontEndVisibilityUserRoles');
        var frontEndVisibilityElm       = popupInstance.content.find('select[name = "FrontEndVisibility"]');

        if(frontEndVisibilityUserRoles.length){
            frontEndVisibilityUserRoles.select2({minimumResultsForSearch: -1, dropdownParent: popupInstance.content});
        }

        if(popupInstance.content.find('#NetworkId').length){
            popupInstance.content.find('#NetworkId').select2({minimumResultsForSearch: -1, dropdownParent: popupInstance.content});
        }

        if(popupInstance.content.find('#NetworkId').length){
            popupInstance.content.find('#NetworkId').select2({minimumResultsForSearch: -1, dropdownParent: popupInstance.content});
        }
        if(popupInstance.content.find('#LineStyle').length){
            popupInstance.content.find('#LineStyle').select2({minimumResultsForSearch: -1, dropdownParent: popupInstance.content});
        }
        if(popupInstance.content.find('#TextAlign').length){
            popupInstance.content.find('#TextAlign').select2({minimumResultsForSearch: -1, dropdownParent: popupInstance.content});
        }

        if(popupInstance.content.find('#SocialConfigPostTypeId').length){
            popupInstance.content.find('#SocialConfigPostTypeId').select2({minimumResultsForSearch: -1, dropdownParent: popupInstance.content});
        }

        if(popupInstance.content.find('#SubscriptionLevels').length){
            popupInstance.content.find('#SubscriptionLevels').select2({minimumResultsForSearch: -1, dropdownParent: popupInstance.content});
        }


        if(frontEndVisibilityElm.length){
            frontEndVisibilityElm.select2({minimumResultsForSearch: -1, dropdownParent: popupInstance.content});
            frontEndVisibilityElm.on('change', function(){

                if (!frontEndVisibilityUserRoles.length)
                    return;

                $(this).val() != 4 ?  frontEndVisibilityUserRoles.parent().hide() : frontEndVisibilityUserRoles.parent().show();

            });
        }


        popupInstance.content.find('.uc-popup-footer button').click(function(evt){
            evt.preventDefault();

            //if( $('.uc-font-awesome-icons-list').length){
            //    fieldIconValue = $('.uc-font-awesome-icons-list').asIconPicker('val');
            //
            //    popupInstance.content.find('input[name = "FontAwesomeIcon"]').val(fieldIconValue);
            //}

            requestParams.action            = 'saveFormFieldSettings';
            requestParams.formCustomPostId  = formCustomPostId;
            requestParams.formFieldSettings = fieldTypeForm.serialize();

            $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {

                ajaxLoader.remove();
                if (typeof resp.success === 'undefined') {
                    notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                    return;
                }

                if(!resp.success && resp.data.message){
                    notice.addClass('uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                    return;
                }


                if(resp.success && resp.data.message && resp.data.html){

                    $('#uc-form-fields-list-' + formCustomPostId).replaceWith(resp.data.html);
                    notice.addClass('uc-notice-success').text(resp.data.message).appendTo(popupInstance.content.find('.uc-popup-content'));
                    initEventListeners();
                    notice.hide().fadeIn(500).delay(500).queue(function () {
                        $(this).dequeue();
                        $.magnificPopup.instance.close();});
                }


            });

        });

    }


    function initEventListeners()
    {
        $('.uc-tooltip:not(.tooltipstered)').tooltipster(
            {
                side:"right",
                animation: 'fade',
                theme: 'tooltipster-borderless',
                maxWidth: 250
            }
        );


        $('ul.uc-form-fields-list button.uc-edit-form-field').click(function(evt){
            evt.preventDefault();
            var clickedButton = $(this);

            popupInstance.open({
                midClick: true,
                mainClass: 'mfp-fade',
                removalDelay: 100,
                alignTop:true,
                items: {
                    src: $('#uc-popup-form-fields-type-list').clone(),
                },
                callbacks: {
                    beforeOpen: function () {
                        ajaxLoader.remove();
                    },
                    afterClose : function () {
                        initEventListeners();
                    },

                    open: function () {
                        popupInstance.content.find('.uc-popup-title h5').html(' ');
                        popupInstance.content.find('.uc-popup-footer').addClass('uc-hidden');
                        popupInstance.content.find('.uc-popup-content').empty().append(ajaxLoader);

                        requestParams.action            = 'getFormFieldSettings';
                        requestParams.formCustomPostId  = clickedButton.data('postid');
                        requestParams.formFieldUniqueId = clickedButton.data('fieldid');

                        $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {

                            ajaxLoader.remove();
                            if (typeof resp.success === 'undefined') {
                                notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                                return;
                            }

                            if(!resp.success && resp.data.message){
                                notice.addClass('uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                                return;
                            }

                            var formContent = $(resp.data.message);

                            formContent.find('.uc-fa-simple-icon-picker').iconpicker('.uc-fa-simple-icon-picker');

                            handleFormFieldTypeForm(formContent, clickedButton.data('postid'));

                        });

                    }
                }

            });
        });

        $('ul.uc-form-fields-list button.uc-delete-form-field, ul.uc-form-fields-list button.uc-delete-profile-header-field').click(function(evt){
            evt.preventDefault();
            var clickedButton = $(this);
            popupInstance.open({

                midClick: true,
                mainClass: 'mfp-fade',
                removalDelay: 100,
                items: {
                    src: $('#uc-popup-delete-form-field')
                },
                callbacks: {
                    beforeOpen: function () {
                        ajaxLoader.remove();
                        notice.remove();
                        notice.attr('class', function (i, c) {
                            return c.replace(/(^|\s)uc-notice-\S+/g, '');
                        });
                    },
                    afterClose : function () {
                        initEventListeners();
                    },

                    open: function () {
                        this.content.find('button.uc-button-danger').one('click', function (evt) {
                            popupInstance.close();
                        });

                        this.content.find('button.uc-button-primary').one('click', function (evt) {

                            popupInstance.content.find('div.uc-popup-content').append(ajaxLoader);

                            requestParams.formCustomPostId = clickedButton.data('postid');
                            requestParams.fieldUniqueId    = clickedButton.data('fieldid');
                            requestParams.action = clickedButton.hasClass('uc-delete-profile-header-field') ? 'deleteProfileHeaderField' : 'deleteFormField';

                            $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                                if (typeof resp.success === 'undefined') {
                                    notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                                    return;
                                }

                                if (resp.success && resp.data)
                                {
                                    notice.addClass('uc-notice-success').text(resp.data.message).appendTo(popupInstance.content.find('.uc-popup-content'));

                                    if(requestParams.action == 'deleteFormField')
                                        clickedButton.closest('ul.uc-form-fields-list').find('#'+requestParams.fieldUniqueId + '-' + requestParams.formCustomPostId).remove();
                                     else
                                        clickedButton.closest('ul.uc-form-fields-list').find('#'+requestParams.fieldUniqueId).remove();

                                    notice.hide().fadeIn(500).delay(200).queue(function () {$(this).dequeue();popupInstance.close();});
                                }

                            });

                        });
                    }
                }


            });



        });


        $('.uc-form-fields-list.uc-sortable-list').sortable({
            stop: function (evt, ui) {

                var arrOrderedFields =  $(this).sortable('toArray');
                var ajaxData = {};
                ajaxData['action']      = 'saveFormFieldsOrder';
                ajaxData['ajaxRequestNonce'] = UltraCommAdmin.ajaxRequestNonce;
                ajaxData['formFields'] = JSON.stringify(arrOrderedFields);
                ajaxData['formCustomPostId'] = ui.item.data('formcustompostid');

                $.ajax({
                    type : 'post',
                    cache: false,
                    dataType : "json",
                    data : ajaxData,
                    url : UltraCommAdmin.ajaxUrl,
                    success: function(response){

                        if(response.success && response.data.message){
                            var messageHolder =  ui.item.closest('div.uc-form-fields-list-holder').find('div.uc-form-fields-list-message');
                            messageHolder.removeClass('uc-hidden').find('h4').html(response.data.message);
                            messageHolder.fadeIn(500).delay(1500).fadeOut(1500);
                        }

                    },
                    error : function () {
                    }
                });
            }
        });

        $('.uc-form-fields-list').sortable({
            stop: function (evt, ui) {

                var arrOrderedFields =  $(this).sortable('toArray');
                var ajaxData = {};
                ajaxData['action']      = $(this).prop('id').indexOf('uc-profile-header-fields') == -1 ? 'saveFormFieldsOrder' : 'saveProfileHeaderFieldsOrder';
                ajaxData['ajaxRequestNonce'] = UltraCommAdmin.ajaxRequestNonce;
                ajaxData['formFields'] = JSON.stringify(arrOrderedFields);
                ajaxData['formCustomPostId'] = ui.item.data('formcustompostid');

                $.ajax({
                    type : 'post',
                    cache: false,
                    dataType : "json",
                    data : ajaxData,
                    url : UltraCommAdmin.ajaxUrl,
                    success: function(response){

                        if(response.success && response.data.message){
                            var messageHolder =  ui.item.closest('div.uc-form-fields-list-holder').find('div.uc-form-fields-list-message');
                            messageHolder.removeClass('uc-hidden').find('h4').html(response.data.message);
                            messageHolder.fadeIn(500).delay(1500).fadeOut(1500);
                        }

                    },
                    error : function () {
                    }
                });
            }
        });


    }




});