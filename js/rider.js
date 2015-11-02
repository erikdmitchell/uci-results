var weeks=[];
var dataset=[];

console.log(wpOptions);
// build weeks //
for (var i in wpOptions.weekly_ranks) {
	weeks.push(wpOptions.weekly_ranks[i].week);
}

// build data //
for (var i in wpOptions.weekly_ranks) {
	dataset.push(wpOptions.weekly_ranks[i].rank);
}

console.log(dataset);

var lineChartData = {
	labels: weeks,
	datasets: [
		{
			label: "Ranking",
			fillColor: "rgba(151,187,205,0.2)",
			strokeColor: "rgba(151,187,205,1)",
			pointColor: "rgba(151,187,205,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(151,187,205,1)",
			data: dataset
		}
	]
};

console.log(lineChartData.datasets);

window.onload=function() {
	var ctx = document.getElementById("weekly-rankings").getContext("2d");
	window.myLine = new Chart(ctx).Line(lineChartData, {
		responsive: true,
		scaleOverride: true,
		scaleSteps: 5,
		scaleStepWidth: 10,
		scaleStartValue: 0
	});
}