window.DashboardLayout = (function() {
    'use strict';

    function applyWidgetOrder(widgetSettings) {
        if (!widgetSettings || Object.keys(widgetSettings).length === 0) {
            return;
        }

        var leftColumn = document.getElementById('column-left');
        var rightColumn = document.getElementById('column-right');

        if (!leftColumn || !rightColumn) return;

        var allWidgets = [];
        var widgets = document.querySelectorAll('.widget-item');

        for (var i = 0; i < widgets.length; i++) {
            var widget = widgets[i];
            var widgetId = widget.getAttribute('id');
            var widgetSetting = widgetSettings[widgetId];

            var isEnabled = !widgetSetting || widgetSetting.enabled !== false;

            widget.style.display = isEnabled ? 'block' : 'none';

            allWidgets.push(widget);
        }

        allWidgets.sort(function(a, b) {
            var widgetIdA = a.getAttribute('id');
            var widgetIdB = b.getAttribute('id');

            var weightA = (widgetSettings[widgetIdA] && widgetSettings[widgetIdA].position) || 999;
            var weightB = (widgetSettings[widgetIdB] && widgetSettings[widgetIdB].position) || 999;

            return weightA - weightB;
        });

        leftColumn.innerHTML = '';
        rightColumn.innerHTML = '';

        var windowWidth = window.innerWidth || document.documentElement.clientWidth;
        var isMobile = windowWidth < 991;

        for (var j = 0; j < allWidgets.length; j++) {
            var widget = allWidgets[j];

            if (isMobile) {

                leftColumn.appendChild(widget);
            } else {

                if (j === 0) {

                    leftColumn.appendChild(widget);
                } else if (j === 1) {

                    rightColumn.appendChild(widget);
                } else {

                    var leftHeight = getColumnHeight(leftColumn);
                    var rightHeight = getColumnHeight(rightColumn);

                    if (leftHeight <= rightHeight) {
                        leftColumn.appendChild(widget);
                    } else {
                        rightColumn.appendChild(widget);
                    }
                }
            }

            widget.offsetHeight;
        }
    }

    function getColumnHeight(column) {
        if (!column) return 0;

        var children = column.children;
        var totalHeight = 0;

        for (var i = 0; i < children.length; i++) {
            var child = children[i];
            var childHeight = child.offsetHeight;

            var marginTop = 0;
            var marginBottom = 0;

            if (window.getComputedStyle) {
                var style = window.getComputedStyle(child);
                marginTop = parseInt(style.marginTop, 10) || 0;
                marginBottom = parseInt(style.marginBottom, 10) || 0;
            } else if (child.currentStyle) {

                marginTop = parseInt(child.currentStyle.marginTop, 10) || 0;
                marginBottom = parseInt(child.currentStyle.marginBottom, 10) || 0;
            }

            totalHeight += childHeight + marginTop + marginBottom;
        }

        return totalHeight;
    }

    function getWidgetSettings() {
        try {
            var settings = localStorage.getItem('widget-settings');
            return settings ? JSON.parse(settings) : {};
        } catch (e) {
            return {};
        }
    }

    function applyWidgetOrderOnLoad() {

        setTimeout(function() {
            var settings = getWidgetSettings();
            if (settings && Object.keys(settings).length > 0) {
                applyWidgetOrder(settings);
            } else {
                applyDefaultDistribution();
            }
        }, 100);
    }

    function applyDefaultDistribution() {
        var leftColumn = document.getElementById('column-left');
        var rightColumn = document.getElementById('column-right');

        if (!leftColumn || !rightColumn) {

            return;
        }

        var widgets = document.querySelectorAll('.widget-item');

        leftColumn.innerHTML = '';
        rightColumn.innerHTML = '';

        var windowWidth = window.innerWidth || document.documentElement.clientWidth;
        var isMobile = windowWidth < 991;

        for (var i = 0; i < widgets.length; i++) {
            var widget = widgets[i];

            widget.style.display = 'block';

            if (isMobile) {

                leftColumn.appendChild(widget);
            } else {

                var leftHeight = getColumnHeight(leftColumn);
                var rightHeight = getColumnHeight(rightColumn);

                if (leftHeight <= rightHeight) {
                    leftColumn.appendChild(widget);
                } else {
                    rightColumn.appendChild(widget);
                }
            }

            widget.offsetHeight;
        }
    }

    function handleResize() {
        var resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                var settings = getWidgetSettings();
                if (settings && Object.keys(settings).length > 0) {
                    applyWidgetOrder(settings);
                } else {
                    applyDefaultDistribution();
                }
            }, 250);
        });
    }

    function init() {
        applyWidgetOrderOnLoad();
        handleResize();
    }

    return {
        init: init,
        applyWidgetOrder: applyWidgetOrder,
        applyDefaultDistribution: applyDefaultDistribution,
        getWidgetSettings: getWidgetSettings
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    if (window.DashboardLayout) {
        window.DashboardLayout.init();
    }
});

if (document.readyState !== 'loading') {
    if (window.DashboardLayout) {
        window.DashboardLayout.init();
    }
}
