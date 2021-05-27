var Job;
(function (Job) {
    Job.JobOrder = (function () {
        function JobOrder(serviceMenuId, serviceSubMenuId) {
            this.serviceMenuId = serviceMenuId;
            this.serviceSubMenuId = serviceSubMenuId;
            this.serviceIds = {};
            document.getElementById(this.serviceMenuId).style.display = 'block';
            document.getElementById(this.serviceSubMenuId).style.display = 'none';

        }

        JobOrder.prototype.AddServiceId = function (parKey, parValue) {
            this.serviceIds[parKey] = parValue;
        };

        JobOrder.prototype.AddServiceEvent = function () {
            var home = document.getElementById('service-home');
            var key = Object.keys(this.serviceIds);
            var keyLength = key.length;
            for (var i = 0; i < keyLength; i++) {
                var btn = document.getElementById(this.serviceIds[key[i]]);
                if (btn) {
                    var _this = this;
                    btn.onclick = function (event) {
                        _this.ShowService(event);
                    };
                }
            }
            home.onclick = function () {
                document.getElementById(_this.serviceMenuId).style.display = 'block';
                document.getElementById(_this.serviceSubMenuId).style.display = 'none';
            };
        };

        JobOrder.prototype.ShowService = function (event) {
            var serviceTarget = event.currentTarget.id;
            document.getElementById(this.serviceMenuId).style.display = 'none';
            document.getElementById(this.serviceSubMenuId).style.display = 'block';
            var key = Object.keys(this.serviceIds);
            var keyLength = key.length;
            for (var i = 0; i < keyLength; i++) {
                var service = this.serviceIds[key[i]];
                if (service === serviceTarget) {
                    document.getElementById('service_' + service).style.display = 'block';
                } else {
                    document.getElementById('service_' + service).style.display = 'none';
                }
            }
            document.getElementById('service-title').innerHTML = event.currentTarget.id;
        };

        return JobOrder;
    }());
})
(Job || (Job = {}));
// window.addEventListener('load', function (event) {
//     return new Detail.JobOrder();
// });
