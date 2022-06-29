(function (require, $) {
    angular.module('piwikApp').controller('SDGWebStatisticsController', SDGWebStatisticsController);
    var ajaxHelper = require('ajaxHelper');
    var ajax = new ajaxHelper();

    function SDGWebStatisticsController($scope) {
        var vm = this;
        vm.sendApiRequestLoading = false;

        vm.sendApiRequest = function() {
            vm.sendApiRequestLoading = true;
            ajax.addParams({
                module: 'SDGWebStatistics',
                action: 'sendDataToApi'
            }, 'get');
            ajax.setCallback(function (response) {
                console.log(response);
                vm.sendApiRequestLoading = false;
                location.reload();
            });
            ajax.setFormat('json');
            ajax.send();
        }
    }
})(require, jQuery);