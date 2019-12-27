<?php require_once("../constants.php"); ?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	
	google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				var data = JSON.parse(this.responseText);

				var tl = [];
				for(var i = 0; i<7; i++)
					tl.unshift([data["date" + i + "d"], data["new" + i + "d"], data["open" + i + "d"], data["wip" + i + "d"]]);
				tl.unshift(['Tag', 'Neu', 'Offen', 'In Bearbeitung']);
				
				var data_line = google.visualization.arrayToDataTable(tl);

				var options = {
					title: 'Tickets der letzten 7 Tage',
					legend: { position: 'bottom' },
					colors: ["#ff4c4c", "#ffbe4c", "#3dbb3d"]
				};

				new google.visualization.LineChart(document.getElementById('curve_chart')).draw(data_line, options);
			}
	  	};
	  	xhttp.open("GET", "/wp-json/<?php echo(ITCROWD_URL_API . ITCROWD_URL_API_STATS); ?>", true);
	 	xhttp.send();
    }
	
</script>
<div id="curve_chart" style="width: 450px; height: 250px"></div>