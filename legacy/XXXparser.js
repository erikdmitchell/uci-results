var curlObj=jQuery.parseJSON(data.curl);
var finderURL=data.finderURL;

jQuery(document).ready(function($) {
	runCURL('.results');

	$('.result-curl').click(function(e) {
		e.preventDefault();
		$.ajax({
		  type: 'POST',
		  url: data.pluginURL+'curl-result-page.php',
		  data: { 
		  	url: $(this).data('url')
		  }
		}).done(function(_return) {
console.log(_return);
/*		
		  parseResultsTable(_return,function() {
				filterOutResults(function(arr) {
					sendResultstoDB(arr);
				});
		  });
*/
		});
	});
	
	
	$('#runAll').click(function(e) {
		e.preventDefault();
/*
pass an array of urls to the result-curl click data value -- IGNORE FOR NOW
turn our click function into a foreach function where the url that gets passed is all urls on page
- 2 - would be to auto establish and cycle through links
*/

		$('.results a').each(function() {
			var elem_url=false;
			if (typeof $(this).data('url')!=='undefined') {
				elem_url=$(this).data('url');
				if (elem_url) {
//console.log(elem_url);				
					$.ajax({
					  type: 'POST',
					  url: data.pluginURL+'curl-result-page.php',
					  data: { 
					  	url: elem_url
					  }
					}).done(function(_return) {
console.log(_return);
/*					
					  parseResultsTable(_return,function() {
							filterOutResults(function(arr) {
								sendResultstoDB(arr);
							});
					  });
*/
					});
				}
			}
		}); // results a each //
	});
	
});

$=jQuery.noConflict();

function parseResultsTable(page,callback) {
// STEP 1
	var $div=$('.race-results');
	
	$div.html(page); // creates an html object we can use jquery to go through and chop
	
	callback();
}

function filterOutResults(callback) {
	var final_arr={}; // new object
	var results_arr={}; // new object
	var startPos=null;
	var endPos=null;
	var data=null;
	var	$div=$('.race-results');
	//var all_tables=$div.find('table');
	var race_details=$div.find('.crumblepad').html();
	//var $race_results=$div.find('.datatable').html();	
	
	race_details=race_details.replace(/[\n\r]/g, '<br>'); // turns carriage returns into <br>
	var race_details_arr=race_details.split('<br>'); // splits our html into array -- [9] - race name, [10] - country/rank, [14] - date, [15] - location //
	var date=race_details_arr[14].trim().replace(':','');
	var countryRank=race_details_arr[10].trim().replace('(','');
	countryRank=countryRank.replace(')','');
	countryRank=countryRank.split('/');

	// build our array with race data and results //		
	final_arr['name']=race_details_arr[9].trim(); // remove whitespace
	final_arr['country']=countryRank[0]; // remove whitespace
	final_arr['rank']=countryRank[1]; // remove whitespace
	final_arr['date']=date; // remove whitespace
	final_arr['location']=race_details_arr[15].trim(); // remove whitespace
	
	// filter through results table and build results array //
	$('.race-results .datatable > tbody > tr').each(function(x) {
console.log(x);
	});
	
	$('.race-results .datatable > tbody > tr').each(function(x) {
		if (x!=0) { // ignore first row (key)
			results_arr[x]={}; // new object
			$(this).find('td').each(function(i) {
//console.log($(this));
		
				// rank, name, nat, age, result, par, pcr
				if (i==0) {
					results_arr[x]['place']=$(this).html().trim();
				} else if (i==1) {
					results_arr[x]['name']=$(this).html();
				} else if (i==2) {
					results_arr[x]['nat']=$(this).html();
				} else if (i==3) {
					results_arr[x]['age']=$(this).html();
				} else if (i==4) {
					results_arr[x]['time']=$(this).html();
				} else if (i==5) {
					startPos=$(this).html().indexOf('">')+2;
					endPos=$(this).html().indexOf('</',startPos);
					data=$(this).html().substring(startPos,endPos);
				
					results_arr[x]['par']=data;
				}	else if (i==6) {
					startPos=$(this).html().indexOf('">')+2;
					endPos=$(this).html().indexOf('</',startPos);
					data=$(this).html().substring(startPos,endPos);
					
					results_arr[x]['pcr']=data;
				}											
			}); 
		}
	});
//console.log(results_arr); // STEP 2a	
	final_arr['results']=results_arr;
//console.log(final_arr); // STEP 2b	
	callback(final_arr);
}

function sendResultstoDB(arr) {
//console.log(arr); // STPE 3
	div='.race-db-result';
	// we now must pass this via ajax to php to put into db //
	$.ajax({
	  type: 'POST',
	  url: data.pluginURL+'ajax-add-results.php',
	  data: { 
	  	info: JSON.stringify(arr)
	  }
	}).done(function(_return) {
		var obj=$.parseJSON(_return);
		var msg='<div class="'+obj.type+'">'+obj.value+'</div>';

		$(div).append(msg);
	});	
}

function runCURL(div) {
	var startPos=null;
	var endPos=null;
	var objURL=null;
	var linkName=null;
	var objString=null;
			
	for (i in curlObj) {
		if (typeof curlObj[i]==='string' && curlObj[i].indexOf(finderURL)!=-1) {
  		// strips between 'Ranking=0">' and '</a>' to get our name
			startPos=curlObj[i].indexOf('Ranking=0">')+11; // the +11 accounts for everything after the R in Ranking
			endPos=curlObj[i].indexOf('</a>',startPos);
			linkName=curlObj[i].substring(startPos,endPos);			  		
  		
  		// strips out everything between " ", which is our link //
			startPos=curlObj[i].indexOf('"')+1;
			endPos=curlObj[i].indexOf('"',startPos);
			objURL=curlObj[i].substring(startPos,endPos);
			
			// we need to add 'PageNr0=-1' to our link to make it default to view all //
			//objURL=objURL+'&PageNr0=-1';

			// get just the query string //
			startPos=curlObj[i].indexOf('?')+1;
			endPos=curlObj[i].indexOf('',startPos);
			objString=curlObj[i].substring(startPos);

			// we need to rebuild our url since this url is a fancy redirect //
			var queryObj=parseQueryString(objString);
			var finalURL='http://www.uci.infostradasports.com/asp/lib/TheASP.asp?';
			
			finalURL+='PageID=19006';
			finalURL+='&SportID='+queryObj.SportID;
			finalURL+='&CompetitionID='+queryObj.CompetitionID;
			finalURL+='&EditionID='+queryObj.EditionID;
			finalURL+='&SeasonID='+queryObj.SeasonID;
			finalURL+='&ClassID='+queryObj.ClassID;
			finalURL+='&GenderID='+queryObj.GenderID;
			finalURL+='&EventID='+queryObj.EventID;
			finalURL+='&EventPhaseID='+queryObj.EventPhaseID;
			finalURL+='&Phase1ID='+queryObj.Phase1ID;
			finalURL+='&Phase2ID=0';
			finalURL+='&Phase3ID=0';
			finalURL+='&PhaseClassificationID=-1';
			finalURL+='&Detail='+queryObj.Detail;
			//finalURL+='&Ranking='+queryObj.Ranking; -- causes and error
			finalURL+='&All=0';
			finalURL+='&TaalCode=2';
			finalURL+='&StyleID=0';
			finalURL+='&Cache=8';
			finalURL+='&PageNr0=-1';
			
			var link='<a href="'+finalURL+'" target="_blank">'+linkName+'</a>&nbsp;&nbsp;&nbsp;[<a href="#" data-url="'+finalURL+'" class="result-curl" >Result cURL</a>]<br/>'; 		
  		
  		$(div).append(link);
		} // end if
	} // end for
}

function parseQueryString(queryString) {
	var params = {}, queries, temp, i, l;
 
  // Split into key/value pairs
  queries = queryString.split("&");
 
  // Convert the array of strings into an object
  for ( i = 0, l = queries.length; i < l; i++ ) {
  	temp = queries[i].split('=');
    params[temp[0]] = temp[1];
  }
 
  return params;
};