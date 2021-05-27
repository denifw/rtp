var Hpp;
(function (Hpp) {
    Hpp.Calculate = function () {
        function Calculate() {
            this.bbm = document.getElementById('bbm_ltr');
            this.fee = document.getElementById('driver_fee');
            this.distance = document.getElementById('distance');
            this.fuelConsume = document.getElementById('fuelConsume');
            this.thousandSeparator = document.getElementById('thousandSeparator');
            this.setOnChange();
        }

        Calculate.prototype.setOnChange = function () {
            var _this = this;

            this.bbm.onkeyup = function () {
                _this.setAlert();
            }
            this.fee.onkeyup = function () {
                _this.setAlert();
            }

        };

        Calculate.prototype.setAlert = function () {
            var bbmVal = this.bbm.value;
            var driverFee = this.fee.value;
            var distance = this.distance.value;
            var fuelConsume = this.fuelConsume.value;
            var thousandSeparator = this.thousandSeparator.value;
            var totalFuelUsed = (parseInt(distance) / parseInt(fuelConsume));
            var totalFuelCosts = (parseInt(Math.ceil(totalFuelUsed)) * parseInt(bbmVal));
            var total = (parseInt(totalFuelCosts) + parseInt(driverFee));

            var number_string = total.toString(),
                result = number_string.length % 3,
                formatSeparator = thousandSeparator.toString(),
                rupiah = number_string.substr(0, result),
                thousand = number_string.substr(result).match(/\d{3}/g);

            if (thousand) {
                separator = result ? formatSeparator : '';
                rupiah += separator + thousand.join(formatSeparator);
            }
            if (isNaN(total)) {
                document.getElementById('rsl').innerHTML = 0;
            } else {
                document.getElementById('rsl').innerHTML = rupiah;
            }

        };


        return Calculate;
    }();
})
(Hpp || (Hpp = {}));
window.addEventListener('load', function (event) {
    return new Hpp.Calculate();
});
