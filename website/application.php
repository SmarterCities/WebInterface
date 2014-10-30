<div id = "application">

    <h1> 1. Choose a model: </h1>
    <div id = 'buttons'>
		<button onclick="input('SmarterHousing')" type="button">SmarterHousing<img src="images/home-icon.png"></button>
		<button onclick="input('ExampleModel')" type="button">ExampleModel<img src="images/bar-chart-icon.png"></button>
    <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>

    <script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
    <script src="d3.slider.js"></script>
    <script type="text/javascript" src="http://cdn.amcharts.com/lib/3/amcharts.js"></script>
	<script type="text/javascript" src="http://cdn.amcharts.com/lib/3/serial.js"></script>
    <link rel="stylesheet" href="d3.slider.css"/>
	<script>
	var current_model;
	function input(model) {
		current_model = model;
        $.get("http://smartercities-api.mybluemix.net/input/"+model,{}).then(function(data){
			//clear previous sliders and save for future use
			d3.select('#inputdiv').text("");

			//for each slider object in data JSON make a slider
			(data.sliders).forEach(function(slider){
				slider.id = slider.name.replace(/\s+/g, ''); //remove spaces
				slider.id = slider.id.replace('[', ''); //remove [
				slider.id = slider.id.replace(']', ''); //remove ]

				//add the title
				d3.select("div#inputdiv")
					.append("p")
					.html(slider.name+ " : " + slider.value)
					.attr("id",slider.id)
					.attr("name",slider.name)
					.attr("value",slider.value)
					.attr("class","slider");

				//add the slider
				d3.select("div#inputdiv")
					.append("div")
		    		.attr("height", "20px")
					//.attr("class", "sliders")
					.attr("z-index", "99999")
					.call(d3.slider()
						.on("slide", function(evt, value) {
							d3.select("#"+slider.id)
							.text(slider.name+" : "+value)
							.attr("value",value);
						})
						.value(slider.value)
						.axis(true)
						.min(slider.min)
						.max(slider.max)
						);

			});

		});
    };


	function output(){
		d3.select('#outputdiv').text("");
		var sliders = d3.selectAll(".slider")[0]
		var variable_values = "?"
		sliders.forEach(function(slider){
			variable_values=variable_values+slider.getAttribute("name")+"="+slider.getAttribute("value")+"&";
		})
		//console.log(variable_values)
		console.log("call:"+"http://smartercities-api.mybluemix.net/output/"+current_model+variable_values)

		$.get("http://smartercities-api.mybluemix.net/output/"+current_model+variable_values,{}).then(function(data){
			//expand output div
			//d3.select("div#outputdiv").style("height","400px");
			console.log("charts:");
			(data.charts).forEach(function(chart){
				console.log(chart.name);
				console.log(chart);
				d3.select("#outputdiv")
					.append("div")
					.attr("id",chart.name)
					.style("width","100%")
					.style("height","400px")
					.style("background-color", "lightgray");

				AmCharts.makeChart(chart.name,chart);
				//AmCharts.makeChart(d3.select("#"+chart.name), )

			});
		});
	}

	</script>
	
	<h1> 2. Set the parameters you want to use: </h1>
	<div id = 'inputdiv'>
	</div>

	<div id="inputvalue">
	</div>

	<div>
     <h1> 3. Run the model: </h1>
	<button onclick="output()" type="button">Run</button>

</div>
<div>
     <h1> 4. Review the output: </h1>
	<div id = 'outputdiv' style="width: 100%; background-color: lightgray;">
	</div>
</div>

</div>