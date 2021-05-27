var AppDom;
(function (AppDom) {
    AppDom.getElementById = function (domId) {
        return document.getElementById(domId);
    };
    AppDom.removeElementById = function (domId) {
        var parentObj, childObj = HtmlDom.getElementById(domId);
        if (childObj) {
            parentObj = childObj.parentNode;
            parentObj.removeChild(childObj);
        }
    };
})
(AppDom || (AppDom = {}));
