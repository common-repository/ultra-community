; jQuery( document ).ready(function($) {
    var requestParams = {ajaxRequestNonce : UltraCommAdmin.ajaxRequestNonce};


    var ajaxLoader = $('<div class="uc-ajax-loader"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div>');
    var notice = $('<p class = "uc-notice" />');
    var generalError = UltraCommAdmin.generalErrorMessage;

    $('.uc-color-picker').wpColorPicker();
    $('.uc-select2').select2({minimumResultsForSearch: -1});
    $('select.uc-select2-multiple').select2({});


    $('.uc-fa-simple-icon-picker').iconpicker('.uc-fa-simple-icon-picker');


    $('#uc-generalappearance-settings-PageColorScheme').length && $('#uc-generalappearance-settings-PageColorScheme').select2({
        minimumResultsForSearch: -1,
        templateSelection: renderPageColorScheme,
        templateResult   : renderPageColorScheme,
        escapeMarkup: function(markup) {
            return markup;
        }
    });


    function renderPageColorScheme(scheme)
    {
        if(!scheme.id)return scheme.text;

        var hexColor = null;
        switch (scheme.id)
        {
            case 'blue'   : hexColor = '#33b5e5'; break;
            case 'grey'   : hexColor = '#6b6b6b'; break;
            case 'purple' : hexColor = '#ab6bc6'; break;
            case 'green'  : hexColor = '#1bc0a0'; break;

        }

        if(null === hexColor){
            return scheme.text;
        }

        return $('<span style="display:inline-block;width:20px; height:20px;margin:5px 10px 0 0; background:'+hexColor+'"></span><span style="vertical-align:super">' + scheme.text  + '</span>');

    }

    function selectionHandler(e){
        var $select2   = $(this);
        var text       = e.params.data.text;
        var order      = $select2.data('preserved-order') || [];
        //var orderedKeys  = $select2.data('preserved-keys') || [];

        var elmId = e.params.data.id;

        if (e.type == 'select2:select'){
            order[ order.length ] = text;
        } else if ( e.type == 'select2:unselect'){
            var found_index = order.indexOf(text);
            if( found_index >= 0 )
                order.splice(found_index,1);
        }

        $select2.data('preserved-order', order); // store it for later

        var formElm = $select2.closest('form');
        var orderedElmName = 'ordered-' + $select2.prop('name');
        var orderedElm = formElm.find('input[name="' + orderedElmName + '"]');
        if(orderedElm.length < 1) {
            orderedElm = $('<input>').attr({type: 'hidden', name: orderedElmName});
            orderedElm.appendTo(formElm);
        }

        var orderedKeys = orderedElm.val().split(',');

        if (e.type == 'select2:select'){
            orderedKeys[orderedKeys.length] = elmId;
        } else if ( e.type == 'select2:unselect'){
            var keyIndex = orderedKeys.indexOf(elmId);
            if(keyIndex >= 0)
                orderedKeys.splice(keyIndex,1);
        }

        orderedElm.val(orderedKeys.join(','));

        select2_render($select2,text);
    }

    function select2_render($select2,text){
        var order      = $select2.data('preserved-order') || [];
        var $container = $select2.next('.select2-container');
        var $tags      = $container.find('li.select2-selection__choice');
        var $selected  = $tags.filter('[title="' + text + '"]');
        var $input     = $tags.last().next();

        $.each(order , function(i, val) {
            var $el = $tags.filter('[title="' + val + '"]');
            $input.before( $el );
        });

        //console.log($('select.uc-select2-multiple').select2('data'));
    }

    $('.uc-select2-multiple').on('select2:select', selectionHandler).on('select2:unselect', selectionHandler);



    $('.uc-settings-module-actions button').each(function(){
        btnElmId = $(this).attr('id');
        actionPrefix = 'btn-uc-form-action-';

        if(btnElmId.indexOf(actionPrefix + 'embed-form-short-code') === 0){
            handleEmbedFormShortCodeAction($(this));
        }

        if(btnElmId.indexOf(actionPrefix + 'add-new-form') === 0){
            handleAddNewFormAction($(this));
        }

        if(btnElmId.indexOf(actionPrefix + 'delete-form') === 0){
            handleDeleteFormAction($(this));
        }

        actionPrefix = 'btn-uc-user-role-action-';

        if(btnElmId.indexOf(actionPrefix + 'add-new-user-role') === 0){
            handleAddNewUserRoleAction($(this));
        }

        if(btnElmId.indexOf(actionPrefix + 'delete-user-role') === 0){
            handleDeleteUserRoleAction($(this));
        }

        if(btnElmId.indexOf(actionPrefix + 'add-new-social-connect-config') === 0){
            handleAddNewSocialConnectConfigAction($(this));
        }

        if(btnElmId.indexOf(actionPrefix + 'delete-social-connect-config') === 0){
            handleDeleteSocialConnectConfigAction($(this));
        }


        actionPrefix = 'btn-uc-members-directory-action-';
        if(btnElmId.indexOf(actionPrefix + 'add-new-members-directory') === 0){
            handleAddNewMembersDirectoryAction($(this));
        }

        if(btnElmId.indexOf(actionPrefix + 'delete-members-directory') === 0){
            handleDeleteMembersDirectoryAction($(this));
        }

        actionPrefix = 'btn-uc-custom-tabs-action-';
        if(btnElmId.indexOf(actionPrefix + 'add-new-user-custom-tab') === 0){
            handleAddNewCustomTabAction($(this), 1);
        }

        if(btnElmId.indexOf(actionPrefix + 'add-new-group-custom-tab') === 0){
            handleAddNewCustomTabAction($(this), 2);
        }

        if(btnElmId.indexOf(actionPrefix + 'delete-custom-tab') === 0){
            handleDeleteCustomTabAction($(this));
        }


    });



    function handleAddNewCustomTabAction(buttonElm, $targetId)
    {
        var popupWrapper = $('#' + 'uc-popup-add-new-custom-tab-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({
            midClick: true,
            mainClass: 'mfp-zoom-in',
            removalDelay: 200, modal:true,

            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();notice.remove();notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },
                open: function () {

                    requestParams.action   = 'addNewCustomTab';
                    requestParams.targetId = $targetId;

                    $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);
                    $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                        ajaxLoader.remove();
                        if (typeof resp.success === 'undefined') {
                            notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                            return;
                        }

                        notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                        if (resp.success) {
                            notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                $(this).dequeue();
                                $.magnificPopup.instance.close();
                                window.location.reload(true);
                            });
                        }

                    });

                }
            }

        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));

    }




    function handleDeleteCustomTabAction(buttonElm)
    {
        var popupWrapper = $('#' + 'uc-popup-delete-custom-tab-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({

            midClick: true,
            mainClass: 'mfp-fade',
            removalDelay: 100,
            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();
                    notice.remove();
                    notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },

                open: function () {

                    this.content.find('button.uc-button-primary').off('click');

                    this.content.find('button.uc-button-danger').one('click', function (evt) {
                        $.magnificPopup.instance.close();
                    });


                    this.content.find('button.uc-button-primary').one('click', function (evt) {

                        $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);

                        requestParams.customTabPostId = buttonElm.data('custompostid');
                        requestParams.action = 'deleteCustomTab';

                        $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                            ajaxLoader.remove();
                            if (typeof resp.success === 'undefined') {
                                notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                                return;
                            }

                            notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                            if (resp.success) {
                                notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                    $(this).dequeue();
                                    $.magnificPopup.instance.close();
                                    window.location.reload(true);
                                });
                            }

                        });

                    });
                }
            }


        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));

    }






    function handleDeleteUserRoleAction(buttonElm)
    {
        var popupWrapper = $('#' + 'uc-popup-delete-user-role-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({

            midClick: true,
            mainClass: 'mfp-fade',
            removalDelay: 100,
            callbacks: {
                beforeOpen: function () {
                     ajaxLoader.remove();
                     notice.remove();
                    notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },

                open: function () {
                    this.content.find('button.uc-button-danger').one('click', function (evt) {
                        $.magnificPopup.instance.close();
                    });

                    this.content.find('button.uc-button-primary').one('click', function (evt) {

                        $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);

                        requestParams.userRoleCustomPostId = buttonElm.data('custompostid');
                        requestParams.action = 'deleteUserRole';

                        $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                            ajaxLoader.remove();
                            if (typeof resp.success === 'undefined') {
                                notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                                return;
                            }

                            notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                            if (resp.success) {
                                notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                    $(this).dequeue();
                                    $.magnificPopup.instance.close();
                                    window.location.reload(true);
                                });
                            }

                        });

                    });
                }
            }


        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));

    }

    function handleAddNewUserRoleAction(buttonElm)
    {
        var popupWrapper = $('#' + 'uc-popup-add-new-user-role-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({
            midClick: true,
            mainClass: 'mfp-zoom-in',
            removalDelay: 200, modal:true,

            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();notice.remove();notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },
                open: function () {

                    requestParams.action = 'addNewUserRole';
                    $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);
                    $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                        ajaxLoader.remove();
                        if (typeof resp.success === 'undefined') {
                            notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                            return;
                        }

                        notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                        if (resp.success) {
                            notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                $(this).dequeue();
                                $.magnificPopup.instance.close();
                                window.location.reload(true);
                            });
                        }

                    });

                }
            }

        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));

    }


    function handleAddNewMembersDirectoryAction(buttonElm)
    {
        var popupWrapper = $('#' + 'uc-popup-add-new-members-directory-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({
            midClick: true,
            mainClass: 'mfp-zoom-in',
            removalDelay: 200, modal:true,

            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();notice.remove();notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },
                open: function () {

                    requestParams.action = 'addNewMembersDirectory';
                    $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);
                    $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                        ajaxLoader.remove();
                        if (typeof resp.success === 'undefined') {
                            notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                            return;
                        }

                        notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                        if (resp.success) {
                            notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                $(this).dequeue();
                                $.magnificPopup.instance.close();
                                window.location.reload(true);
                            });
                        }

                    });

                }
            }

        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));

    }


    function handleDeleteMembersDirectoryAction(buttonElm)
    {
        var popupWrapper = $('#' + 'uc-popup-delete-members-directory-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({

            midClick: true,
            mainClass: 'mfp-fade',
            removalDelay: 100,
            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();
                    notice.remove();
                    notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },

                open: function () {

                    this.content.find('button.uc-button-primary').off('click');

                    this.content.find('button.uc-button-danger').one('click', function (evt) {
                        $.magnificPopup.instance.close();
                    });


                    this.content.find('button.uc-button-primary').one('click', function (evt) {

                        $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);

                        requestParams.membersDirectoryCustomPostId = buttonElm.data('custompostid');
                        requestParams.action = 'deleteMembersDirectory';

                        $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                            ajaxLoader.remove();
                            if (typeof resp.success === 'undefined') {
                                notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                                return;
                            }

                            notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                            if (resp.success) {
                                notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                    $(this).dequeue();
                                    $.magnificPopup.instance.close();
                                    window.location.reload(true);
                                });
                            }

                        });

                    });
                }
            }


        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));


    }












    function handleAddNewSocialConnectConfigAction(buttonElm)
    {
        var popupWrapper = $('#' + 'uc-popup-add-new-social-connect-config-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({
            midClick: true,
            mainClass: 'mfp-zoom-in',
            removalDelay: 200, modal:true,

            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();notice.remove();notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },
                open: function () {

                    requestParams.action = 'addNewSocialConnectConfig';
                    $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);
                    $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                        ajaxLoader.remove();
                        if (typeof resp.success === 'undefined') {
                            notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                            return;
                        }

                        notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                        if (resp.success) {
                            notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                $(this).dequeue();
                                $.magnificPopup.instance.close();
                                window.location.reload(true);
                            });
                        }

                    });

                }
            }

        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));

    }


    function handleDeleteSocialConnectConfigAction(buttonElm)
    {
        var popupWrapper = $('#' + 'uc-popup-delete-social-connect-config-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({

            midClick: true,
            mainClass: 'mfp-fade',
            removalDelay: 100,
            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();
                    notice.remove();
                    notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },

                open: function () {

                    this.content.find('button.uc-button-primary').off('click');

                    this.content.find('button.uc-button-danger').one('click', function (evt) {
                        $.magnificPopup.instance.close();
                    });


                    this.content.find('button.uc-button-primary').one('click', function (evt) {

                        $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);

                        requestParams.socialConnectCustomPostId = buttonElm.data('custompostid');
                        requestParams.action = 'deleteSocialConnectConfig';

                        $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                            ajaxLoader.remove();
                            if (typeof resp.success === 'undefined') {
                                notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                                return;
                            }

                            notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                            if (resp.success) {
                                notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                    $(this).dequeue();
                                    $.magnificPopup.instance.close();
                                    window.location.reload(true);
                                });
                            }

                        });

                    });
                }
            }


        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));

    }



    function handleEmbedFormShortCodeAction(buttonElm)
    {
        var popupWrapper = $('#' + 'uc-popup-embed-form-short-code-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({
            midClick: true,
            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();
                    notice.remove();
                    notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },
                open: function () {
                }
            }

        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));

    }

    function handleAddNewFormAction(buttonElm)
    {
        var popupWrapper = $('#' + 'uc-popup-add-new-form-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({
            midClick: true,
            mainClass: 'mfp-zoom-in',
            removalDelay: 300, modal:false,

            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();
                    notice.remove();
                    notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },
                open: function () {

                    $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);
                    requestParams.formPostType = $.magnificPopup.instance.st.mainEl.data('customposttype');
                    requestParams.action = 'addNewForm';

                    $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                        ajaxLoader.remove();
                        if (typeof resp.success === 'undefined') {
                            notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                            return;
                        }

                        notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                        if (resp.success) {
                            notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                $(this).dequeue();
                                $.magnificPopup.instance.close();
                                window.location.reload(true);
                            });
                        }

                    });

                }
            }

        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));

    }

    function handleDeleteFormAction(buttonElm)
    {
        var popupWrapper = $('#' + 'uc-popup-delete-form-' + buttonElm.data('modulekey'));
        if(!popupWrapper.length)
            return;

        buttonElm.magnificPopup({

            midClick: true,
            //mainClass: 'mfp-zoom-in',
            //removalDelay: 300,
            callbacks: {
                beforeOpen: function () {
                    ajaxLoader.remove();
                    notice.remove();
                    notice.attr('class', function (i, c) {
                        return c.replace(/(^|\s)uc-notice-\S+/g, '');
                    });
                },

                open: function () {
                    this.content.find('button.uc-button-danger').one('click', function (evt) {
                        $.magnificPopup.instance.close();
                    });

                    this.content.find('button.uc-button-primary').one('click', function (evt) {

                        $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);

                        requestParams.formCustomPostId = $.magnificPopup.instance.st.mainEl.data('custompostid');
                        requestParams.action = 'deleteForm';

                        $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function (resp) {
                            ajaxLoader.remove();
                            if (typeof resp.success === 'undefined') {
                                notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                                return;
                            }

                            notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                            if (resp.success) {
                                notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                    $(this).dequeue();
                                    $.magnificPopup.instance.close();
                                    window.location.reload(true);
                                });
                            }

                        });

                    });
                }
            }


        }).attr('data-mfp-src', '#' + popupWrapper.attr('id'));



    }

    $('#uc-delete-user-role').magnificPopup({
        midClick: true,
        mainClass: 'mfp-zoom-in',
        //removalDelay: 300,
        callbacks :
        {
            beforeOpen : function(){ajaxLoader.remove(); notice.remove(); notice.attr('class',function(i, c){return c.replace(/(^|\s)uc-notice-\S+/g, '');});
            },

            open : function(){
                this.content.find('button.uc-button-danger').one('click', function(evt){$.magnificPopup.instance.close();});

                this.content.find('button.uc-button-primary').one('click', function(evt){

                    $.magnificPopup.instance.content.find('div.uc-popup-content').append(ajaxLoader);

                    requestParams.userRoleCustomPostId = $.magnificPopup.instance.st.mainEl.data('postid');
                    requestParams.action = 'deleteUserRole';

                    $.post(UltraCommAdmin.ajaxUrl, requestParams).always(function( resp ) {
                        ajaxLoader.remove();
                        if(typeof resp.success === 'undefined'){
                            notice.addClass('uc-notice-error').text(generalError).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));
                            return;
                        }

                        notice.addClass(resp.success ? 'uc-notice-success' : 'uc-notice-error').text(resp.data.message).appendTo($.magnificPopup.instance.content.find('.uc-popup-content'));

                        if (resp.success)
                        {
                            notice.hide().fadeIn(1000).delay(2000).queue(function () {
                                $(this).dequeue();
                                $.magnificPopup.instance.close();
                                window.location.reload(true);
                            });
                        }

                    });

                });
            }
        }
    }).attr('data-mfp-src', '#uc-confirmation-popup');


});
