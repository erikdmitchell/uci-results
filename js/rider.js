var weeks=[];
var dataset=[];

console.log(wpOptions);
// build weeks //
for (var i in wpOptions.weekly_ranks) {
	weeks.push('Wk. '+wpOptions.weekly_ranks[i].week);
}

// build data //
for (var i in wpOptions.weekly_ranks) {
	dataset.push(wpOptions.weekly_ranks[i].rank);
}

var datasetMax=Math.max.apply(Math,dataset);
var datasetMin=Math.min.apply(Math,dataset);

// make dataset negative //
for (var i in dataset) {
	//dataset[i]=-Math.abs(dataset[i]);
}

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

window.onload=function() {
	var ctx = document.getElementById("weekly-rankings").getContext("2d");

	window.myLine = new Chart(ctx).Line(lineChartData, {
		responsive: true,
		maintainAspectRatio: false
		//scaleOverride: false,
	});
}