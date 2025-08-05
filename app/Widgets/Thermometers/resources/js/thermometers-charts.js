

(function() {
    'use strict';

    if (!window.DashboardThermometers) {

        return;
    }

    function drawTemperatureChart(canvas, data, days) {
        days = days || 1;

        if (!canvas) {

            return;
        }

        var ctx = canvas.getContext('2d');
        var width = canvas.width;
        var height = canvas.height;

        ctx.clearRect(0, 0, width, height);

        if (!data || data.length === 0) {

            ctx.font = '16px -apple-system, BlinkMacSystemFont, sans-serif';
            ctx.fillStyle = '#999';
            ctx.textAlign = 'center';
            ctx.fillText('Aucune donnée disponible', width / 2, height / 2);
            return;
        }

        var margin = { top: 20, right: 60, bottom: 60, left: 60 };
        var chartWidth = width - margin.left - margin.right;
        var chartHeight = height - margin.top - margin.bottom;

        var temperatures = data.map(function(d) { return d.temperature; }).filter(function(t) { return t !== null; });
        var heatIndexes = data.map(function(d) { return d.heat_index; }).filter(function(h) { return h !== null; });
        var humidities = data.map(function(d) { return d.humidity; }).filter(function(h) { return h !== null; });

        var tempMin = Math.min.apply(Math, temperatures.concat(heatIndexes));
        var tempMax = Math.max.apply(Math, temperatures.concat(heatIndexes));
        var tempRange = tempMax - tempMin;

        var humidityMin = Math.min.apply(Math, humidities);
        var humidityMax = Math.max.apply(Math, humidities);

        humidityMin = Math.min(humidityMin, 40);
        humidityMax = Math.max(humidityMax, 60);

        tempMin = tempMin - tempRange * 0.1;
        tempMax = tempMax + tempRange * 0.1;
        humidityMin = Math.max(0, humidityMin - 5);
        humidityMax = Math.min(100, humidityMax + 5);

        function getX(index) {
            return margin.left + ((index + 0.5) / data.length) * chartWidth;
        }

        function getTempY(temp) {
            return margin.top + chartHeight - ((temp - tempMin) / (tempMax - tempMin)) * chartHeight;
        }

        function getHumidityY(humidity) {
            return margin.top + chartHeight - ((humidity - humidityMin) / (humidityMax - humidityMin)) * chartHeight;
        }

        ctx.strokeStyle = '#f0f0f0';
        ctx.lineWidth = 1;
        for (var temp = Math.ceil(tempMin); temp <= Math.floor(tempMax); temp += 1) {
            var y = getTempY(temp);
            ctx.beginPath();
            ctx.moveTo(margin.left, y);
            ctx.lineTo(width - margin.right, y);
            ctx.stroke();
        }

        ctx.strokeStyle = '#555';
        ctx.lineWidth = 2;

        ctx.beginPath();
        ctx.moveTo(margin.left, margin.top);
        ctx.lineTo(margin.left, height - margin.bottom);
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(width - margin.right, margin.top);
        ctx.lineTo(width - margin.right, height - margin.bottom);
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(margin.left, height - margin.bottom);
        ctx.lineTo(width - margin.right, height - margin.bottom);
        ctx.stroke();

        ctx.strokeStyle = '#27ae60';
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 5]);

        var y40 = getHumidityY(40);
        ctx.beginPath();
        ctx.moveTo(margin.left, y40);
        ctx.lineTo(width - margin.right, y40);
        ctx.stroke();

        var y60 = getHumidityY(60);
        ctx.beginPath();
        ctx.moveTo(margin.left, y60);
        ctx.lineTo(width - margin.right, y60);
        ctx.stroke();

        ctx.setLineDash([]);

        ctx.strokeStyle = '#FF6B6B';
        ctx.lineWidth = 3;
        ctx.beginPath();
        var firstPoint = true;
        for (var i = 0; i < data.length; i++) {
            if (data[i].temperature !== null) {
                var x = getX(i);
                var y = getTempY(data[i].temperature);
                if (firstPoint) {
                    ctx.moveTo(x, y);
                    firstPoint = false;
                } else {
                    ctx.lineTo(x, y);
                }
            }
        }
        ctx.stroke();

        ctx.strokeStyle = '#FF8E53';
        ctx.lineWidth = 2;
        ctx.beginPath();
        firstPoint = true;
        for (var i = 0; i < data.length; i++) {
            if (data[i].heat_index !== null) {
                var x = getX(i);
                var y = getTempY(data[i].heat_index);
                if (firstPoint) {
                    ctx.moveTo(x, y);
                    firstPoint = false;
                } else {
                    ctx.lineTo(x, y);
                }
            }
        }
        ctx.stroke();

        for (var i = 0; i < data.length; i++) {
            var x = getX(i);

            if (data[i].temperature !== null) {
                ctx.fillStyle = '#FF6B6B';
                ctx.beginPath();
                ctx.arc(x, getTempY(data[i].temperature), 3, 0, 2 * Math.PI);
                ctx.fill();
            }

            if (data[i].heat_index !== null) {
                ctx.fillStyle = '#FF8E53';
                ctx.beginPath();
                ctx.arc(x, getTempY(data[i].heat_index), 2, 0, 2 * Math.PI);
                ctx.fill();
            }
        }

        ctx.fillStyle = '#4ECDC4';
        ctx.globalAlpha = 0.15;
        for (var i = 0; i < data.length; i++) {
            if (data[i].humidity !== null) {
                var x = getX(i);
                var barWidth = chartWidth / data.length * 0.6;
                var barHeight = chartHeight - (getHumidityY(data[i].humidity) - margin.top);
                ctx.fillRect(x - barWidth / 2, getHumidityY(data[i].humidity), barWidth, barHeight);
            }
        }
        ctx.globalAlpha = 1.0;

        ctx.fillStyle = '#666';
        ctx.font = '12px -apple-system, BlinkMacSystemFont, sans-serif';
        ctx.textAlign = 'right';
        for (var temp = Math.ceil(tempMin); temp <= Math.floor(tempMax); temp += 2) {
            var y = getTempY(temp);
            ctx.fillText(temp + '°C', margin.left - 10, y + 4);
        }

        ctx.textAlign = 'left';
        ctx.fillStyle = '#4ECDC4';

        var startHumidity = Math.floor(humidityMin / 10) * 10;
        var endHumidity = Math.ceil(humidityMax / 10) * 10;

        for (var humidity = startHumidity; humidity <= endHumidity; humidity += 10) {
            if (humidity >= humidityMin && humidity <= humidityMax) {
                var y = getHumidityY(humidity);
                ctx.fillText(humidity + '%', width - margin.right + 10, y + 4);
            }
        }

        ctx.fillStyle = '#666';
        ctx.textAlign = 'center';
        if (days === 1) {

            for (var i = 0; i < data.length; i++) {
                var x = getX(i);
                var hour = data[i].hour.split(' ')[1].substring(0, 2);
                ctx.fillText(hour, x, height - margin.bottom + 15);
            }
        } else {

            var stepSize = Math.ceil(data.length / 6);
            for (var i = 0; i < data.length; i += stepSize) {
                if (i < data.length) {
                    var x = getX(i);
                    var dateParts = data[i].hour.split(' ');
                    var date = dateParts[0];
                    var time = dateParts[1].substring(0, 2);
                    var day = date.split('-')[2];

                    ctx.fillText(time, x, height - margin.bottom + 10);

                    ctx.fillText(day, x, height - margin.bottom + 25);
                }
            }
        }
    }

    function createTemperatureStatistics(data) {
        if (!data || data.length === 0) {
            return '<p class="text-gray-light text-center">Données insuffisantes pour calculer les statistiques</p>';
        }

        var temperatures = data.map(function(d) { return d.temperature; }).filter(function(t) { return t !== null; });
        var heatIndexes = data.map(function(d) { return d.heat_index; }).filter(function(h) { return h !== null; });
        var humidities = data.map(function(d) { return d.humidity; }).filter(function(h) { return h !== null; });

        if (temperatures.length === 0 || heatIndexes.length === 0 || humidities.length === 0) {
            return '<p class="text-gray-light text-center">Données insuffisantes pour calculer les statistiques</p>';
        }

        var tempMin = Math.min.apply(Math, temperatures);
        var tempMax = Math.max.apply(Math, temperatures);
        var tempAvg = temperatures.reduce(function(a, b) { return a + b; }, 0) / temperatures.length;

        var heatMin = Math.min.apply(Math, heatIndexes);
        var heatMax = Math.max.apply(Math, heatIndexes);
        var heatAvg = heatIndexes.reduce(function(a, b) { return a + b; }, 0) / heatIndexes.length;

        var humidityMin = Math.min.apply(Math, humidities);
        var humidityMax = Math.max.apply(Math, humidities);
        var humidityAvg = humidities.reduce(function(a, b) { return a + b; }, 0) / humidities.length;

        return `
            <div class="row">
                <!-- Panel Température -->
                <div class="col-md-6 col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="background-color: #FF6B6B; color: white;">
                            <h3 class="panel-title">🌡️ Température</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-4 text-center"><small>Min</small><br><strong>${tempMin.toFixed(1)}°C</strong></div>
                                <div class="col-xs-4 text-center"><small>Moy</small><br><strong>${tempAvg.toFixed(1)}°C</strong></div>
                                <div class="col-xs-4 text-center"><small>Max</small><br><strong>${tempMax.toFixed(1)}°C</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Humidité -->
                <div class="col-md-6 col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="background-color: #4ECDC4; color: white;">
                            <h3 class="panel-title">💧 Humidité</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-4 text-center"><small>Min</small><br><strong>${humidityMin.toFixed(0)}%</strong></div>
                                <div class="col-xs-4 text-center"><small>Moy</small><br><strong>${humidityAvg.toFixed(0)}%</strong></div>
                                <div class="col-xs-4 text-center"><small>Max</small><br><strong>${humidityMax.toFixed(0)}%</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Panel Température ressentie (centré) -->
                <div class="col-md-6 col-md-offset-3 col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="background-color: #FF8E53; color: white;">
                            <h3 class="panel-title">🔥 Température ressentie</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-4 text-center"><small>Min</small><br><strong>${heatMin.toFixed(1)}°C</strong></div>
                                <div class="col-xs-4 text-center"><small>Moy</small><br><strong>${heatAvg.toFixed(1)}°C</strong></div>
                                <div class="col-xs-4 text-center"><small>Max</small><br><strong>${heatMax.toFixed(1)}°C</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    window.DashboardThermometers.drawTemperatureChart = drawTemperatureChart;
    window.DashboardThermometers.createTemperatureStatistics = createTemperatureStatistics;

})();
