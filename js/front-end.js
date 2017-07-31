jQuery(document).ready(function($) {
	
	$('#uci-rankings-discipline').on('change', function(e) {
		e.preventDefault();
		
		var data={
			'action' : 'uci_rankings_discipline_dd',
			'discipline' : $(this).val()
		};
		
		$.post(UCIResultsFrontEnd.ajax_url, data, function(data) {
			data=$.parseJSON(data);

			// setup dates dropdown //
			var $select=$('#uci-rankings-date');
			
			$select.html('');
			
			$.each(data.date_options, function() {
			    $select.append($("<option />").val(this.value).text(this.name));
			});
			
			$select.val(data.selected_date); // set value
			
			// setup date //
			var $div=$('.uci-rankings');
			
			$div.find('.riders-list-wrap').html('');

			$.each(data.ranks, function() {
			    $div.find('.riders-list-wrap').append(this);
			});			
		});
	});
	
});