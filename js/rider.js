var weeks=[];
var dataset=[];
var inverted=[];

// build weeks //
for (var i in wpOptions.weekly_ranks) {
	weeks.push('Wk. '+wpOptions.weekly_ranks[i].week);
}

// build data //
for (var i in wpOptions.weekly_ranks) {
	dataset.push(parseInt(wpOptions.weekly_ranks[i].rank));
}

var max=Math.max.apply(Math,dataset);

// in cases where a rider went from 0 to X and X remained the same, it can skew the graph
// we check for the unique values and if there's 2 or less, we can assume that their is either 1 or 2 changes
// if 1 change, do nothing for now NOT SETUP
// if 2 changes and one is Zero (0), then we create some dummy numbers
if (arrayUnique(dataset).length==2) {
	for (var i in dataset) {
		if (dataset[i]==0) {
			dataset[i]=max+(max+1);
		}
	}
	max=Math.max.apply(Math,dataset); // redo max
}

console.log(dataset);

// make inverted //
for (var i in dataset) {
	var newValue=max-dataset[i];

	if (dataset[i]==0) {
		newValue=0;
	}

	inverted[i]=newValue;
}

var lineChartData = {
	labels: weeks,
	datasets: [
		{
			label: "Ranking I",
			fillColor: "rgba(151,187,205,0.2)",
			strokeColor: "rgba(151,187,205,1)",
			pointColor: "rgba(151,187,205,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(151,187,205,1)",
			data: inverted //[5, 5, 3, 3, 3, 3, 3, 3, 3, 3]
		}
	]
};


console.log(inverted);

var steps=5;
var scaleWidth=Math.ceil(max/steps);
//var scaleWidth=Math.floor(max/steps);
var scaleStart=0;

console.log('m: '+max);
console.log('s: '+steps);
console.log('w: '+scaleWidth);

window.onload=function() {
	var ctx = document.getElementById("weekly-rankings").getContext("2d");

	var chartInverted=new Chart(ctx).Line(lineChartData, {
		customTooltips: function(tooltip) {
			var tooltipEl = jQuery('#chartjs-tooltip');

			if (!tooltip) {
			    tooltipEl.css({
			        opacity: 0
			    });
			    return;
			}

			tooltipEl.removeClass('above below');
			tooltipEl.addClass(tooltip.yAlign);

			// split out the label and value and make your own tooltip here
			var parts = tooltip.text.split(":");

			// we get the original value via data set where the parts[0] is the week //
			var weekNum=parts[0].trim().replace( /^\D+/g, '');
			var weekNumKey=weekNum-1;
			var newYvalue=dataset[weekNumKey];

			if (newYvalue==0) {
				newYvalue='n/a';
			}

			var innerHtml = '<span>' + parts[0].trim() + '</span> : <span><b>' + newYvalue + '</b></span>';
			tooltipEl.html(innerHtml);

			tooltipEl.css({
				opacity: 1,
				left: tooltip.chart.canvas.offsetLeft + tooltip.x + 'px',
				top: tooltip.chart.canvas.offsetTop + tooltip.y + 'px',
				fontFamily: tooltip.fontFamily,
				fontSize: tooltip.fontSize,
				fontStyle: tooltip.fontStyle,
			});
		},
		scaleOverride: true,
		scaleStartValue: scaleStart,
		scaleStepWidth: scaleWidth,
		scaleSteps: steps,
		responsive:true,
		scaleLabel: function(object) {
			var value=max-object.value;

			if (value<0) {
				value=0;
			}

			return value;
		}
	});

}


function arrayUnique(a) {
	return a.reduce(function(p, c) {
	  if (p.indexOf(c) < 0) p.push(c);
	  return p;
	}, []);
};