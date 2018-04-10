'use strict';

(function (window) {
    // NodeList.forEach()
    if (window.NodeList && !NodeList.prototype.forEach) {
        NodeList.prototype.forEach = function (call, arg) {
            arg = arg || window;

            for (let a = 0; a < this.length; a++) {
                call.call(arg, this[a], a, this);
            }
        };
    }
})(window);
