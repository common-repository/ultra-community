jQuery( document ).ready(function($){

    UltraComm.loggedInUserId = 0;
    var targetActionElements = {'.uc-button-join-group' : 'userJoinGroup', '.uc-button-leave-group' : 'userLeaveGroup', '.uc-button-ban-group-user' : 'banUserGroup', '.uc-button-unlock-group-user' : 'unlockUserGroup', '.uc-button-delete-group-user' : 'deleteUserGroup', '.uc-button-accept-group-join-request' : 'acceptGroupJoinRequest', '.uc-button-add-activity-post' : 'saveActivityPost'};
    var ajaxRequestData = {action : '', profiledUserSlug: UltraComm.profiledUserSlug, profiledGroupId: UltraComm.profiledGroupId, ajaxRequestNonce : UltraComm.ajaxRequestNonce};
    (function(){
        ajaxRequestData.action = 'getLoggedInUserInfo';
        $.post(UltraComm.ajaxUrl, ajaxRequestData).always(function( resp ) {
            if(resp.success && resp.data.userId && isInt(resp.data.userId)){
                UltraComm.loggedInUserId = +resp.data.userId;UltraComm.ajaxRequestNonce = ajaxRequestData.ajaxRequestNonce = resp.data.ajaxRequestNonce;
                initTargetElementsEvents();
                return;
            }
            initModalPopupLogin();
        });
    })();

    saveActivityPost = function(evt){
        evt.preventDefault();
        ajaxRequestData.action = 'saveActivityPost';
        ajaxRequestData.uploadedFiles = JSON.stringify(arrUploadedFiles);
        var btnElm = $(evt.currentTarget);
        var frmElm = btnElm.closest('form');
        var requestData = frmElm.serialize() + '&' + $.param(ajaxRequestData);
        btnElm.off('click');
        delete ajaxRequestData.uploadedFiles;

        $.post(UltraComm.ajaxUrl, requestData).always(function( resp ) {
            btnElm.one('click', saveActivityPost);
            //frmElm.find('.uc-notice-holder').html('');
            if(!resp.success && resp.data && resp.data.message){
                frmElm.find('.uc-notice-holder').html($('<p class = "uc-notice uc-notice-error" />').text(resp.data.message)).find('p').delay(2500).fadeOut('slow', function(){$(this).remove();});
                return;
            }

            if(resp.success){
                frmElm.trigger('reset');
                $('.uc-activity-delete-file').each(function(index){$(this).trigger('click');});
                frmElm.find(uploadsHolder).hide('slow', function(){ uploadsHolder.remove();});
                arrUploadedFiles = [];
            }




        });



    };



    function getAllowedFileTypes() {

        switch($('#uc-activity-post-format').val())
        {
            case 'image' : return /^image\/.*$/;
            case 'audio' : return /^audio\/.*$/;
            case 'video' : return /^video\/.*$/;
        }

        return /\.[0-9a-z]+$/i;;
    }


    var uploadsHolder = $('<div class="uc-activity-uploads-holder"/>').append('<div class="uc-section-divider"/>').append('<div class="uc-grid uc-grid--center uc-grid--justify-center uc-grid--flex-cells uc-grid--gutters" />');
    var arrUploadedFiles = [];

    //autosize($('#uc-activity-fileupload').closest('form').find('textarea'));


    $('#uc-activity-fileupload').closest('form').on('click', 'ul.uc-activity-post-format label', function (evt) {
        evt.preventDefault();

        if($(this).hasClass('active')){
            return;
        }

        $('.uc-activity-delete-file').each(function(index){
            $(this).trigger('click');
        });

        $(this).closest('form').find(uploadsHolder).hide('slow', function(){ uploadsHolder.remove();});

        $(this).closest('ul').find('label').toggleClass('active', false);
        $(this).toggleClass('active', true);
        $('#uc-activity-post-format').val($(this).data('postFormat'));

        switch ($(this).data('postFormat'))
        {
            case '':
            case 'quote':
            case 'status':  $('#uc-activity-fileupload').closest('div').hide(); break;
            default : $('#uc-activity-fileupload').closest('div').show(); break;
        }


        $('.uc-activity-quote-fields-holder, .uc-activity-link-fields-holder').fadeOut();
        switch ($(this).data('postFormat'))
        {
            case 'quote':  $('.uc-activity-quote-fields-holder').fadeIn(); break;
            case 'link':  $('.uc-activity-link-fields-holder').fadeIn(); break;
        }

    });


    $('#uc-activity-fileupload').closest('form').on('click', '.uc-activity-delete-file', function (evt) {
        evt.preventDefault();
        var removeElm = $(this);
        arrUploadedFiles = jQuery.grep(arrUploadedFiles, function( uploadedFile ) {
            if (uploadedFile.name !== removeElm.data('fileName') )
                return true;
            removeElm.closest('.uc-activity-upload-holder').parent().hide('slow', function(){ $(this).remove();});
            return false;
        });
    });


    $('#uc-activity-fileupload').length && $('#uc-activity-fileupload').fileupload({
        url: UltraComm.ajaxUrl,
        dataType: 'json',
        autoUpload: true,
        //acceptFileTypes: getAllowedFileTypes(),
        maxFileSize: 9999000,
        singleFileUploads: true
    }).on('fileuploadadd', function (e, data) {

        data.acceptFileTypes =  getAllowedFileTypes();

        data.form.find('.uc-notice-holder').html('');
        uploadsHolder.css('display', 'block');
        data.form.find(uploadsHolder).length || uploadsHolder.insertBefore(data.form.find('.uc-form-footer'));
        data.context = $('<div class="uc-grid uc-grid--full uc-grid--center uc-activity-upload-holder" />').css({width:'90px', height:'30px'});

        data.context.append('<div class="uc-grid-cell uc-grid-cell--top"></div>');
        data.context.append('<div class="uc-grid-cell uc-ajax-loader"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div>');
        data.context.append($('<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--bottom uc-activity-upload-progress"></div>').css({width:'.01em'}));

    }).on('fileuploadsubmit', function (e, data) {
        data.formData = ajaxRequestData;
        data.formData.action = 'uploadTemporaryFile';
    }).on('fileuploaddone', function (e, data) {

        $.each(data.result.files, function (index, file) {

            if(file.error){
                data.form.find('.uc-notice-holder').html($('<p class = "uc-notice uc-notice-error" />').text(file.error));
                data.form.find('.uc-notice').delay(1500).hide('slow', function(){$(this).remove();});
                data.context.parent().delay(1500).hide('slow', function(){ $(this).remove();});

                return;
            }

            arrUploadedFiles.push(file);
            if (file.url && file.type.indexOf('image/') !== -1) {
                data.context.css('background-image', 'url(' + file.url + ')').css({width:'90px', height:'90px'});
                data.context.html('<div class="uc-grid-cell" style="text-align: center"><i class="fa fa-trash uc-activity-delete-file" data-file-name = "'+file.name+'"></i></div>');
            }
            else
            {
                data.context.toggleClass('uc-activity-upload-file', true).css({width:'auto', height:'auto'});

                data.context.html('<p><i class="fa fa-paperclip"></i>'+file.name+'<i class="fa fa-trash uc-activity-delete-file" data-file-name = "'+file.name+'"></i></p>');
            }



        });



    }).on('fileuploadprogress', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        data.context.find('.uc-activity-upload-progress').css('width', progress + '%');

    }).on('fileuploadprocessalways', function (e, data) {

        if(data.files[data.index].error)
        {
            data.form.find('.uc-notice-holder').html($('<p class = "uc-notice uc-notice-error" />').text(data.files[data.index].error));
            data.form.find('.uc-notice').delay('1500').hide('slow', function(){ $(this).remove();});

            return;
        }


        uploadsHolder.children().last().append($('<div class="uc-grid-cell uc-grid-cell--autoSize"/>').append(data.context));

    });













    unlockUserGroup = function(evt){
        changeGroupUserStatus(evt, 'unlockUser');
    };

    banUserGroup = function(evt){
        changeGroupUserStatus(evt, 'banUser');
    };

    deleteUserGroup = function(evt){
        changeGroupUserStatus(evt, 'deleteUser');
    };

    acceptGroupJoinRequest = function(evt){
        changeGroupUserStatus(evt, 'acceptJoinRequest');
    };

    changeGroupUserStatus = function(evt, status){
        evt.preventDefault();
        var btnElm = $(evt.currentTarget);
        btnElm.off('click');

        if(!btnElm.data('userId') || !isInt(btnElm.data('userId')))
            return;

        ajaxRequestData.action = 'changeGroupUserStatus';
        var requestParams = ajaxRequestData;
        requestParams.userId = btnElm.data('userId');
        requestParams.status = status;
        $.post(UltraComm.ajaxUrl, requestParams).always(function( resp ) {

            if(resp.data && resp.data.message){
                btnElm.closest('.uc-group-profile-member-holder').find('.uc-group-profile-member-body').css('display', 'block').html(resp.data.message).delay(1000).fadeOut(1500).queue(function (){
                    $(this).dequeue(); $(this).html('');
                    if('deleteUser' == status){
                        btnElm.closest('.uc-group-profile-member-holder').remove();
                    }
                });
            }

        });

    };


    userJoinGroup = function (evt) {
        evt.preventDefault();
        var btnElm = $(evt.currentTarget);
        btnElm.off('click');

        if(!btnElm.data('groupId') || !isInt(btnElm.data('groupId')))
            return;

        btnElm.siblings('.uc-ajax-loader').css('visibility', 'visible');

        ajaxRequestData.action = 'currentUserJoinGroup';
        ajaxRequestData.groupId = btnElm.data('groupId');
        $.post(UltraComm.ajaxUrl, ajaxRequestData).always(function( resp ) {
            delete ajaxRequestData.groupId;

            var message  = resp.data && resp.data.message ? $(resp.data.message) : null;

            message ? btnElm.parent().append(message) : 0;
            message ? message.delay(2500).fadeOut(1500).queue(function () {$(this).dequeue(); message.remove()}) : 0;
            btnElm.siblings('.uc-ajax-loader').css('visibility', 'hidden');

            if(!resp.success){
                return btnElm.one('click', userJoinGroup);
            }

            resp.data.buttonText ? btnElm.html(resp.data.buttonText) : 0;

            resp.data.buttonClass ? btnElm.removeClass('uc-button-join-group').addClass(resp.data.buttonClass) : 0;

            +resp.data.userStatusId === 1 ? btnElm.one('click', userLeaveGroup) : 0;

        });

    };

    userLeaveGroup = function (evt) {
        evt.preventDefault();
        var btnElm = $(evt.currentTarget);
        btnElm.off('click');

        if(!btnElm.data('groupId') || !isInt(btnElm.data('groupId')))
            return;

        btnElm.siblings('.uc-ajax-loader').css('visibility', 'visible');

        ajaxRequestData.action = 'currentUserLeaveGroup';
        ajaxRequestData.groupId = btnElm.data('groupId');
        $.post(UltraComm.ajaxUrl, ajaxRequestData).always(function( resp ) {
            delete ajaxRequestData.groupId;

            var message  = resp.data.message ? $(resp.data.message) : null;

            message ? btnElm.parent().append(message) : 0;
            message ? message.delay(2500).fadeOut(1500).queue(function () {$(this).dequeue(); message.remove()}) : 0;
            btnElm.siblings('.uc-ajax-loader').css('visibility', 'hidden');

            if(!resp.success){
                return btn.one('click', userLeaveGroup);
            }

            resp.data.buttonText ? btnElm.html(resp.data.buttonText) : 0;

            resp.data.buttonClass ? btnElm.removeClass('uc-button-leave-group').addClass(resp.data.buttonClass) : 0;

            btnElm.one('click', userJoinGroup);

        });


    };

    authenticateUser = function (evt) {
        evt.preventDefault();
        var frmElm = $(this);
        var popupInstance = $.magnificPopup.instance;
        var noticeElm = popupInstance.content.find('p.uc-notice'); noticeElm.length ? noticeElm.parent().remove() : 0;

        frmElm.off('submit');
        popupInstance.content.find('.uc-ajax-loader').toggleClass('uc-hidden', false);
        ajaxRequestData.action = 'authenticateUser';

        $.post(UltraComm.ajaxUrl, $(this).serialize() + '&' + $.param(ajaxRequestData)).always(function( resp ) {

            frmElm.one('submit', authenticateUser);
            popupInstance.content.find('.uc-ajax-loader').toggleClass('uc-hidden', true);
            if(!resp.success){
                popupInstance.content.find('.uc-form-body').append('<div class="uc-u-1 uc-control-group"><p class="uc-notice uc-notice-error">'+resp.data.message+'</p></div>');
                return;
            }

            if(resp.success && resp.data.userId && isInt(resp.data.userId)){
                UltraComm.loggedInUserId   = +resp.data.userId;
                UltraComm.ajaxRequestNonce = ajaxRequestData.ajaxRequestNonce = resp.data.ajaxRequestNonce;
                popupInstance.close();
                initTargetElementsEvents();
                $(evt.data.elmIdentifier).trigger('click');
            }

        });


    };

    initTargetElementsEvents = function () {
        $.each(targetActionElements, function (elmIdentifier, handler) {
            $(elmIdentifier).off('click').one('click', function (evt) {
                window[handler](evt);
            });

        });

    };

    initModalPopupLogin = function () {
        if(UltraComm.loggedInUserId)return;

        $.each(targetActionElements, function(elmIdentifier, action) {
            $(elmIdentifier).magnificPopup({
                midClick: true,
                mainClass: 'mfp-fade',
                removalDelay: 500,
                type:'inline',
                disableOn: function() {return !(isInt(UltraComm.loggedInUserId) && UltraComm.loggedInUserId > 0);},

                callbacks : {
                    beforeOpen : function(){
                        var noticeElm = $('#uc-modal-login-popup').find('p.uc-notice'); noticeElm.length ? noticeElm.parent().remove() : 0;
                    },

                    open : function(){
                        this.content.find('form').off('submit').one("submit", {elmIdentifier : elmIdentifier}, authenticateUser);
                    }

                }

            }).attr('data-mfp-src', '#uc-modal-login-popup');

        });

    };


    /*User Menu DropDown */
    $('div.uc-user-actions-toggle').click(function(e){
        var actionsDropDownElm = this.querySelector('.uc-user-actions-dropdown');
        var isHidden = $(actionsDropDownElm).is(':hidden');
        $(actionsDropDownElm).toggle();
        $(this).find('ul.uc-user-actions-box-info i.fa').toggleClass('fa-caret-square-o-up', isHidden).toggleClass('fa-caret-square-o-down', !isHidden);
    });

    $(document).click(function(e) {
        var target = e.target;
        if (!$(target).is('.uc-user-actions-toggle') && !$(target).parents().is('.uc-user-actions-toggle')) {
            $('.uc-user-actions-dropdown').hide();$('.uc-user-actions-toggle').find('ul.uc-user-actions-box-info i.fa').toggleClass('fa-caret-square-o-up', false).toggleClass('fa-caret-square-o-down', true);
        }

        if (!$(target).is('.uc-profile-main-navigation') && !$(target).parents().is('.uc-mobile-menu-toggle')) {
            if($('.uc-profile-main-navigation').hasClass('uc-mobile-menu-active')){
                $('.uc-profile-main-navigation').removeClass('uc-mobile-menu-active')
                $('.uc-profile-main-navigation').hide();
            }
        }

    });


    $( '.uc-mobile-menu-toggle' ).click( function( e ) {
        e.preventDefault();
        var mainMenu = $('ul.uc-profile-main-navigation').toggleClass('uc-mobile-menu-active').toggle();
    });


    $(window).ucWindowResize(function(){
        if($(this).width() < 1024)
            return;
        $('ul.uc-profile-main-navigation').show();
    });


    function isInt(n) {
        return +n === n && !(n % 1);
    }



// Activity comments //





});

(function($,sr){
    var debounce = function (func, threshold, execAsap) {
        var timeout;

        return function debounced () {
            var obj = this, args = arguments;
            function delayed () {
                if (!execAsap)
                    func.apply(obj, args);
                timeout = null;
            };

            if (timeout)
                clearTimeout(timeout);
            else if (execAsap)
                func.apply(obj, args);

            timeout = setTimeout(delayed, threshold || 100);
        };
    };
    jQuery.fn[sr] = function(fn){  return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr); };

})(jQuery,'ucWindowResize');



/*!
 Autosize 4.0.0
 license: MIT
 http://www.jacklmoore.com/autosize
 */
!function(e,t){if("function"==typeof define&&define.amd)define(["exports","module"],t);else if("undefined"!=typeof exports&&"undefined"!=typeof module)t(exports,module);else{var n={exports:{}};t(n.exports,n),e.autosize=n.exports}}(this,function(e,t){"use strict";function n(e){function t(){var t=window.getComputedStyle(e,null);"vertical"===t.resize?e.style.resize="none":"both"===t.resize&&(e.style.resize="horizontal"),s="content-box"===t.boxSizing?-(parseFloat(t.paddingTop)+parseFloat(t.paddingBottom)):parseFloat(t.borderTopWidth)+parseFloat(t.borderBottomWidth),isNaN(s)&&(s=0),l()}function n(t){var n=e.style.width;e.style.width="0px",e.offsetWidth,e.style.width=n,e.style.overflowY=t}function o(e){for(var t=[];e&&e.parentNode&&e.parentNode instanceof Element;)e.parentNode.scrollTop&&t.push({node:e.parentNode,scrollTop:e.parentNode.scrollTop}),e=e.parentNode;return t}function r(){var t=e.style.height,n=o(e),r=document.documentElement&&document.documentElement.scrollTop;e.style.height="";var i=e.scrollHeight+s;return 0===e.scrollHeight?void(e.style.height=t):(e.style.height=i+"px",u=e.clientWidth,n.forEach(function(e){e.node.scrollTop=e.scrollTop}),void(r&&(document.documentElement.scrollTop=r)))}function l(){r();var t=Math.round(parseFloat(e.style.height)),o=window.getComputedStyle(e,null),i="content-box"===o.boxSizing?Math.round(parseFloat(o.height)):e.offsetHeight;if(i!==t?"hidden"===o.overflowY&&(n("scroll"),r(),i="content-box"===o.boxSizing?Math.round(parseFloat(window.getComputedStyle(e,null).height)):e.offsetHeight):"hidden"!==o.overflowY&&(n("hidden"),r(),i="content-box"===o.boxSizing?Math.round(parseFloat(window.getComputedStyle(e,null).height)):e.offsetHeight),a!==i){a=i;var l=d("autosize:resized");try{e.dispatchEvent(l)}catch(e){}}}if(e&&e.nodeName&&"TEXTAREA"===e.nodeName&&!i.has(e)){var s=null,u=e.clientWidth,a=null,c=function(){e.clientWidth!==u&&l()},p=function(t){window.removeEventListener("resize",c,!1),e.removeEventListener("input",l,!1),e.removeEventListener("keyup",l,!1),e.removeEventListener("autosize:destroy",p,!1),e.removeEventListener("autosize:update",l,!1),Object.keys(t).forEach(function(n){e.style[n]=t[n]}),i.delete(e)}.bind(e,{height:e.style.height,resize:e.style.resize,overflowY:e.style.overflowY,overflowX:e.style.overflowX,wordWrap:e.style.wordWrap});e.addEventListener("autosize:destroy",p,!1),"onpropertychange"in e&&"oninput"in e&&e.addEventListener("keyup",l,!1),window.addEventListener("resize",c,!1),e.addEventListener("input",l,!1),e.addEventListener("autosize:update",l,!1),e.style.overflowX="hidden",e.style.wordWrap="break-word",i.set(e,{destroy:p,update:l}),t()}}function o(e){var t=i.get(e);t&&t.destroy()}function r(e){var t=i.get(e);t&&t.update()}var i="function"==typeof Map?new Map:function(){var e=[],t=[];return{has:function(t){return e.indexOf(t)>-1},get:function(n){return t[e.indexOf(n)]},set:function(n,o){e.indexOf(n)===-1&&(e.push(n),t.push(o))},delete:function(n){var o=e.indexOf(n);o>-1&&(e.splice(o,1),t.splice(o,1))}}}(),d=function(e){return new Event(e,{bubbles:!0})};try{new Event("test")}catch(e){d=function(e){var t=document.createEvent("Event");return t.initEvent(e,!0,!1),t}}var l=null;"undefined"==typeof window||"function"!=typeof window.getComputedStyle?(l=function(e){return e},l.destroy=function(e){return e},l.update=function(e){return e}):(l=function(e,t){return e&&Array.prototype.forEach.call(e.length?e:[e],function(e){return n(e,t)}),e},l.destroy=function(e){return e&&Array.prototype.forEach.call(e.length?e:[e],o),e},l.update=function(e){return e&&Array.prototype.forEach.call(e.length?e:[e],r),e}),t.exports=l});