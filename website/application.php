<div id = "application">

    <h1> 1. Choose a model: </h1>
    <div id = 'buttons'>
		<button onclick="input('ExampleModel')" type="button">ExampleModel<img src="images/bar-chart-icon.png"></button>
		<button onclick="input('SmarterHousing')" type="button">SmarterHousing<img src="images/home-icon.png"></button>
		<button onclick="input('311Messages')" type="button">311 Service Requests<img src="images/coin-icon.png"></button>
		<button onclick="input('CityPulse')" type="button">CityPulse<img src="images/heart-pulse-icon.png"</button>
	</div>
   
	
	<h1> 2. Set the parameters you want to use: </h1>
	<div id = 'inputdiv'>
	</div>
	
    <h1> 3. Run the model: </h1>
    <div>
		<button onclick="output()" type="button">Run</button>
	<div>

    <h1> 4. Review the output: </h1>
	<div id = 'outputdiv' style="width: 100%; background-color: lightgray;">
	</div>
</div>

<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
<script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
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
    	console.log(data)
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
		}); //sliders
			
		//for each entry object in data JSON make an entry
		(data.entries).forEach(function(entry){
			//add the title
			d3.select("div#inputdiv")
			 	.append("p")
			 	.attr("id", entry.name)
			 	.attr("name", entry.name)
			 	.attr("value", 0)
			 	.attr("class","entry")
			 	.html(entry.name + " : ");

			//add the entry
			d3.select("div#inputdiv")
			 	.append("input")
			 	.attr("type","number")
			 	.attr("name",entry.name)
			 	.attr("value",0)
			 	.on("input", function(){
			 		d3.select("#"+entry.name)
			 		.attr("value", this.value)
			 	});
		}); //entries

		//for each dropdown menu in data JSON make a dropdown menu
		(data.dropdowns).forEach(function(dropdown){
			//add the title
			d3.select("div#inputdiv")
			 	.append("p")
			 	.html(dropdown.name + " : ");
			//add the dropdown
			d3.select("div#inputdiv")
			 	.append("select")
			 	.attr("class", "dropdown")
			 	.attr("name", dropdown.name)
			 	//.attr("value", dropdown.values[0])
			 	.attr("id", dropdown.name);
			//add each value of the dropdown
			(dropdown.values).forEach(function(value){
				d3.select("select#"+dropdown.name)
					.append("option")
					.attr("value", value)
					.html(value)
					.on("select", function(v){
						d3.select("#"+dropdown.name)
						.html(v)
						.attr("value", this.value);
					});
			})
		});


	}); //api call
}; //input function


function output(){
	//clear previous output
	d3.select('#outputdiv').text("");

	//Get parameter values:
	var variable_values = "?"
	//sliders
	var sliders = d3.selectAll(".slider")[0]
	sliders.forEach(function(slider){
		variable_values=variable_values+slider.getAttribute("name")+"="+slider.getAttribute("value")+"&";
	});
	//entries
	var entries = d3.selectAll(".entry")[0]
	entries.forEach(function(entry){
		variable_values=variable_values+entry.getAttribute("name")+"="+entry.getAttribute("value")+"&";
	});
	//dropdowns
	var dropdowns = d3.selectAll(".dropdown")[0]
	dropdowns.forEach(function(dropdown){
		variable_values=variable_values+dropdown.getAttribute("name")+"="+dropdown.selectedOptions[0].getAttribute("value").replace(" ", "")+"&";
	});


	//make the vall with the parameter values:
	console.log(variable_values)
	console.log("call:"+"http://smartercities-api.mybluemix.net/output/"+current_model+variable_values)
	$.get("http://smartercities-api.mybluemix.net/output/"+current_model+variable_values,{}).then(function(data){
		//make amCharts
		(data.output.amCharts).forEach(function(chart){
			d3.select("#outputdiv")
				.append("div")
				.attr("id",chart.name)
				.style("width","100%")
				.style("height","400px")
				.style("background-color", "lightgray");

			AmCharts.makeChart(chart.name,chart);
		});

		//make maps
		(data.output.maps).forEach(function(m){
			//add the map to the output div
			d3.select("#outputdiv")
				.append("div")
				.style("height", "500px")
				.attr("id", m.name);
			
			//make map visible
			var map = L.map(m.name).setView([m.view.lat, m.view.lon], m.view.zoom);
			L.tileLayer('http://{s}.tiles.mapbox.com/v3/seanluciotolentino.jhknj4m5/{z}/{x}/{y}.png', {
				attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
				maxZoom: 18
			}).addTo(map);

			//add circles
			(m.circles).forEach(function(c){
				console.log(c)
				L.circle([c.lat, c.lon], c.radius, {
				    color: c.color,
				    fillColor: c.fillColor,
				    fillOpacity: c.fillOpacity
				}).addTo(map);
			});

			//add markers
			(m.markers).forEach(function(marker){
				L.marker([marker.lat, marker.lon]).addTo(map)
						.bindPopup(marker.text)
			});

		});

		//make texts -- for testing
		(data.output.text).forEach(function(t){
			d3.select("#outputdiv")
				.append("p")
				.html(t);
		})
	});
}

</script>


</div>