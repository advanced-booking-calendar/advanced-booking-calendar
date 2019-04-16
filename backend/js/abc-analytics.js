jQuery(document).ready(function () {
	var requestsData = {
		labels: JSON.parse(abc_graphData.dateGraphTicks),
		series: JSON.parse(abc_graphData.dateGraphData)
	};
	var requestsOptions = {
		height: 300,
		showPoint: false,
		axisY: {
			onlyInteger: true
		}	
	};	
	var requestsResponsiveOptions = [
		['screen and (max-width: 640px)', {
			axisX: {
				labelInterpolationFnc: function (value) {
					return value.substring(0, 6);
				}
			}
		}]
	];
new Chartist.Line('.abc-requests', requestsData, requestsOptions, requestsResponsiveOptions);	

	var totalAvailData = {
		labels: JSON.parse(abc_graphData.availabilityLabels),
		series: JSON.parse(abc_graphData.totalAvailData)
	};
	var totalAvailOptions = {
		height: 300,
		showPoint: false,
		showArea: true,
		axisY: {
			onlyInteger: true,
			high: 100,
			low: 0,
			labelInterpolationFnc: function(value) {
				return value + '%';
			}	
		}	
	};	
	var totalAvailResponsiveOptions = [
		['screen and (max-width: 640px)', {
			axisX: {
				labelInterpolationFnc: function (value) {
					return value.substring(0, 6);
				}
			}
		}]
	];
new Chartist.Line('.abc-overallAvailability', totalAvailData, totalAvailOptions, totalAvailResponsiveOptions);	

var availabilityData = {
		labels: JSON.parse(abc_graphData.availabilityLabels),
		series: JSON.parse(abc_graphData.availabilityData)
	};
	var availabilityOptions = {
		high: 100,
		low: 0,
		height: 300,
		lineSmooth: false,
		showPoint: false,
		axisY: {
			onlyInteger: true,
			labelInterpolationFnc: function(value) {
				return value + '%';
    		}
		}	
	};	
	var availabilityResponsiveOptions = [
		['screen and (max-width: 640px)', {
			axisX: {
				labelInterpolationFnc: function (value) {
					return value.substring(0, 6);
				}
			}
		}]
	];
new Chartist.Line('.abc-availability', availabilityData, availabilityOptions, availabilityResponsiveOptions);

	var personPieData = {
		labels: JSON.parse(abc_graphData.personPieLabels),
		series: JSON.parse(abc_graphData.personPieData)
	}
	var personPieOptions = {
		height: 300,
		labelInterpolationFnc: function(value) {
			return value[0];
		}
	};
	var sum = function(a, b) { return a + b };
	var personPieResponsiveOptions = [
	['screen and (min-width: 640px)', {
		chartPadding: 30,
		labelOffset: 100,
		labelDirection: 'explode',
		labelInterpolationFnc: function(value) {
			return value;
		}
	  }],
	  ['screen and (min-width: 1024px)', {
		labelOffset: 80,
		chartPadding: 20
	  }]
	];
new Chartist.Pie('.abc-personpie', personPieData, personPieOptions, personPieResponsiveOptions);
	
	var revenueData = {
		labels: JSON.parse(abc_graphData.revenueLabels),
		series: JSON.parse(abc_graphData.revenueData)
	};
	
	var revenueOptions = {
		height: 300,
	  	seriesBarDistance: 10,
		axisY: {
			labelInterpolationFnc: function(value) {
				return value + '€';
   			}
   		}	
	};

new Chartist.Bar('.abc-revenueBar', revenueData, revenueOptions);
});