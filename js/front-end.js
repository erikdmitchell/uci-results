jQuery(document).ready(function($) {
	
	$('#uci-rankings-discipline').on('change', function(e) {
		e.preventDefault();
		
		var data={
			'action' : 'uci_rankings_discipline_dd',
			'discipline' : $(this).val()
		};
		
		$.post(UCIResultsFrontEnd.ajax_url, data, function(options) {
			var select=$('#uci-rankings-date');
			
			select.html('');
			
			options=$.parseJSON(options);

			$.each(options, function() {
				console.log(this);
			    select.append($("<option />").val(this.value).text(this.name));
			});
		});
	});
	
});