/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

angular.module('WholesaleApp').controller('PropertyDetailController', [
    "$scope",
    "$http",
    function (localScope, http) {
        function loadProperty() {
            var prop = $.cookie("property");
            if (prop) {
                prop = JSON.parse(prop);
                prop.assignment_detail.closing_date = new Date(prop.assignment_detail.closing_date);
            }
            return prop;
        }
        localScope.buyerCount = $.cookie("count");
        localScope.buyerValue = $.cookie("value");
        localScope.feeForService = $.cookie("fee");
        localScope.property = loadProperty();
        if (typeof localScope.property == "undefined") {
            localScope.property = {
                sqft: 0,
                beds: 0,
                baths: {
                    full: 0,
                    half: 0,
                    three_quarter: 0
                },
                deal_type: "",
                atn: null,
                street: null,
                pricing: {
                    as_is: 0,
                    arv: 0,
                    repairs: 0
                },
                videoURLs: "",
                assignment_detail: {
                    fee: 0,
                    contract_price: 0,
                    closing_date: 0,
                    escrow: {
                        name: "",
                        url: ""
                    }
                },
                double_close_detail: {
                    desired_price: 0,
                    alternative_action: "",
                    bottom_price: 0
                },
                about_seller: "",
                description: ""
            };
        }
        localScope.selected_help = "";
        var scopeReference = localScope;
        localScope.displayHelp = function (help_id) {
            scopeReference.closeTips();
            $("#" + help_id).removeClass("hidden");
            setTimeout(
                    function () {
                        scopeReference.closeTips()
                    },
                    10000
                    );
        }
        localScope.closeTips = function () {
            $(".helpText").addClass("hidden");
        }
        function saveProperty(property) {
            $.cookie("property", JSON.stringify(property), {expires: 1});
            return true;
        }
        var $http = http;
        function loadNextPageWithData(result) {
            if( typeof result.data.count !== "undefined" ){
                $.cookie("count", result.data.count, {expires: 1});
                $.cookie("value", result.data.value, {expires: 1});
                $.cookie("fee", result.data.fee, {expires:1} );
                $("#nextPage")[0].click();
            }
        }
        function handleError(result) {
            if (typeof result.status != "undefined" && typeof result.statusText != "undefined") {
                var msg = "Error( " + result.status + " ): " + result.statusText + "\n" + result.config.url;
                alert(msg);
            } else {
                debugger;
            }
        }
        localScope.advance = function (property) {
            saveProperty(property);
            var property2 = loadProperty();
            if (property2.atn == property.atn) {
                var config = {
                    headers: {"Content-Type": "application/x-www-form-urlencoded; charset=utf-8"}
                };
                var successCallback = loadNextPageWithData;
                var errorCallback = handleError;
                $http.post('php/countPropertyReach.php', {property: property}, config)
                        .then(successCallback, errorCallback);
            }
        }
        localScope.closeTips();
        localScope.totalInvestment = function (property) {
            var investment = 0;
            investment = property.pricing.repairs;
            switch (property.deal_type) {
                case 'assignment':
                    investment += property.assignment_detail.contract_price + property.assignment_detail.fee;
                    break;
                case 'double_close':
                    investment += property.double_close_detail.desired_price;
                    break;
            }
            return investment;
        }
        var getTotalInvestment = localScope.totalInvestment;
        localScope.percentOfMarket = function (property) {
            var arv = property.pricing.arv;
            var totalInvestment = getTotalInvestment(property);
            return totalInvestment * 100 / arv;
        }
        localScope.extractUrls = function (string) {
            debugger;
            return [];
        }
        function loadPaymentPage(result){
            if( result.data.success == 1 ){
                var url = result.data.nextUrl;
                $( "#nextPage" )[0].attr( "href", url );
                $( "#nextPage" )[0].click();
            }
        }
        localScope.confirmPropertyRequest = function (property) {
            saveProperty(property);
            var config = {
                headers: {"Content-Type": "application/x-www-form-urlencoded; charset=utf-8"}
            };
            var successCallback = loadPaymentPage;
            var errorCallback = handleError;
            $http.post('php/saveConfirmedPropertyDetails.php', {property: property}, config )
                    .then( successCallback, errorCallback );
            
        }
    }
]
        );