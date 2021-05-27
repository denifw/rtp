var App;
(function (App) {
    App.Popups = [];
    App.Modals = {};
    App.getBaseUrl = function () {
        return window.location.origin;
    };
    App.reloadWindow = function () {
        window.location = window.location.href;
    };
    App.checkPopupSession = function () {
        var ajax = new App.AjaxHandler();
        ajax.setMethod('GET');
        ajax.setResponseType('json');
        ajax.setContentType('json');
        ajax.setUrl('/checkTimeout');
        ajax.setData({});
        ajax.execute(function (response) {
            if (response === false) {
                App.closeAllPopup();
                window.close();
            }
        }, function (err) {
            console.log('failed ajax check session', err);
        });
    };
    App.checkSession = function () {
        var ajax = new App.AjaxHandler();
        ajax.setMethod('GET');
        ajax.setResponseType('json');
        ajax.setContentType('json');
        ajax.setUrl('/checkTimeout');
        ajax.setData({});
        ajax.execute(function (response) {
            if (response === false) {
                App.logoutSystem();
            }
        }, function (err) {
            console.log('failed ajax check session', err);
        });
    };
    App.closeAllPopup = function () {
        var lengthPopup = App.Popups.length;
        if (lengthPopup > 0) {
            for (var i = 0; i < lengthPopup; i++) {
                if (App.Popups[i] && App.Popups[i].closed === false) {
                    App.Popups[i].App.closeAllPopup();
                    App.Popups[i].close();
                    console.log('Popup closed.');
                }
            }
        }
    };
    App.showLoading = function () {
        var modalElement = document.getElementById('mdl-loading');
        if (modalElement) {
            $('#mdl-loading').modal();
            var intVal = setInterval(function () {
                if (document.readyState === 'complete') {
                    $('#mdl-loading').modal('toggle');
                    clearInterval(intVal);
                }
            }, 100);
        }
    };
    App.submitForm = function (formId, hideLoading) {
        console.log('Submit form ', formId);
        if (document.getElementById(formId)) {
            if (hideLoading !== true) {
                var modalElement = document.getElementById('mdl-loading');
                if (modalElement) {
                    $('#mdl-loading').modal();
                }
            }
            document.getElementById(formId).submit();
        }
    };
    App.onClickPaging = function (formId, pageNumber, fieldId) {
        var form = document.getElementById(formId);
        var field = document.getElementById(fieldId);
        if (form && field) {
            field.value = pageNumber;
            App.submitForm(formId);
        }
    };
    App.logoutSystem = function () {
        if (document.getElementById('logout_form')) {
            App.closeAllPopup();
            /*
            * Close all popup when logout,
            * 1. when open a popup, store the name of the popup inside the cookie.
            * var wd = window.open('', 'test_blank', '', true);
            * wd.close();
            * */
            App.submitForm('logout_form');
        }
    };
    App.popup = function (url) {
        var windowObject;
        windowObject = window.open(url, '_blank', 'toolbar=0,menubar=0,status=0,scrollbars=1');
        windowObject.focus();
        App.Popups.push(windowObject);
        console.log('window popup', windowObject);
        console.log('window self', window.self);
    };
    App.hyperLink = function (url) {
        var windowObject;
        windowObject = window.open(url, '_self');
        windowObject.focus();
    };
    App.closeWindow = function () {
        window.close();
    };
    App.Tabs = (function () {
        function Tabs(tabId) {
            this.id = tabId;
            this.contentIds = [];
            this.activeField = null;
        }

        Tabs.prototype.setActiveField = function (fieldId) {
            this.activeField = document.getElementById(fieldId);
        };
        Tabs.prototype.addContentId = function (contentId) {
            if (this.contentIds.indexOf(contentId) === -1) {
                this.contentIds.push(contentId);
            }
        };
        Tabs.prototype.createTab = function () {
            var countIds = this.contentIds.length;
            for (var i = 0; i < countIds; i++) {
                this.createTabEvent(this.contentIds[i]);
            }
        };
        Tabs.prototype.createTabEvent = function (contentId) {
            var _this = this;
            var fieldId = _this.id + '_' + contentId;
            var field = document.getElementById(fieldId);
            if (field) {
                field.onclick = function () {
                    _this.setActiveValue(contentId);
                }
            }
        };
        Tabs.prototype.setActiveValue = function (contentId) {
            if (this.activeField) {
                this.activeField.value = contentId;
            }
        };
        return Tabs;
    }());
    App.AjaxHandler = (function () {
        function AjaxHandler() {
            this.baseUrl = window.location.origin;
            this.method = '';
            this.contentType = '';
            this.responseType = '';
            this.url = '';
            this.onSuccess = null;
            this.onFail = null;
            this.data = {};
            this.response = null;
        }

        AjaxHandler.prototype.setMethod = function (method) {
            var availableMethod = ['GET', 'POST'];
            method = method.toUpperCase();
            if (availableMethod.indexOf(method) > -1) {
                this.method = method;
            }
        };
        AjaxHandler.prototype.setContentType = function (contentType) {
            var availableContentType = ['json', 'form', 'html', 'xml'];
            var contentTypeValue = {
                'json': 'application/json',
                'form': 'application/x-www-form-urlencoded',
                'html': 'text/html',
                'xml': 'text/xml'
            };
            contentType = contentType.toLowerCase();
            if (availableContentType.indexOf(contentType) > -1) {
                this.contentType = contentTypeValue[contentType];
            }
        };
        AjaxHandler.prototype.setResponseType = function (responseType) {
            var availableResponseType = ['array', 'json', 'serial', 'string', 'xml'];
            responseType = responseType.toLowerCase();
            if (availableResponseType.indexOf(responseType) > -1) {
                this.responseType = responseType;
            }
        };
        AjaxHandler.prototype.setUrl = function (url) {
            this.url = this.baseUrl + url;
        };
        AjaxHandler.prototype.setData = function (data) {
            this.data = data;
        };
        AjaxHandler.prototype.callSuccessFunction = function () {
            if (this.onSuccess !== undefined || this.onSuccess !== null) {
                this.onSuccess(this.response);
            }
        };
        AjaxHandler.prototype.callFailFunction = function () {
            if (this.onFail !== undefined || this.onFail !== null) {
                this.onFail(this.response);
            }
        };
        AjaxHandler.prototype.callErrorFunction = function (error) {
            this.callFailFunction();
            console.log('Ajax Error : ', error);
        };
        AjaxHandler.prototype.execute = function (onSuccess, onFail) {
            var _this = this;
            _this.onSuccess = onSuccess;
            _this.onFail = onFail;
            $(document).ready(function () {
                $.ajax({
                    type: _this.method,
                    dataType: _this.responseType,
                    url: _this.url,
                    data: _this.data,
                    contentType: _this.contentType,
                    success: function (data) {
                        _this.response = data;
                        _this.callSuccessFunction();
                    },
                    error: function (data) {
                        _this.callErrorFunction(data);
                    }
                });
            });
        };

        return AjaxHandler;
    }());
    App.StringHelper = (function () {
        function StringHelper() {
        }

        StringHelper.trim = function (stringValue) {
            return stringValue.replace(/^\s*|\s*$/g, '');
        };
        StringHelper.getHtmlEncode = function (stringValue) {
            var str = stringValue;
            str = str.replace(/&/g, '&amp;');
            str = str.replace(/</g, '&lt;');
            str = str.replace(/>/g, '&gt;');
            str = str.replace(/"/g, '&quot;');
            return str;
        };
        StringHelper.getHtmlDecode = function (stringValue) {
            var str = stringValue;
            str = str.replace(/&amp;/g, '&');
            str = str.replace(/&lt;/g, '<');
            str = str.replace(/&gt;/g, '>');
            str = str.replace(/&quot;/g, '\"');
            return str;
        };
        StringHelper.isTextSelected = function (input) {
            var returnValue = false;
            if (typeof input.selectionStart === 'number') {
                returnValue = input.selectionStart === 0 && input.selectionEnd === input.value.length;
            } else if (typeof document.getSelection() !== 'undefined') {
                input.focus();
                returnValue = document.getSelection().toString() === input.value;
            }
            return returnValue;
        };
        return StringHelper;
    }());
    App.SingleSelect = (function () {
        function SingleSelect(fieldId, hiddenId) {
            this.fieldId = fieldId;
            this.Field = document.getElementById(fieldId);
            this.hiddenId = hiddenId;
            this.HiddenField = document.getElementById(hiddenId);
            this.readOnlyField = false;
            this.callBackRoute = '';
            this.detailReferenceCode = '';
            this.enableNewBtn = true;
            this.enableDetailBtn = true;
            this.enableDeleteBtn = true;
            this.BtnNew = AppDom.getElementById(this.fieldId + '_new_btn');
            this.BtnDetail = AppDom.getElementById(this.fieldId + '_detail_btn');
            this.BtnDelete = AppDom.getElementById(this.fieldId + '_delete_btn');
            this.parameters = {};
            this.optionalParameters = {};
            this.parameterByFields = {};
            this.parameterLabels = {};
            this.onClearFields = [];
            this.autoCompleteFields = [];
            this.suggestionBoxId = this.fieldId + '_suggestion_box';
            this.suggestionBox = null;
            this.suggestionListId = this.fieldId + '_suggestion_list';
            this.selectionId = this.fieldId + '_active_select';
            this.SuggestionList = null;
            this.minTextLength = 1;
            this.timeDelay = 500;
            this.tempTime = null;
            this.suggestionData = [];
            this.addCallBackParameterById('search_key', this.fieldId);

        }

        SingleSelect.prototype.setReadOnly = function (enable) {
            this.readOnlyField = enable;
        };
        SingleSelect.prototype.setCallBackRoute = function (callBackRoute) {
            this.callBackRoute = callBackRoute;
        };
        SingleSelect.prototype.setCallBackFunction = function (callBackFunction) {
            this.addCallBackParameter('callBackFunction', callBackFunction);
        };
        SingleSelect.prototype.setDetailReferenceCode = function (refCode) {
            this.detailReferenceCode = refCode;
        };
        SingleSelect.prototype.disableNewBtn = function () {
            this.enableNewBtn = false;
        };
        SingleSelect.prototype.enableNewBtn = function () {
            this.enableNewBtn = true;
        };
        SingleSelect.prototype.disableDetailBtn = function () {
            this.enableDetailBtn = false;
        };
        SingleSelect.prototype.enableDetailBtn = function () {
            this.enableDetailBtn = true;
        };
        SingleSelect.prototype.disableDeleteBtn = function () {
            this.enableDeleteBtn = false;
        };
        SingleSelect.prototype.enableDeleteBtn = function () {
            this.enableDeleteBtn = true;
        };
        SingleSelect.prototype.addCallBackParameter = function (parKey, parValue) {
            this.parameters[parKey] = parValue;
        };
        SingleSelect.prototype.addCallBackParameterById = function (parKey, fieldId) {
            this.parameterByFields[parKey] = fieldId;
        };
        SingleSelect.prototype.addOptionalCallBackParameterById = function (parKey, fieldId) {
            this.optionalParameters[parKey] = fieldId;
        };
        SingleSelect.prototype.addParameterLabel = function (parKey, label) {
            this.parameterLabels[parKey] = label;
        };
        SingleSelect.prototype.addFieldOnClear = function (fieldId) {
            if (this.onClearFields.indexOf(fieldId) === -1) {
                this.onClearFields.push(fieldId);
            }
        };
        SingleSelect.prototype.addAutoCompleteField = function (parKey, parValue) {
            this.autoCompleteFields[parKey] = parValue;
        };
        SingleSelect.prototype.createSingleSelect = function () {
            if (this.readOnlyField === false) {
                this.Field.style.background = "99% 3px no-repeat  url('" + App.getBaseUrl() + "/images/search.png')";
                this.Field.style.backgroundColor = 'white';
                this.eventOnFocus();
                this.eventOnBlur();

                // this.eventOnKeyDown();
                this.eventOnKeyPress();
                this.eventOnKeyUp();
            }
            this.setButtonsEvent();
            this.loadButtons();

        };
        SingleSelect.prototype.loadButtons = function () {
            if (this.BtnNew) {
                if (this.HiddenField.value !== '') {
                    this.BtnNew.style.display = 'none';
                } else {
                    this.BtnNew.style.display = 'inline';
                    if (this.enableNewBtn === false) {
                        this.BtnNew.style.color = 'transparent';
                        this.BtnNew.style.cursor = 'default';
                    }
                }
            }
            if (this.BtnDetail) {
                if (this.enableDetailBtn === false || this.detailReferenceCode.length === 0 || this.HiddenField.value.length === 0) {
                    this.BtnDetail.style.display = 'none';
                } else {
                    this.BtnDetail.style.display = 'inline';
                }
            }
            if (this.BtnDelete) {
                if (this.Field.value === '' || this.HiddenField.value === '') {
                    this.BtnDelete.style.display = 'none';
                } else {
                    this.BtnDelete.style.display = 'inline';
                    if (this.enableDeleteBtn === false) {
                        this.BtnDelete.style.color = 'transparent';
                        this.BtnDelete.style.cursor = 'default';
                    }
                }
            }
        };
        SingleSelect.prototype.setButtonsEvent = function () {
            var _this = this;
            if (_this.BtnNew && this.enableNewBtn === true) {
                _this.BtnNew.onclick = function () {
                    var params = [];
                    params.push('pv=1');
                    var key2 = Object.keys(_this.parameters);
                    var keyLength2 = key2.length;
                    for (var j = 0; j < keyLength2; j++) {
                        if (key2[j] !== 'callBackFunction' && _this.parameters[key2[j]] !== '') {
                            params.push(key2[j] + '=' + _this.parameters[key2[j]]);
                        }
                    }

                    var keys = Object.keys(_this.parameterByFields);
                    var keyLength = keys.length;
                    var temp = null;
                    for (var i = 0; i < keyLength; i++) {
                        if (_this.parameters.hasOwnProperty(keys[i]) === false) {
                            temp = AppDom.getElementById(_this.parameterByFields[keys[i]]);
                            if (temp) {
                                if (temp.value !== '' && _this.parameterByFields[keys[i]] !== _this.fieldId) {
                                    params.push(keys[i] + '=' + temp.value);
                                }
                            }
                        }
                    }

                    App.popup(App.getBaseUrl() + _this.callBackRoute + '/detail?' + params.join('&'));
                };
            }
            if (_this.BtnDetail) {
                _this.BtnDetail.onclick = function () {
                    App.popup(App.getBaseUrl() + _this.callBackRoute + '/detail?pv=1&' + _this.detailReferenceCode + '=' + _this.HiddenField.value);
                };
            }
            if (_this.BtnDelete && this.enableDeleteBtn === true) {
                _this.BtnDelete.onclick = function () {
                    _this.resetSingleSelect();
                };
            }
        };
        SingleSelect.prototype.resetSingleSelect = function () {
            this.Field.value = '';
            this.HiddenField.value = '';
            this.loadButtons();
            this.clearReferenceField();
        };
        SingleSelect.prototype.clearReferenceField = function () {
            var countField = this.onClearFields.length;
            var fieldIds = Object.keys(this.autoCompleteFields);
            var countAutoComplete = fieldIds.length;
            var field, i;
            for (i = 0; i < countField; i++) {
                field = document.getElementById(this.onClearFields[i]);
                if (field) {
                    field.value = '';
                }
            }
            for (i = 0; i < countAutoComplete; i++) {
                field = document.getElementById(fieldIds[i]);
                if (field) {
                    field.value = '';
                }
            }
        };
        SingleSelect.prototype.eventOnBlur = function () {
            var _this = this;
            this.Field.onblur = function () {
                console.log('On Blur Event action');
                if (_this.HiddenField.value === '') {
                    _this.resetSingleSelect();
                }
                _this.removeSuggestionBox();
            };
        };
        SingleSelect.prototype.eventOnFocus = function () {
            var _this = this;
            this.Field.onfocus = function () {
                console.log('On focus action');
                if (_this.Field.value === '') {
                    _this.loadSuggestions();
                }
            };
        };
        SingleSelect.prototype.eventOnKeyPress = function () {
            var _this = this;
            this.Field.onkeypress = function (event) {
                console.log('On key press');
                _this.getKeyCode(event, function (keyCode) {
                    console.log('Key Code ', keyCode);
                    if (keyCode === 13) {
                        event.preventDefault();
                        if (document.getElementById(_this.suggestionBoxId)) {
                            _this.selectSuggestion();
                        }
                    }
                });
            };
        };
        SingleSelect.prototype.eventOnKeyUp = function () {
            var _this = this;
            var oldValue = _this.Field.value;
            this.Field.onkeyup = function (event) {
                console.log('On key up');
                _this.getKeyCode(event, function (keyCode) {
                    console.log('Key Code ', keyCode);
                    if ((keyCode >= 48 && keyCode <= 111) || (keyCode >= 186 && keyCode <= 226) || keyCode === 8 || keyCode === 46) {
                        if (oldValue !== _this.Field.value && _this.Field.value.length >= _this.minTextLength) {
                            _this.HiddenField.value = '';
                            _this.loadButtons();
                            _this.clearReferenceField();
                            console.log('Get in here');
                            if (_this.tempTime) {
                                clearTimeout(_this.tempTime);
                            }
                            _this.tempTime = window.setTimeout(function () {
                                console.log('call this');
                                oldValue = _this.Field.value;
                                _this.loadSuggestions();
                            }, _this.timeDelay);
                        } else {
                            _this.removeSuggestionBox();
                        }
                    }
                });
            };
        };
        SingleSelect.prototype.eventOnKeyDown = function () {
            var _this = this;
            this.Field.onkeydown = function (event) {
                console.log('Event on key down');
                _this.getKeyCode(event, function (keyCode) {
                    console.log('Key Code ', keyCode);
                    if (keyCode === 40) {
                        if (_this.Field.innerHTML === '' && !document.getElementById(_this.suggestionBoxId)) {
                            if (_this.tempTime) {
                                clearTimeout(_this.tempTime);
                            }
                            _this.tempTime = window.setTimeout(function () {
                                _this.loadSuggestions();
                            }, _this.timeDelay);
                        } else {
                            _this.nextSuggestion();
                        }
                    }
                    if (keyCode === 38) {
                        _this.previousSuggestion();
                    }
                    if (keyCode === 27) {
                        // Keyboard ESC
                        _this.removeSuggestionBox();
                        _this.HiddenField.value = '';
                        _this.loadButtons();
                    }
                    if (keyCode === 8) {
                        // Keyboard Backspace
                        _this.HiddenField.value = '';
                        _this.loadButtons();
                    }
                    if (keyCode === 46 || (event.ctrlKey === true && keyCode === 88)) {
                        if (App.StringHelper.isTextSelected(_this.Field) === true) {
                            _this.HiddenField.value = '';
                            _this.loadButtons();
                        }
                    }
                    if (keyCode === 9) {
                        _this.selectSuggestion();
                    }
                });


            };
        };
        SingleSelect.prototype.getKeyCode = function (event, callBackFunction) {
            var code;

            if (event.keyCode !== undefined) {
                code = event.keyCode;
            }
            callBackFunction(code);
        };
        SingleSelect.prototype.loadSuggestions = function () {
            var _this = this;
            var errors = _this.checkParameterById();
            if (errors.length === 0) {
                var ajax = new App.AjaxHandler();
                ajax.setMethod('GET');
                ajax.setResponseType('json');
                ajax.setContentType('json');
                ajax.setUrl(_this.callBackRoute + '/ajax');
                ajax.setData(_this.parameters);
                ajax.execute(function (response) {
                    console.log('here');
                    if (Array.isArray(response)) {
                        console.log('there');
                        _this.suggestionData = response;
                        _this.buildSuggestionBox();
                    } else {
                        console.log('where');
                        _this.showMessage(response);
                    }
                }, function () {
                    console.log('shere');
                    _this.showMessage('No data found.');
                });
            } else {
                _this.showMessage(errors[0]);
            }


        };
        SingleSelect.prototype.checkParameterById = function () {
            var errors = [];
            var _this = this;
            var keys = Object.keys(_this.parameterByFields);
            var keyLength = keys.length;
            var temp = null;
            for (var i = 0; i < keyLength; i++) {
                temp = AppDom.getElementById(_this.parameterByFields[keys[i]]);
                if (temp) {
                    _this.addCallBackParameter(keys[i], temp.value);
                    if (temp.value === '' && _this.parameterByFields[keys[i]] !== _this.fieldId && _this.optionalParameters.hasOwnProperty(keys[i]) === false) {
                        if (_this.parameterLabels.hasOwnProperty(keys[i]) === true) {
                            errors.push('Required parameter for field ' + _this.parameterLabels[keys[i]]);
                        } else {
                            errors.push('Required parameter for field ' + _this.parameterByFields[keys[i]]);
                        }
                    }
                } else {
                    if (_this.optionalParameters.hasOwnProperty(keys[i]) === false) {
                        if (_this.parameterLabels.hasOwnProperty(keys[i]) === true) {
                            errors.push('Invalid parameter for field ' + _this.parameterByFields[keys[i]]);
                        } else {
                            errors.push('Invalid parameter for field ' + _this.parameterByFields[keys[i]]);
                        }
                    }
                }
            }
            return errors;
        };
        SingleSelect.prototype.buildSuggestionBox = function () {
            var _this = this;
            var i, li;
            this.createSuggestionBox();
            this.SuggestionList = document.createElement('ul');
            this.SuggestionList.id = this.suggestionListId;
            var lengthData = this.suggestionData.length;
            for (i = 0; i < lengthData; i += 1) {
                li = document.createElement('li');
                li.style.padding = '5px';
                li.innerHTML = this.suggestionData[i]['text'];
                li.value = i;
                li.onmouseover = function (event) {
                    _this.highlightSuggestion(event.target);
                };
                li.onmousedown = function () {
                    _this.selectSuggestion();
                };
                this.SuggestionList.appendChild(li);
            }
            this.setListStyle();
            this.suggestionBox.appendChild(this.SuggestionList);
            this.suggestionBox.style.visibility = 'visible';
        };
        SingleSelect.prototype.highlightSuggestion = function (obj) {
            var i, oNode;
            for (i = 0; i < this.SuggestionList.childNodes.length; i += 1) {
                oNode = this.SuggestionList.childNodes[i];
                if (oNode === obj) {
                    obj.id = this.selectionId;
                    obj.style.backgroundColor = 'blue';
                    obj.style.color = 'white';
                } else {
                    oNode.id = '';
                    oNode.style.backgroundColor = 'white';
                    oNode.style.color = 'black';
                }
            }
        };
        SingleSelect.prototype.createSuggestionBox = function () {
            var activeElement = document.activeElement, parentOfActiveElement = activeElement.parentNode;
            this.removeSuggestionBox();
            this.suggestionBox = document.createElement('div');
            this.suggestionBox.id = this.suggestionBoxId;
            this.suggestionBox.innerHTML = '';
            parentOfActiveElement.appendChild(this.suggestionBox);
            this.setSuggestionBoxStyle();
        };
        SingleSelect.prototype.setSuggestionBoxStyle = function () {
            var position = this.getFieldPosition(this.fieldId);
            this.suggestionBox.classList.add('single-select-box');
            this.suggestionBox.style.minWidth = position.width + 'px';
            this.suggestionBox.style.maxWidth = position.width * 1.5 + 'px';
            this.suggestionBox.style.top = position.height + 'px';
        };
        SingleSelect.prototype.setListStyle = function () {
            this.SuggestionList.classList.add('single-select-option');
        };
        SingleSelect.prototype.getFieldPosition = function (objId) {
            var width, currentOffsetTop, currentOffsetLeft, height, obj, position;
            obj = document.getElementById(objId);
            width = obj.offsetWidth;
            height = obj.offsetHeight;
            if (obj.offsetParent) {
                currentOffsetLeft = obj.offsetLeft;
                currentOffsetTop = obj.offsetTop;
                while (obj) {
                    currentOffsetLeft += (obj.offsetLeft - obj.scrollLeft);
                    if (obj.scrollTop) {
                        currentOffsetTop += obj.offsetTop - obj.scrollTop;
                    } else {
                        currentOffsetTop += obj.offsetTop;
                    }
                    obj = obj.offsetParent;
                }
            }
            position = {
                left: currentOffsetLeft,
                top: currentOffsetTop,
                right: currentOffsetLeft + width,
                bottom: currentOffsetTop + height,
                width: width,
                height: height
            };
            return position;
        };
        SingleSelect.prototype.selectSuggestion = function () {
            var index, selectionElement;
            if (document.getElementById(this.suggestionBoxId)) {
                selectionElement = document.getElementById(this.selectionId);
                if (selectionElement) {
                    index = selectionElement.value;
                    this.HiddenField.value = this.suggestionData[index].value;

                    this.Field.value = App.StringHelper.getHtmlDecode(selectionElement.innerHTML);
                    this.loadButtons();
                    this.removeSuggestionBox();
                    this.fillAutoCompleteFields(this.suggestionData[index]);
                }
            }
        };
        SingleSelect.prototype.fillAutoCompleteFields = function (data) {
            var fieldIds = Object.keys(this.autoCompleteFields);
            var countAutoComplete = fieldIds.length;
            var field, i;
            for (i = 0; i < countAutoComplete; i++) {
                field = document.getElementById(fieldIds[i]);
                if (field && data.hasOwnProperty(this.autoCompleteFields[fieldIds[i]])) {
                    field.value = data[this.autoCompleteFields[fieldIds[i]]];
                }
            }
        };
        SingleSelect.prototype.removeSuggestionBox = function () {
            var parentObj, childObj;
            childObj = document.getElementById(this.suggestionBoxId);
            if (childObj) {
                parentObj = childObj.parentNode;
                parentObj.removeChild(childObj);
                this.suggestionBox = null;
                this.SuggestionList = null;
            }
        };
        SingleSelect.prototype.nextSuggestion = function () {
            var layer = document.getElementById(this.suggestionBoxId),
                selected = document.getElementById(this.selectionId);
            if (layer !== null) {
                if (selected === null) {
                    if (this.SuggestionList.firstChild !== null) {
                        this.SuggestionList.firstChild.id = this.selectionId;
                        this.SuggestionList.firstChild.style.backgroundColor = 'blue';
                        this.SuggestionList.firstChild.style.color = 'white';
                    }
                } else {
                    if (selected.nextSibling === null) {
                        this.highlightSuggestion(this.SuggestionList.lastChild);
                    } else {
                        this.highlightSuggestion(selected.nextSibling);
                    }
                    layer.scrollTop = selected.offsetTop;
                }
            }
        };
        SingleSelect.prototype.previousSuggestion = function () {
            var layer = document.getElementById(this.suggestionBoxId),
                selected = document.getElementById(this.selectionId);
            if (layer !== null) {
                if (selected === null) {
                    if (this.SuggestionList.firstChild !== null) {
                        this.SuggestionList.lastChild.id = this.selectionId;
                        this.SuggestionList.lastChild.style.backgroundColor = 'blue';
                        this.SuggestionList.lastChild.style.color = 'white';
                    }
                } else {
                    if (selected.previousSibling === null) {
                        this.highlightSuggestion(this.SuggestionList.firstChild);
                    } else {
                        this.highlightSuggestion(selected.previousSibling);
                    }
                    layer.scrollTop = selected.offsetTop - selected.scrollHeight;
                }
            }
        };
        SingleSelect.prototype.showMessage = function (text) {
            this.createSuggestionBox();
            this.suggestionBox.innerHTML = '<ul style="text-decoration:none; list-style-type:none; margin:1px; padding:0px;"><li style="padding:5px;"><b><span style="color:red;">' + text + '</span></b></li></ul>';
        };

        return SingleSelect;
    }());
    App.Calendar = (function () {
        function Calendar(calId) {
            this.id = calId;
        }

        Calendar.prototype.create = function () {
            var _this = this;
            $('#' + _this.id).datetimepicker({
                ignoreReadonly: true,
                allowInputToggle: true,
                format: 'YYYY-MM-DD',
            });
        };
        return Calendar;
    }());
    App.NumberField = (function () {
        function NumberField(fieldId, thousand, decimal, decimalNumber) {
            this.id = fieldId;
            this.decimalSeparator = decimal;
            this.thousandSeparator = thousand;
            this.decimalNumber = decimalNumber;
            this.Field = document.getElementById(fieldId);
            this.NewField = null;
            this.decimalCode = 190;
            if (decimal === ',') {
                this.decimalCode = 188;
            }
            this.oldValue = this.Field.value.replace('.', this.decimalSeparator);
        }

        NumberField.prototype.create = function () {
            var _this = this;
            if (_this.Field) {
                _this.Field.type = 'hidden';
                _this.NewField = document.createElement('input');
                _this.NewField.id = _this.Field.id + '_number';
                _this.NewField.type = 'text';
                _this.NewField.readOnly = _this.Field.readOnly;
                _this.NewField.value = _this.doFormat(_this.oldValue, false);
                _this.NewField.setAttribute('class', _this.Field.className);
                _this.NewField.onkeyup = function () {
                    var val = _this.doParseToDefaultNumeric(_this.NewField.value, false);
                    var lastIndex = val.length - 1;
                    var lastNumber = val.substring(lastIndex);
                    var numberValue = val;
                    if (lastNumber === '.') {
                        numberValue = val.substring(0, lastIndex);
                    }
                    if (isNaN(numberValue) === false) {
                        _this.oldValue = _this.NewField.value;
                        _this.Field.value = val;
                    }
                    _this.NewField.value = _this.doFormat(_this.oldValue, false);

                };
                // _this.NewField.onblur = function () {
                //     console.log('ON BLUR', _this.oldValue);
                //     _this.Field.value = _this.doParseToDefaultNumeric(_this.oldValue, true);
                //     _this.NewField.value = _this.doFormat(_this.oldValue, true);
                // };
                var parent = _this.Field.parentNode;
                parent.appendChild(_this.NewField);
            }
        };
        NumberField.prototype.doFormat = function (value, onBlur) {
            var result = '';
            var divider = 3, arrNumber = [], strNumber = '', lengthValue = 0, modula = 0, interval = 0;
            value = value.trim();
            var firstValue = '';
            if (value.length > 0) {
                firstValue = value.substring(0, 1);
                if (firstValue === '-') {
                    value = value.substring(1);
                } else {
                    firstValue = '';
                }
                value = value.split(this.thousandSeparator).join('');
                arrNumber = value.split(this.decimalSeparator);
                strNumber = arrNumber[0];
                lengthValue = strNumber.length;
                if (lengthValue > divider) {
                    interval = parseInt(lengthValue / divider);
                    modula = lengthValue % divider;
                    if (modula > 0) {
                        result += strNumber.substring(0, modula);
                    }
                    for (var i = 0; i < interval; i++) {
                        if (result.length > 0) {
                            result += this.thousandSeparator;
                        }
                        result += strNumber.substring(modula, (modula + divider));
                        modula += divider;
                    }
                } else {
                    result = strNumber;
                }
                if (arrNumber.length > 1) {
                    if (onBlur === false) {
                        result += this.decimalSeparator;
                        if (arrNumber[1].length > 0) {
                            result += arrNumber[1];
                        }
                    } else {
                        if (arrNumber[1].length > 0) {
                            result += this.decimalSeparator + arrNumber[1];
                        }
                    }
                }
            }
            return firstValue + result;
        };
        NumberField.prototype.doParseToDefaultNumeric = function (value, onBlur) {
            var result = '', arrNumber = [];
            value = value.trim();
            var firstValue = '';
            if (value.length > 0) {
                firstValue = value.substring(0, 1);
                if (firstValue === '-') {
                    value = value.substring(1);
                } else {
                    firstValue = '';
                }
                value = value.split(this.thousandSeparator).join('');
                arrNumber = value.split(this.decimalSeparator);
                result = arrNumber[0];
                if (arrNumber.length > 1) {
                    if (onBlur === false) {
                        result += '.';
                        if (arrNumber[1].length > 0) {
                            result += arrNumber[1];
                        }
                    } else {
                        if (arrNumber[1].length > 0) {
                            result += '.' + arrNumber[1];
                        }
                    }
                }
            }
            return firstValue + result;
        };
        return NumberField;
    }());
    App.Time = (function () {
        function Time(id) {
            this.id = id;
        }

        Time.prototype.create = function () {
            var _this = this;
            console.log('Time Created ', document.getElementById(_this.id));
            $('#' + _this.id).datetimepicker({
                ignoreReadonly: true,
                allowInputToggle: true,
                format: 'HH:mm',
            });
        };
        return Time;
    }());
    App.Action = (function () {
        function Action(fieldId, formId, actionName) {
            this.fieldId = fieldId;
            this.formId = formId;
            this.actionFieldId = formId + '_action';
            this.actionName = actionName;
            this.showLoading = true;
        }

        Action.prototype.setEnableLoading = function (enable) {
            this.showLoading = enable;
        };
        Action.prototype.create = function () {
            var _this = this;
            var field = document.getElementById(_this.fieldId);
            console.log('FIELD ', field);
            if (field) {
                field.onclick = function () {
                    var formField = document.getElementById(_this.formId);
                    var actionField = document.getElementById(_this.actionFieldId);
                    if (formField && actionField) {
                        actionField.value = _this.actionName;
                        App.submitForm(_this.formId, !this.showLoading);
                    } else {
                        console.log('form not found.')
                    }
                };
            } else {
                console.log('field not found.')
            }
        };
        return Action;
    }());
    App.ModalHandler = function () {
        return {
            createModal: function (modalId, formId, actionName) {
                if (App.Modals[modalId] === undefined || App.Modals[modalId] === null) {
                    var mod = new App.Modal(modalId, formId, actionName);
                    mod.create();
                    App.Modals[modalId] = mod;
                }
                console.log('Modal created.');
                return App.Modals[modalId];
            },
            showModal: function (modalId) {
                if (App.Modals[modalId] !== undefined && App.Modals[modalId] !== null) {
                    App.Modals[modalId].show();
                }
            },
        };
    };
    App.Modal = (function () {
        function Modal(modalId, formId, actionName) {
            this.modalId = modalId;
            this.formId = formId;
            this.actionFieldId = formId + '_action';
            this.actionName = actionName;
            this.closeFunction = null;
        }

        Modal.prototype.getId = function () {
            return this.modalId;
        };
        Modal.prototype.setOnCloseFunction = function (closeFunction) {
            this.closeFunction = closeFunction;
        };
        Modal.prototype.create = function (isShow) {
            var _this = this;
            var okButton = document.getElementById(this.modalId + 'BtnOk');
            if (okButton) {
                okButton.onclick = function () {
                    var formField = document.getElementById(_this.formId);
                    var actionField = document.getElementById(_this.actionFieldId);
                    if (formField && actionField) {
                        actionField.value = _this.actionName;
                        App.submitForm(_this.formId);
                    } else {
                        console.log('form not found.')
                    }
                };
            }
            var closeButton = document.getElementById(this.modalId + 'BtnClose');
            if (closeButton) {
                closeButton.onclick = function () {
                    _this.close();
                };
            }
            if (isShow === true) {
                _this.show();
            }
        };
        Modal.prototype.show = function () {
            var modalElement = document.getElementById(this.modalId);
            if (modalElement) {
                $('#' + this.modalId).modal();
            }
        };
        Modal.prototype.close = function () {
            $('#' + this.modalId).modal('toggle');
            if (this.closeFunction !== null) {
                this.closeFunction();
            }
        };
        return Modal;
    }());
    App.ModalButton = (function () {
        function ModalButton(buttonId, modalId, route) {
            this.buttonId = buttonId;
            this.modalId = modalId;
            this.Parameters = {};
            this.fieldIds = [];
            this.route = route;
        }

        ModalButton.prototype.addParameter = function (key, value) {
            if (this.Parameters.hasOwnProperty(key) === false) {
                this.Parameters[key] = value;
            }
        };
        ModalButton.prototype.create = function () {
            var _this = this;
            var field = document.getElementById(_this.buttonId);
            if (field) {
                field.onclick = function () {
                    if (Object.keys(_this.Parameters).length > 1) {
                        _this.loadData();
                    } else {
                        _this.showModal();
                    }
                };
            }
        };
        ModalButton.prototype.loadData = function () {
            var _this = this;
            var ajax = new App.AjaxHandler();
            ajax.setMethod('GET');
            ajax.setResponseType('json');
            ajax.setContentType('json');
            ajax.setUrl('/' + _this.route + '/ajax');
            ajax.setData(_this.Parameters);
            ajax.execute(function (response) {
                if (response instanceof Object) {
                    _this.setFormData(response);
                }
                _this.showModal();
            }, function (err) {
                console.log('Error Modal ', err);
            });
        };
        ModalButton.prototype.setFormData = function (data) {
            this.fieldIds = Object.keys(data);
            var lengthKey = this.fieldIds.length;
            var field;
            for (var i = 0; i < lengthKey; i++) {
                field = document.getElementById(this.fieldIds[i]);
                if (field) {
                    if(this.fieldIds[i] === 'sik_type') {
                        console.log('sik type', field);
                    }
                    this.setFieldValue(field, data[this.fieldIds[i]]);
                }
            }
        };
        ModalButton.prototype.setFieldValue = function (field, fieldValue) {
            if (field.tagName === 'INPUT' && (field.type === 'text' || field.type === 'hidden')) {
                field.value = fieldValue;
            }else if(field.tagName === 'TEXTAREA') {
                field.innerHTML = fieldValue;
            }else if(field.tagName === 'SELECT') {
                field.value = fieldValue;
            } else if (field.tagName === 'DIV') {
                var inputs = field.getElementsByTagName('INPUT');
                var lengthInput = inputs.length;
                for (var i = 0; i < lengthInput; i++) {
                    if (inputs[i].type === 'radio' && inputs[i].value === fieldValue) {
                        inputs[i].checked = true;
                    }
                    if (inputs[i].type === 'radio' && inputs[i].checked === true && fieldValue === '') {
                        inputs[i].checked = false;
                    }
                }
            }
        };
        ModalButton.prototype.showModal = function () {
            var _this = this;
            if (App.Modals[this.modalId] !== undefined && App.Modals[this.modalId] !== null) {
                var onClose = function () {
                    var lengthKey = _this.fieldIds.length;
                    if (lengthKey > 0) {
                        var field;
                        for (var i = 0; i < lengthKey; i++) {
                            field = document.getElementById(_this.fieldIds[i]);
                            if (field) {
                                _this.setFieldValue(field, '');
                            }
                        }
                    }
                };
                App.Modals[this.modalId].setOnCloseFunction(onClose);
                App.Modals[this.modalId].show();
            }
            App.ModalHandler().showModal(this.modalId);
        };
        return ModalButton;
    }());
    App.SystemNotification = (function () {
        function SystemNotification(route, totalFunction, listFunction, listPagePath) {
            this.NotifButton = document.getElementById('notif_button');
            this.Route = route;
            this.callBackTotalFunction = totalFunction;
            this.callBackListFunction = listFunction;
            this.NotifBox = null;
            this.totalUnreadInterval = 0;
            this.totalUnreadActual = 0;
            this.rows = [];
            this.listPagePath = listPagePath;
        }

        SystemNotification.prototype.create = function () {
            var _this = this;
            if (_this.NotifButton) {
                _this.checkWindowNotification();
                _this.reload();
                _this.NotifButton.onclick = function () {
                    if (_this.NotifBox !== null && _this.totalUnreadInterval === _this.totalUnreadActual && _this.rows.length > 0) {
                        _this.generateListBox();
                    } else {
                        _this.loadListNotification();
                    }
                }
            }

        };
        SystemNotification.prototype.checkWindowNotification = function () {
            if (window.Notification && Notification.permission !== "denied") {
                Notification.requestPermission(function (status) {
                    if (Notification.permission !== status) {
                        Notification.permission = status;
                    }
                });
            }
        };

        SystemNotification.prototype.reload = function () {
            this.loadUnreadNotification();
        };
        SystemNotification.prototype.loadUnreadNotification = function () {
            var _this = this;
            var ajax = new App.AjaxHandler();
            var params = {};
            params['callBackFunction'] = _this.callBackTotalFunction;
            ajax.setMethod('GET');
            ajax.setResponseType('json');
            ajax.setContentType('json');
            ajax.setUrl('/' + _this.Route + '/ajax');
            ajax.setData(params);
            ajax.execute(function (response) {
                if (parseInt(response['total_unread']) !== _this.totalUnreadInterval) {
                    _this.totalUnreadInterval = response['total_unread'];
                    _this.setTotal();
                }
                if (response['new_rows'].length > 0) {
                    _this.createWindowNotification(response['new_rows']);
                }
            }, function (err) {
                console.log('Error Load Total Notification', err);
            });
        };
        SystemNotification.prototype.setTotal = function () {
            var fieldNumberId = 'notif_number';
            var fieldNumber = document.getElementById(fieldNumberId);
            if (fieldNumber) {
                fieldNumber.innerHTML = this.totalUnreadInterval.toString();
            } else {
                fieldNumber = document.createElement('span');
                fieldNumber.classList.add('badge');
                fieldNumber.classList.add('bg-red');
                fieldNumber.id = fieldNumberId;
                fieldNumber.innerHTML = this.totalUnreadInterval.toString();
            }
            this.NotifButton.appendChild(fieldNumber);
            var title = document.title;
            var indexNumber = title.indexOf(")");
            var newTitle = title.substring(indexNumber + 1, title.length);
            document.title = '(' + this.totalUnreadInterval.toString() + ') ' + newTitle;
        };
        SystemNotification.prototype.createWindowNotification = function (rows) {
            var _this = this;
            if (window.Notification && Notification.permission === "granted") {
                var lengthRows = rows.length;
                var intervalIndex = -1;
                var interval = window.setInterval(function () {
                    if (rows.hasOwnProperty(intervalIndex) === true) {
                        var notif = new Notification(rows[intervalIndex]['cp_name'], {
                            body: rows[intervalIndex]['nf_message'],
                            icon: App.getBaseUrl() + "/images/matalogix_logo.png",
                        });
                        var url = rows[intervalIndex]['nf_url'];
                        notif.onclick = function (event) {
                            event.preventDefault();
                            var myWindow = window.open(url, '_self');
                            myWindow.focus();
                        };
                        window.setTimeout(notif.close(), 1500);
                    }
                    intervalIndex++;
                    if (intervalIndex > lengthRows) {
                        window.clearInterval(interval);
                    }
                }, 300);
            }

        };
        SystemNotification.prototype.generateUrl = function (categoryRoute, route, params) {
            var pcRoute = '';
            if (categoryRoute) {
                pcRoute = '/' + categoryRoute;
            }
            var url = App.getBaseUrl() + '/' + route + pcRoute;
            var field = Object.keys(params);
            var fieldLength = field.length;
            for (var i = 0; i < fieldLength; i++) {
                if (i === 0) {
                    url += '?' + field[i] + '=' + params[field[i]];
                } else {
                    url += '&' + field[i] + '=' + params[field[i]];
                }
            }
            return url;
        };
        SystemNotification.prototype.loadListNotification = function () {
            var _this = this;
            var ajax = new App.AjaxHandler();
            var params = {};
            params['callBackFunction'] = _this.callBackListFunction;
            ajax.setMethod('GET');
            ajax.setResponseType('json');
            ajax.setContentType('json');
            ajax.setUrl('/' + _this.Route + '/ajax');
            ajax.setData(params);
            ajax.execute(function (response) {
                console.log('Load notification list.');
                _this.rows = response['rows'];
                _this.totalUnreadActual = response['total_unread'];
                if (_this.totalUnreadActual !== _this.totalUnreadInterval) {
                    _this.totalUnreadInterval = _this.totalUnreadActual;
                    _this.setTotal();
                }
                _this.generateListBox();
            }, function (err) {
                console.log('Error Load Rows Notification', err);
            });
        };
        SystemNotification.prototype.generateListBox = function () {
            var lengthRow = this.rows.length;
            if (lengthRow > 0) {
                if (this.NotifBox === null) {
                    this.NotifBox = document.createElement('ul');
                    this.NotifBox.setAttribute('class', 'dropdown-menu list-unstyled msg_list');
                    this.NotifBox.setAttribute('role', 'menu');
                } else {
                    while (this.NotifBox.firstChild) {
                        this.NotifBox.removeChild(this.NotifBox.firstChild);
                    }
                }
                for (var i = 0; i < lengthRow; i++) {
                    this.NotifBox.appendChild(this.generateRowElement(this.rows[i]));
                }
                if (lengthRow > 0) {
                    var liElement = document.createElement('li');
                    var divElement = document.createElement('div');
                    divElement.setAttribute('class', 'text-center');
                    var linkElement = document.createElement('a');
                    linkElement.setAttribute('href', App.getBaseUrl() + '/' + this.Route);
                    var content = '<span>';
                    content += 'See All ';
                    content += '<i class="fa fa-angle-right"></i>';
                    linkElement.innerHTML = content;
                    divElement.appendChild(linkElement);
                    liElement.appendChild(divElement);
                    this.NotifBox.appendChild(liElement);
                }
                var parent = this.NotifButton.parentNode;
                parent.appendChild(this.NotifBox);
                this.showList();
            }
        };
        SystemNotification.prototype.generateRowElement = function (row) {
            var liElement = document.createElement('li');
            if (row['nfr_read'] === 'N') {
                liElement.setAttribute('style', 'background-color : #e1e1e1');
            }
            var linkElement = document.createElement('a');
            linkElement.setAttribute('href', row['nf_url']);
            var content = '<span>';
            content += '<span style="font-weight: bold">' + row['cp_name'] + '</span>';
            content += '<span class="time">' + row['time'] + '</span>';
            content += '</span>';
            content += '<span class="message">' + row['nf_message'] + '</span>';
            linkElement.innerHTML = content;
            liElement.appendChild(linkElement);
            return liElement;
        };
        SystemNotification.prototype.showList = function () {
            if (this.NotifBox) {
                console.log('Notif box ', this.NotifBox);
                $('#notif_button').toggle();
                this.NotifButton.style.display = 'block';
            }
        };
        return SystemNotification;
    }());
    App.Chart = (function () {
        function Chart(id, idColumnGrid, route, type) {
            this.id = id;
            this.container = document.getElementById(id);
            this.route = route;
            this.type = type;
            this.parameters = {};
            this.btnReload = null;
            this.addParameter('id', id);
            this.titleId = id + 'title';
            this.panelId = id + 'panel';
            this.columnGridId = idColumnGrid + 'grid';
        }

        Chart.prototype.setReloadButton = function () {
            this.btnReload = document.getElementById(this.id + 'BtnReload');
        };
        Chart.prototype.addParameter = function (par, val) {
            this.parameters[par] = val;
        };
        Chart.prototype.setAutoReload = function (par) {
            console.log('interval ', par);
            var _this = this;
            window.setInterval(function () {
                _this.reloadContent();
            }, par);
        };
        Chart.prototype.create = function () {
            var _this = this;
            _this.loadData();
            if (_this.btnReload) {
                _this.btnReload.style.display = 'block';
                _this.btnReload.onclick = function () {
                    _this.reloadContent();
                };
            }

        };
        Chart.prototype.reloadContent = function () {
            var _this = this;
            if (_this.container) {
                while (_this.container.firstChild) {
                    _this.container.removeChild(_this.container.firstChild);
                }
            }
            _this.loadData();

        };
        Chart.prototype.loadData = function () {
            console.log('Load data Chart');
            var _this = this;
            var ajax = new App.AjaxHandler();
            ajax.setMethod('GET');
            ajax.setResponseType('json');
            ajax.setContentType('json');
            ajax.setUrl('/' + _this.route);
            ajax.setData(_this.parameters);
            ajax.execute(function (response) {
                console.log('response ', response);
                if (response instanceof Object) {
                    _this.show(response);
                    _this.setTitle(response);
                    _this.setColor(response);
                    _this.setColumnGridClass(response);
                    console.log('response object');
                } else {
                    console.log('response not object');
                }
            }, function () {
                console.log('shere');
            });

        };
        Chart.prototype.setTitle = function(result) {
            if (document.getElementById(this.titleId)) {
                document.getElementById(this.titleId).innerHTML= result['title'];
            }

        };
        Chart.prototype.setColor = function(result) {
            if (document.getElementById(this.panelId)) {
                document.getElementById(this.panelId).style.background = result['color'];
            }
        };
        Chart.prototype.setColumnGridClass = function(result) {
            document.getElementById(this.columnGridId).className = result['gridClass'];
        };
        Chart.prototype.show = function (result) {
            if (this.type === 'chart') {
                this.showChart(result);
            } else if (this.type === 'table') {
                this.showTable(result);
            } else {
                this.showWidget(result);
            }
        };
        Chart.prototype.showChart = function (result) {
            var _this = this;
            var data = result['data'];
            var keys = Object.keys(data), keyLength = keys.length, i = 0, temp = null;
            var chartData = {};
            for (i = 0; i < keyLength; i++) {
                temp = data[keys[i]];
                if (Array.isArray(temp) === false || temp.length > 0) {
                    chartData[keys[i]] = temp;
                }
            }
            Highcharts.chart(_this.id, chartData);
        };
        Chart.prototype.showTable = function (result) {
            if (this.container) {
                this.container.innerHTML = result['data'];
            }
        };
        Chart.prototype.showWidget = function (result) {
            if (this.container) {
                this.container.innerHTML = result['data'];
            }
        };
        return Chart;
    }());
    App.SingleSelectTable = (function () {
        function SingleSelectTable(fieldId, hiddenId) {
            this.fieldId = fieldId;
            this.Field = document.getElementById(fieldId);
            this.HiddenField = document.getElementById(hiddenId);
            this.readOnlyField = false;
            this.callBackRoute = '';
            this.BtnSearch = AppDom.getElementById(this.fieldId + '_src_btn');
            this.BtnDelete = AppDom.getElementById(this.fieldId + '_delete_btn');
            this.parameters = {};
            this.optionalParameters = {};
            this.parameterByFields = {};
            this.parameterLabels = {};
            this.autoCompleteFields = {};
            this.onClearFields = [];
            this.filterFields = [];
            this.tableColumns = [];
            this.modalId = this.fieldId + '_mdl';
            this.parentModalId = '';
            this.Table = AppDom.getElementById(this.fieldId + '_tbl');
            this.TableBody = AppDom.getElementById(this.fieldId + '_tbl_body');
            this.minTextLength = 2;
            this.timeDelay = 500;
            this.tempTime = null;
            this.suggestionData = [];
            this.selectedData = null;
            this.valueCode = '';
            this.labelCode = '';
            this.suggestionBoxId = this.fieldId + '_suggestion_box';
            this.suggestionBox = null;
            this.isModalShow = false;
        }

        SingleSelectTable.prototype.setReadOnly = function (enable) {
            this.readOnlyField = enable;
        };
        SingleSelectTable.prototype.setValueCode = function (reference) {
            this.valueCode = reference;
        };
        SingleSelectTable.prototype.setParentModal = function (parentId) {
            this.parentModalId = parentId;
        };
        SingleSelectTable.prototype.setLabelCode = function (reference) {
            this.labelCode = reference;
        };
        SingleSelectTable.prototype.setCallBackRoute = function (callBackRoute) {
            this.callBackRoute = callBackRoute;
        };
        SingleSelectTable.prototype.setCallBackFunction = function (callBackFunction) {
            this.addCallBackParameter('callBackFunction', callBackFunction);
        };
        SingleSelectTable.prototype.addCallBackParameter = function (parKey, parValue) {
            this.parameters[parKey] = parValue;
        };
        SingleSelectTable.prototype.addAutoCompleteField = function (parKey, parValue) {
            this.autoCompleteFields[parKey] = parValue;
        };
        SingleSelectTable.prototype.addCallBackParameterById = function (parKey, fieldId) {
            this.parameterByFields[parKey] = fieldId;
        };
        SingleSelectTable.prototype.addOptionalCallBackParameterById = function (parKey, fieldId) {
            this.optionalParameters[parKey] = fieldId;
        };
        SingleSelectTable.prototype.addParameterLabel = function (parKey, label) {
            this.parameterLabels[parKey] = label;
        };
        SingleSelectTable.prototype.addFieldOnClear = function (fieldId) {
            if (this.onClearFields.indexOf(fieldId) === -1) {
                this.onClearFields.push(fieldId);
            }
        };
        SingleSelectTable.prototype.addFilterField = function (fieldId) {
            if (this.filterFields.indexOf(fieldId) === -1) {
                this.filterFields.push(fieldId);
            }
        };
        SingleSelectTable.prototype.addTableColumn = function ($columnId) {
            if (this.tableColumns.indexOf($columnId) === -1) {
                this.tableColumns.push($columnId);
            }
        };
        SingleSelectTable.prototype.createField = function () {
            if (this.readOnlyField === false) {
                this.Field.style.background = "99% 3px no-repeat  url('" + App.getBaseUrl() + "/images/search.png')";
                this.Field.style.backgroundColor = 'white';
                this.eventOnFocus();
                this.eventOnBlur();
            }
            this.setButtonsEvent();
            this.loadButtons();
            var field = null, i;
            var lengthFilter = this.filterFields.length;
            for (i = 0; i < lengthFilter; i++) {
                field = document.getElementById(this.filterFields[i] + '_' + this.fieldId);
                if (field) {
                    this.setFilterEvent(field, this.filterFields[i]);
                }
            }
            var btnModalClose = document.getElementById(this.modalId + 'BtnClose');
            if (btnModalClose) {
                var _this = this;
                btnModalClose.onclick = function () {
                    _this.closeModal();
                };
            }
        };
        SingleSelectTable.prototype.resetFilterEvent = function () {
            var field = null, i;
            var lengthFilter = this.filterFields.length;
            for (i = 0; i < lengthFilter; i++) {
                field = document.getElementById(this.filterFields[i] + '_' + this.fieldId);
                if (field) {
                    field.value = '';
                    this.addCallBackParameter(this.filterFields[i], '');
                }
            }
        };
        SingleSelectTable.prototype.setFilterEvent = function (field, parId) {
            var _this = this;
            // Key Oress
            field.onkeypress = function (event) {
                console.log('On key press');
                _this.getKeyCode(event, function (keyCode) {
                    console.log('Key Code ', keyCode);
                    if (keyCode === 13) {
                        event.preventDefault();
                    }
                });
            };
            // Key Up
            field.onkeyup = function (event) {
                console.log('On key up');
                _this.getKeyCode(event, function (keyCode) {
                    console.log('Key Code ', keyCode);
                    if ((keyCode >= 48 && keyCode <= 111) || (keyCode >= 186 && keyCode <= 226) || keyCode === 8 || keyCode === 46) {
                        if (field.value.length >= _this.minTextLength) {
                            console.log('Get in here');
                            if (_this.tempTime) {
                                clearTimeout(_this.tempTime);
                            }
                            _this.addCallBackParameter(parId, field.value);
                            _this.tempTime = window.setTimeout(function () {
                                console.log('call this');
                                _this.doValidateParameter();
                            }, _this.timeDelay);
                        } else {
                            if (_this.parameters.hasOwnProperty(parId) && _this.parameters[parId].length >= _this.minTextLength) {
                                if (_this.tempTime) {
                                    clearTimeout(_this.tempTime);
                                }
                                _this.addCallBackParameter(parId, '');
                                _this.tempTime = window.setTimeout(function () {
                                    console.log('call this');
                                    _this.doValidateParameter();
                                }, _this.timeDelay);
                            }
                            _this.addCallBackParameter(parId, '');
                        }
                    }
                });
            };
        };
        SingleSelectTable.prototype.getKeyCode = function (event, callBackFunction) {
            var code;

            if (event.keyCode !== undefined) {
                code = event.keyCode;
            }
            callBackFunction(code);
        };
        SingleSelectTable.prototype.loadButtons = function () {
            if (this.BtnSearch) {
                this.BtnSearch.style.display = 'inline';
                if (this.readOnlyField) {
                    this.BtnSearch.style.color = 'transparent';
                    this.BtnSearch.style.cursor = 'default';
                }
            }
            if (this.BtnDelete) {
                if (this.Field.value === '' || this.HiddenField.value === '') {
                    this.BtnDelete.style.display = 'none';
                } else {
                    this.BtnDelete.style.display = 'inline';
                    if (this.readOnlyField === true) {
                        this.BtnDelete.style.color = 'transparent';
                        this.BtnDelete.style.cursor = 'default';
                    }
                }
            }
        };
        SingleSelectTable.prototype.setButtonsEvent = function () {
            var _this = this;
            if (_this.BtnSearch) {
                _this.BtnSearch.onclick = function () {
                    _this.doValidateParameter();
                };
            }
            if (_this.BtnDelete) {
                _this.BtnDelete.onclick = function () {
                    _this.resetSingleSelect();
                };
            }
        };
        SingleSelectTable.prototype.closeModal = function () {
            if (this.isModalShow === true) {
                $('#' + this.modalId).modal('toggle');
                if (this.parentModalId.length > 0 && document.getElementById(this.parentModalId)) {
                    $('#' + this.parentModalId).modal();
                }
                this.isModalShow = false;
            }
        };
        SingleSelectTable.prototype.resetSingleSelect = function () {
            this.Field.value = '';
            this.HiddenField.value = '';
            this.loadButtons();
            this.clearReferenceField();
        };
        SingleSelectTable.prototype.showModal = function () {
            if (this.isModalShow === false) {
                if (this.parentModalId.length > 0 && document.getElementById(this.parentModalId)) {
                    $('#' + this.parentModalId).modal('toggle');
                }
                var modalElement = document.getElementById(this.modalId);
                if (modalElement) {
                    $('#' + this.modalId).modal();
                }
                this.isModalShow = true;
            }
        };
        SingleSelectTable.prototype.clearReferenceField = function () {
            var countField = this.onClearFields.length;
            var fieldIds = Object.keys(this.autoCompleteFields);
            var countAutoComplete = fieldIds.length;
            var field, i;
            for (i = 0; i < countField; i++) {
                field = document.getElementById(this.onClearFields[i]);
                if (field) {
                    field.value = '';
                }
            }
            for (i = 0; i < countAutoComplete; i++) {
                field = document.getElementById(fieldIds[i]);
                if (field) {
                    field.value = '';
                }
            }
        };
        SingleSelectTable.prototype.eventOnBlur = function () {
            var _this = this;
            this.Field.onblur = function () {
                console.log('On Blur Event action');
                if (_this.HiddenField.value === '') {
                    _this.resetSingleSelect();
                }
                _this.removeSuggestionBox();
            };
        };
        SingleSelectTable.prototype.eventOnFocus = function () {
            var _this = this;
            this.Field.onfocus = function () {
                console.log('On focus action');
                _this.removeSuggestionBox();
            };
        };
        SingleSelectTable.prototype.doValidateParameter = function () {
            var errors = this.loadParameterById();
            if (errors.length === 0) {
                this.loadSuggestions();
            } else {
                this.showMessage(errors[0]);
            }

        };
        SingleSelectTable.prototype.loadSuggestions = function () {
            while (this.TableBody.firstChild) {
                this.TableBody.removeChild(this.TableBody.firstChild);
            }
            var _this = this;
            var ajax = new App.AjaxHandler();
            ajax.setMethod('GET');
            ajax.setResponseType('json');
            ajax.setContentType('json');
            ajax.setUrl(_this.callBackRoute + '/ajax');
            ajax.setData(_this.parameters);
            ajax.execute(function (response) {
                if (Array.isArray(response)) {
                    _this.suggestionData = response;
                    _this.buildTableSuggestion();
                } else {
                    _this.showMessage('No data found.');
                }
            }, function () {
                _this.showMessage('No data found.');
            });


        };
        SingleSelectTable.prototype.loadParameterById = function () {
            var errors = [];
            var _this = this;
            var keys = Object.keys(_this.parameterByFields);
            var keyLength = keys.length;
            var temp = null;
            for (var i = 0; i < keyLength; i++) {
                temp = AppDom.getElementById(_this.parameterByFields[keys[i]]);
                if (temp) {
                    _this.addCallBackParameter(keys[i], temp.value);
                    if (temp.value === '' && _this.parameterByFields[keys[i]] !== _this.fieldId && _this.optionalParameters.hasOwnProperty(keys[i]) === false) {
                        if (_this.parameterLabels.hasOwnProperty(keys[i]) === true) {
                            errors.push('Required parameter for field ' + _this.parameterLabels[keys[i]]);
                        } else {
                            errors.push('Required parameter for field ' + _this.parameterByFields[keys[i]]);
                        }
                    }
                } else {
                    if (_this.optionalParameters.hasOwnProperty(keys[i]) === false) {
                        if (_this.parameterLabels.hasOwnProperty(keys[i]) === true) {
                            errors.push('Invalid parameter for field ' + _this.parameterByFields[keys[i]]);
                        } else {
                            errors.push('Invalid parameter for field ' + _this.parameterByFields[keys[i]]);
                        }
                    }
                }
            }
            return errors;
        };
        SingleSelectTable.prototype.buildTableSuggestion = function () {
            var _this = this;
            var i, j, newRow, newCell, newText, data, btn, icon;
            var lengthData = this.suggestionData.length;
            var lengthColumn = this.tableColumns.length;
            for (i = 0; i < lengthData; i += 1) {
                data = this.suggestionData[i];
                // Insert a row at the end of the table
                newRow = this.TableBody.insertRow(-1);

                // Insert a cell in the row at index 0
                newCell = newRow.insertCell(0);
                newText = document.createTextNode(i + 1);
                newCell.appendChild(newText);
                newCell.setAttribute('style', 'text-align:center;');

                // Insert cell in the row
                for (j = 0; j < lengthColumn; j += 1) {
                    newCell = newRow.insertCell(j + 1);
                    newText = '';
                    if (data.hasOwnProperty(this.tableColumns[j])) {
                        newText = data[this.tableColumns[j]];
                    }
                    newCell.innerHTML = newText;
                    // newCell.appendChild(document.createTextNode(newText));
                }

                _this.addSelectButton(newRow, data, lengthColumn + 1);

            }
            this.showModal();
        };
        SingleSelectTable.prototype.addSelectButton = function (row, data, index) {
            var _this = this;
            var newCell, btn, icon;
            // insert action select
            newCell = row.insertCell(index);
            icon = document.createElement('i');
            icon.setAttribute('class', 'fa fa-check');
            btn = document.createElement('button');
            btn.setAttribute('type', 'button');
            btn.setAttribute('class', 'btn btn-primary btn-sm');
            btn.appendChild(icon);
            btn.appendChild(document.createTextNode(' Select'));
            btn.onclick = function () {
                _this.selectSuggestion(data);
            };
            newCell.appendChild(btn);
            newCell.setAttribute('style', 'text-align:center;');
        };
        SingleSelectTable.prototype.selectSuggestion = function (data) {
            var hiddenValue = '';
            var labelValue = '';
            if (data.hasOwnProperty(this.valueCode) === true) {
                hiddenValue = data[this.valueCode];
                if (this.selectedData !== null && data[this.valueCode] !== this.selectedData[this.valueCode]) {
                    this.clearReferenceField();
                }
            }
            if (data.hasOwnProperty(this.labelCode) === true) {
                labelValue = data[this.labelCode];
            }
            this.HiddenField.value = hiddenValue;
            this.Field.value = labelValue;
            this.selectedData = data;
            this.fillAutoCompleteFields(data);
            this.loadButtons();
            this.resetFilterEvent();
            this.closeModal();
        };
        SingleSelectTable.prototype.fillAutoCompleteFields = function (data) {
            var fieldIds = Object.keys(this.autoCompleteFields);
            var countAutoComplete = fieldIds.length;
            var field, i;
            for (i = 0; i < countAutoComplete; i++) {
                field = document.getElementById(fieldIds[i]);
                if (field && data.hasOwnProperty(this.autoCompleteFields[fieldIds[i]])) {
                    field.value = data[this.autoCompleteFields[fieldIds[i]]];
                }
            }
        };
        SingleSelectTable.prototype.showMessage = function (text) {
            this.createSuggestionBox();
            this.suggestionBox.innerHTML = '<ul style="text-decoration:none; list-style-type:none; margin:1px; padding:0px;"><li style="padding:5px;"><b><span style="color:red;">' + text + '</span></b></li></ul>';
        };
        SingleSelectTable.prototype.createSuggestionBox = function () {
            var activeElement = this.Field, parentOfActiveElement = activeElement.parentNode;
            this.removeSuggestionBox();
            this.suggestionBox = document.createElement('div');
            this.suggestionBox.id = this.suggestionBoxId;
            this.suggestionBox.innerHTML = '';
            parentOfActiveElement.appendChild(this.suggestionBox);
            this.setSuggestionBoxStyle();
        };
        SingleSelectTable.prototype.setSuggestionBoxStyle = function () {
            var position = this.getFieldPosition(this.fieldId);
            this.suggestionBox.classList.add('single-select-box');
            this.suggestionBox.style.minWidth = position.width + 'px';
            this.suggestionBox.style.maxWidth = position.width * 1.5 + 'px';
            this.suggestionBox.style.top = position.height + 'px';
        };
        SingleSelectTable.prototype.getFieldPosition = function (objId) {
            var width, currentOffsetTop, currentOffsetLeft, height, obj, position;
            obj = document.getElementById(objId);
            width = obj.offsetWidth;
            height = obj.offsetHeight;
            if (obj.offsetParent) {
                currentOffsetLeft = obj.offsetLeft;
                currentOffsetTop = obj.offsetTop;
                while (obj) {
                    currentOffsetLeft += (obj.offsetLeft - obj.scrollLeft);
                    if (obj.scrollTop) {
                        currentOffsetTop += obj.offsetTop - obj.scrollTop;
                    } else {
                        currentOffsetTop += obj.offsetTop;
                    }
                    obj = obj.offsetParent;
                }
            }
            position = {
                left: currentOffsetLeft,
                top: currentOffsetTop,
                right: currentOffsetLeft + width,
                bottom: currentOffsetTop + height,
                width: width,
                height: height
            };
            return position;
        };
        SingleSelectTable.prototype.removeSuggestionBox = function () {
            var parentObj, childObj;
            childObj = document.getElementById(this.suggestionBoxId);
            if (childObj) {
                parentObj = childObj.parentNode;
                parentObj.removeChild(childObj);
                this.suggestionBox = null;
            }
        };

        return SingleSelectTable;
    }());
    App.PdfButton = (function () {
        function PdfButton(buttonId, route) {
            this.buttonId = buttonId;
            this.Button = document.getElementById(buttonId);
            this.modalId = buttonId + '_mdl';
            this.ModalBtnOk = document.getElementById(buttonId + '_mdlBtnOk');
            this.ModalBtnCancel = document.getElementById(buttonId + '_mdlBtnClose');
            this.SelectField = document.getElementById(buttonId + '_select');
            this.Parameters = {};
            this.route = route;
        }

        PdfButton.prototype.addParameter = function (key, value) {
            this.Parameters[key] = value;
        };
        PdfButton.prototype.create = function () {
            this.ModalBtnOk.style.display = 'none';
            this.btnEvent();
            this.onChangeTemplate();
            this.loadBtnOkModal();
            this.loadBtnCloseModal();
        };
        PdfButton.prototype.btnEvent = function () {
            var _this = this;
            if (_this.Button) {
                _this.Button.onclick = function () {
                    console.log('Clicked');
                    _this.showModal();
                };
            }
        };
        PdfButton.prototype.onChangeTemplate = function () {
            var _this = this;
            if (_this.SelectField) {
                _this.SelectField.onchange = function () {
                    console.log('Templete Selected');
                    var value = this.value;
                    if (value.length > 0) {
                        _this.addParameter('path', value);
                        _this.ModalBtnOk.style.display = 'inline';
                    } else {
                        _this.ModalBtnOk.style.display = 'none';
                    }
                };
            }
        };
        PdfButton.prototype.loadBtnOkModal = function () {
            var _this = this;
            if (_this.ModalBtnOk) {
                _this.ModalBtnOk.onclick = function () {
                    console.log('Modal OK');
                    var params = [];
                    params.push('pv=1');
                    var key2 = Object.keys(_this.Parameters);
                    var keyLength2 = key2.length;
                    for (var j = 0; j < keyLength2; j++) {
                        if (_this.Parameters[key2[j]] !== '') {
                            params.push(key2[j] + '=' + _this.Parameters[key2[j]]);
                        }
                    }
                    _this.closeModal();
                    App.popup(App.getBaseUrl() + _this.route + '?' + params.join('&'));
                };
            }
        };
        PdfButton.prototype.loadBtnCloseModal = function () {
            var _this = this;
            if (_this.ModalBtnCancel) {
                _this.ModalBtnCancel.onclick = function () {
                    console.log('Modal No');
                    _this.closeModal();
                };
            }
        };
        PdfButton.prototype.showModal = function () {
            console.log('Modal ', this.modalId);
            if (document.getElementById(this.modalId)) {
                console.log('show modal');
                $('#' + this.modalId).modal();
            }
        };
        PdfButton.prototype.closeModal = function () {
            if (this.SelectField) {
                this.SelectField.selectedIndex = 0;
                this.Parameters['path'] = '';
                this.ModalBtnOk.style.display = 'none';
            }
            $('#' + this.modalId).modal('toggle');
        };
        return PdfButton;
    }());
})
(App || (App = {}));
