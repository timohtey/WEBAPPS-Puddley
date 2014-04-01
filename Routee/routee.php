<html lang = "en">

<head>

    <meta charset="utf-8">
    <title>Routee</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="css/coco.css" rel="stylesheet">
    <link href="css/fonts.css" type = "text/css" rel = "stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css">
    
    <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDQFSdn0OTS5bgEVYvfGMBWmkC54uk-6PM&sensor=false&libraries=places"></script>        
        
        <?php
        try {
            if (!empty($_REQUEST['pType']) && !empty($_REQUEST['placeName']) && !empty($_REQUEST['pDesc'])) {
                $type = $_REQUEST['pType'];
                $place = $_REQUEST['placeName'];
                $Description = $_REQUEST['pDesc'];
            } else {
                $type = '';
                $place = '';
                $Description = '';
            }

            if (!empty($_REQUEST['sourceField']) && $_REQUEST['destinationField']) {
                $source = $_REQUEST['sourceField'];
                $destination = $_REQUEST['destinationField'];
            } else {
                 $source = '';
                $destination = '';
            }
        } catch (Exception $e) {
            
        }
        ?>
      <script>
            var map;
            var centerOfMap = new google.maps.LatLng(14.56486, 120.99370);
            var geocoder;
            var startIsSet = false;
            var startLocation;
            var destinationLocation;
            var waypts = [];

            var currLetter = 65;
            var markers = [];

            //Existing points
            var events = new Array();
            var directionsDisplay;
            var directionsService = new google.maps.DirectionsService();
            function initialize() {
                directionsDisplay = new google.maps.DirectionsRenderer();
                geocoder = new google.maps.Geocoder();
                var mapInitialize = {
                    center: centerOfMap,
                    zoom: 15,
                    disableDoubleClickZoom: true,
                    mapTypeId: google.maps.MapTypeId.ROADMAP

                };
                map = new google.maps.Map(document.getElementById('map-canvas'), mapInitialize);

                directionsDisplay.setMap(map);
                directionsDisplay.setOptions({suppressMarkers: true});

                var place = '<?php echo $place; ?>';
                var type = '<?php echo $type; ?>';
                var desc = '<?php echo $Description ?>';

                if (place !== '' && type !== '' && desc !== '')
                {
                    $.ajax({
                        url: "http://maps.googleapis.com/maps/api/geocode/json?address=" + place + "&sensor=false",
                        type: "POST",
                        success: function(res) {
                            console.log(res.results[0].geometry.location.lat);
                            console.log(res.results[0].geometry.location.lng);
                            var pos = new google.maps.LatLng(res.results[0].geometry.location.lat, res.results[0].geometry.location.lng);

                            map.setCenter(pos);
                            var typeConcat;
                            if (type === "Accident")
                            {
                                typeConcat = '<option value="Accident" selected= "selected">Accident</option>' +
                                        '<option value="Flood">Flood</option>' +
                                        '<option value="Construction">Construction</option>' +
                                        '<option value="Heavy Traffic">Heavy Traffic</option>' +
                                        '<option value="Others">Others</option>';
                            }
                            else if (type === "Flood")
                            {
                                typeConcat = '<option value="Accident" >Accident</option>' +
                                        '<option value="Flood" selected= "selected">Flood</option>' +
                                        '<option value="Construction">Construction</option>' +
                                        '<option value="Heavy Traffic">Heavy Traffic</option>' +
                                        '<option value="Others">Others</option>';
                            }
                            else if (type === "Construction")
                            {
                                typeConcat = '<option value="Accident" >Accident</option>' +
                                        '<option value="Flood" >Flood</option>' +
                                        '<option value="Construction" selected= "selected">Construction</option>' +
                                        '<option value="Heavy Traffic">Heavy Traffic</option>' +
                                        '<option value="Others">Others</option>';
                            }

                            else if (type === "Heavy Traffic")
                            {
                                typeConcat = '<option value="Accident" >Accident</option>' +
                                        '<option value="Flood" >Flood</option>' +
                                        '<option value="Construction" >Construction</option>' +
                                        '<option value="Heavy Traffic" selected= "selected">Heavy Traffic</option>' +
                                        '<option value="Others">Others</option>';
                            }
                            else
                            {
                                typeConcat = '<option value="Accident" >Accident</option><option value="Flood" >Flood</option>' +
                                        '<option value="Construction" >Construction</option><option value="Heavy Traffic" >Heavy Traffic</option>' +
                                        '<option value="Others" selected= "selected">Others</option>';
                            }

                            var Report_Form = '<p><div class="marker-edit">' +
                                    '<form action="ajax-save.php" method="POST" name="SaveMarker" id="SaveMarker">' +
                                    '<label for="pAddress"><span>Address :</span> <textarea disabled name="address_ta" class="save-add" maxlength="200" placeholder= "Address">' + place + '</textarea></label>' +
                                    '<label for="pType"><span>Area Type :</span> <select name="pType" class="save-type">' + typeConcat + '</select></label>' +
                                    '<label for="pDesc"><span>Event Details</span><textarea name="pDesc" class="save-desc" placeholder="Enter Details" maxlength="200">' + desc + '</textarea></label>' +
                                    '</form>' +
                                    '</div></p><button name="save-marker" class="save-marker">Save Report!</button>';

                            add_marker(pos, 'Report Area', Report_Form, true, false, true, "");
                        }


                    });
                }

                //Get the existing points
                $.get("dbControl.php", function(data) {
                    $(data).find("marker").each(function() {

                        var type = $(this).attr('type');
                        var date = $(this).attr('date');
                        date = date.split(" ");
                        var desc = '<h6> Date:  ' + date[0] + '  Time:  ' + date[1] + ' </h6>' + $(this).attr('Address') + '</p><hr>' + '<p>' + $(this).attr('description') + '</p>';
                        var point = new google.maps.LatLng(parseFloat($(this).attr('lat')), parseFloat($(this).attr('lng')));
                        var iconPath;

                        if (type === "Accident")
                            iconPath = "images/custom_markers/marker_accident.png";
                        else if (type === "Construction")
                            iconPath = "images/custom_markers/marker_construction.png";
                        else if (type === "Heavy Traffic")
                            iconPath = "images/custom_markers/marker_traffic.png";
                        else
                            iconPath = "images/custom_markers/marker_others.png";
                        events.push(point);
                        add_marker(point, type, desc, true, false, false, iconPath);
                    });
                });
                //Right Click to Drop a New Marker
                google.maps.event.addListener(map, 'rightclick', function(event) {

                    geocoder.geocode({'latLng': event.latLng}, function(results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            var address = results[0].formatted_address;
                        }
                        else {
                            alert("Geocoder failed due to: " + status);
                        }
                        //form to be displayed with new marker
                        var Report_Form = '<p><div class="marker-edit">' +
                                '<form action="ajax-save.php" method="POST" name="SaveMarker" id="SaveMarker">' +
                                '<label for="pAddress"><span>Address :</span> <textarea disabled name="address_ta" class="save-add" maxlength="200" placeholder= "Address">' + address + '</textarea></label>' +
                                '<label for="pType"><span>Area Type :</span> <select name="pType" class="save-type"><option value="Accident">Accident</option><option value="Flood">Flood</option>' +
                                '<option value="Construction">Construction</option><option value="Heavy Traffic">Heavy Traffic</option><option value="Others">Others</option></select></label>' +
                                '<label for="pDesc"><span>What Happened here ?</span><textarea name="pDesc" class="save-desc" placeholder="Enter Details" maxlength="200"></textarea></label>' +
                                '</form>' +
                                '</div></p><button name="save-marker" class="save-marker">Save Report!</button>';

                        add_marker(event.latLng, 'Report Area', Report_Form, true, false, true, "");
                    });
                });

                // -------------- AUTCOMPLETE ----------------------//
                var sourceInput = document.getElementById('sourceTextBox');
                var destinationInput = document.getElementById('destinationTextBox');

                var autocomplete = new google.maps.places.Autocomplete(sourceInput);
                var autocomplete2 = new google.maps.places.Autocomplete(destinationInput);

                autocomplete.bindTo('bounds', map);
                autocomplete2.bindTo('bounds', map);
                autocomplete.setComponentRestrictions({country: 'ph'});
                autocomplete2.setComponentRestrictions({country: 'ph'});

                var infowindow = new google.maps.InfoWindow();

                google.maps.event.addListener(autocomplete, 'place_changed', function() {
                    infowindow.close();
                    var place = autocomplete.getPlace();
                });

                google.maps.event.addListener(autocomplete2, 'place_changed', function() {
                    infowindow.close();
                    var place = autocomplete2.getPlace();
                });

                setupClickListener('changetype-all', []);
                google.maps.event.addDomListener(window, 'load', initialize);
            }



            //------------------ADD MARKER FUNCTION---------------------------
            function add_marker(MapPos, MapTitle, MapDesc, InfoOpenDefault, DragAble, Removable, iconPath)
            {

                var marker = new google.maps.Marker({
                    position: MapPos,
                    map: map,
                    draggable: DragAble,
                    animation: google.maps.Animation.DROP,
                    title: "Map Report",
                    icon: iconPath
                });

                //Content structure of info Window for the Markers
                var contentString = $('<div class="marker-info-win">' +
                        '<div class="marker-inner-win"><span class="info-content">' +
                        '<h3 class="marker-heading">' + MapTitle + '</h3>' +
                        MapDesc +
                        '</span><button name="remove-marker" class="remove-marker" title="Remove Marker">Remove Marker</button>' +
                        '</div></div>');


                var markerinfowindow = new google.maps.InfoWindow();
                markerinfowindow.setContent(contentString[0]);

                var removeBtn = contentString.find('button.remove-marker')[0];
                var saveBtn = contentString.find('button.save-marker')[0];

                google.maps.event.addDomListener(removeBtn, "click", function(event) {
                    remove_marker(marker);
                });

                if (typeof saveBtn !== 'undefined')
                {
                    //add click listner to save marker button
                    google.maps.event.addDomListener(saveBtn, "click", function(event) {
                        var mReplace = contentString.find('span.info-content');
                        var mAddress = contentString.find('textarea.save-add')[0].value; // Address
                        var mType = contentString.find('select.save-type')[0].value; //type of marker
                        var mDesc = contentString.find('textarea.save-desc')[0].value; //description input field value

                        if (mDesc === '')
                        {
                            alert("Please enter Description!");
                        } else {
                            save_marker(marker, mDesc, mType, mReplace, mAddress);
                        }
                    });


                    if (InfoOpenDefault)
                    {
                        markerinfowindow.open(map, marker);
                    }
                }
                google.maps.event.addListener(marker, 'click', function() {
                    markerinfowindow.open(map, marker);
                });
            }

            
            //------------------SAVE MARKER TO DB FUNCTION---------------------------
            function save_marker(Marker, mDesc, mType, replaceWin, mAddress)
            {
                //Save new marker using jQuery Ajax
                var mLatLang = Marker.getPosition().toUrlValue();
                var date = new Date();
                date = date.getYear() + '-' +
                        ('00' + (date.getMonth() + 1)).slice(-2) + '-' +
                        ('00' + date.getDate()).slice(-2) + ' ' +
                        ('00' + date.getHours()).slice(-2) + ':' +
                        ('00' + date.getMinutes()).slice(-2) + ':' +
                        ('00' + date.getSeconds()).slice(-2);
                console.log(mLatLang);
                console.log(date);

                var myData = {description: mDesc, latlang: mLatLang, type: mType, address: mAddress, date: date};
                var iconPath;
                if (mType === "Accident")
                    iconPath = "images/custom_markers/marker_accident.png";
                else if (mType === "Construction")
                    iconPath = "images/custom_markers/marker_construction.png";
                else if (mType === "Heavy Traffic")
                    iconPath = "images/custom_markers/marker_traffic.png";
                else
                    iconPath = "images/custom_markers/marker_others.png";
                console.log(replaceWin);
                $.ajax({
                    type: "POST",
                    url: "dbControl.php",
                    data: myData,
                    success: function(data) {
                        replaceWin.html(data);
                        Marker.setDraggable(false);
                        Marker.setIcon(iconPath);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(thrownError);
                    }
                });
            }

            //------------------REMOVE MARKER FUNCTION---------------------------
            function remove_marker(Marker)
            {
                if (Marker.getDraggable())
                {
                    Marker.setMap(null); //just remove new marker
                }
                else
                {
                    var mLatLang = Marker.getPosition().toUrlValue(); //get marker position
                    var myData = {del: 'true', latlang: mLatLang}; //post variables
                    $.ajax({
                        type: "POST",
                        url: "dbControl.php",
                        data: myData,
                        success: function(data) {
                            Marker.setMap(null);
                            alert("Report Removed!");
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            alert(thrownError); //throw any errors
                        }
                    });
                }
            }

			//VARIABLES FROM INDEX PHP INPUT
            var source = '<?php echo $source; ?>';
            var destination = '<?php echo $destination; ?>';
            
            function routeAddress() {
                source = document.getElementById('sourceTextBox').value;
                destination = document.getElementById('destinationTextBox').value;
                
                geocoder.geocode({'address': source}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        startLocation = results[0].geometry.location;
                       

                        geocoder.geocode({'address': destination}, function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                destinationLocation = results[0].geometry.location;
                                
                                var request = {
                                    origin: startLocation,
                                    destination: destinationLocation,
                                    provideRouteAlternatives: true,
                                    travelMode: google.maps.TravelMode.DRIVING
                                };

                                directionsService.route(request, function(response, status) {
                                    if (status == google.maps.DirectionsStatus.OK) {
                                    	//alert(response.routes.length);
                                       	var index = 0;
                                       	var colorindex=0;
                                       	var bounds;
                                       	
										response.routes.forEach(function(route){
											
											directionsService = new google.maps.DirectionsService();
											var request = {
										        origin: startLocation,
										        destination: destinationLocation,
										        travelMode: google.maps.TravelMode.DRIVING
										    };
										  
					    					
   	
											var polyline = new google.maps.Polyline({
												path: [],
											
											});
											bounds = new google.maps.LatLngBounds();
														
												
											var legs = response.routes[index++].legs;
												
											for (i=0;i<legs.length;i++) {
												var steps = legs[i].steps;
												for (j=0;j<steps.length;j++) {
													var nextSegment = steps[j].path;
												    for (k=0;k<nextSegment.length;k++) {
														  polyline.getPath().push(nextSegment[k]);
														  bounds.extend(nextSegment[k]);
													}
												}
											}
											var count=0;
											
											events.forEach(function(element, index) {
													
													if (google.maps.geometry.poly.isLocationOnEdge(element, polyline, .0001)) {
															console.log(element + " YES");
															count++;
					                                       	
					                                       	
					                                       
													} else {
															console.log(element + " not on edge");
													}
														   		
											});

											var color;
											var colorimage = document.createElement('img');
											colorimage.style.height = "24px";
											colorimage.style.width = "24px";
											switch(index-1){
												case 0: color = '#2ecc71'; 
														colorimage.src = "images/green-colorpanel.png";
														break;
												case 1: color = '#2980b9';  
														colorimage.src = "images/blue-colorpanel.png";
														break;
												case 2: color = '#9b59b6';  
														colorimage.src = "images/purple-colorpanel.png";
														break;
											} 
											var rendererOptions = {
											    preserveViewport: true,
											    polylineOptions:{
											    	strokeColor: color,
											    	strokeWeight: 7	
											    }         
											    
											};
											
											var cRow = document.createElement('div');
											
					                        cRow.id = "row"+index;
					                        cRow.className="span2";
					                        cRow.style.margin = "17px";
					                        document.getElementById('directions-panel').appendChild(cRow);
					                        document.getElementById('row'+index).appendChild(colorimage);
					                        document.getElementById('row'+index).innerHTML += " has "+count+" obstructions.";
					                        
					                       
					                             	
											directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
											directionsDisplay.setOptions({directions:response,routeIndex:index-1});
							    			directionsDisplay.setMap(map);
							    			
							    			
					    					       
											
											
											
										});
										
                                       /*edit panel to display count somewhere here*/     
                                       
                                       
                                       
							    	   directionsDisplay.setPanel(document.getElementById('directions-panel'));
                                       map.fitBounds(bounds);
                                    } else
                                        alert("Routing failed!");

                                });

                            }
                        });

                    }
                });

            }
            window.onload = function() {
                document.getElementById('findItButton').onclick = function() {
                    routeAddress();
                };
            }

            google.maps.event.addDomListener(window, 'load', initialize);
            google.maps.event.addDomListener(window, "resize", function() {
                var center = map.getCenter();
                google.maps.event.trigger(map, "resize");
                map.setCenter(center);
            });
        </script>     

</head>


 <body onload = "routeAddress();popsidebar(document.getElementById('map-content'))">

        <div class = "navbar navbar-default navbar-fixed-top">
            <div class = "container">
                <a href = "#" class = "navbar-brand"><img src = "routee.jpg" class ="headpic"></a>
                
                <button class = "navbar-toggle" data-toggle = "collapse" data-target = ".nav-collapse" type="button">
                <span class="sr-only">Toggle navigation</span>
                    <span class = "icon-bar"></span>
                    <span class = "icon-bar"></span>
                    <span class = "icon-bar"></span>
                </button>
                <div class = "collapse navbar-collapse nav-collapse">
                    <ul class = "nav navbar-nav navbar-right">
                        <form class="navbar-form navbar-left" role="search">
                            <div class="form-group">
                                <input id="sourceTextBox" type="text" class="form-control" placeholder = "From where?" value='<?php echo $source; ?>'>
                                <input id="destinationTextBox" type="text" class="form-control" placeholder = "To where?" value='<?php echo $destination; ?>'>
                                <input id="findItButton" type="button" class="btn btn-default" value="Find It">
                                <a id="helpbutton" href = "#getIns" data-toggle="modal"><img src = "images/help.png" class="headpic"></a>
                                
                            </div>
                            <!--
                            <button type="submit" onclick="routeAddress()" class="btn btn-default">Find it</button>
                            -->

                        </form>
                    </ul>
                    
                </div>
                
            </div>
        </div>


        <div class = "modal fade" id = "getIns" role = "dialog">
            <div class = "modal-dialog">
                <div class = "modal-content">
                   
                    <div class = "modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class = "modal-title" align = "center"> Hello there!</h4>
                    </div>

                    <div class = "modal-body">
                        <p align = "center">Salutations, driver! Thank you for using Routee! I will show you some instructions on how to use this map module of Routee. First and foremost, if you want to know the quickest routes to use out there, just click on '<em>Get Directions</em>' and to see these again, click '<em>Instructions</em>'.<br><br>
                        </p>

                        <img src = "images/ins1.jpg" class="img-responsive">
                        <br><br> 
                       
                        <p align = "center"> Another neat feature is the '<em>Reporting</em>' aspect of Routee? How do we do this? Just right-click on the location in the map and select the situation. Simple!<br><br>
                        </p>

                         <img src = "images/ins2.jpg" class="img-responsive">

                        <br><br>

                        <p align = "center"> Here are the legends when reporting for a situation: </p>
                        <br>

                        <div class="media">
                            
                            <a class="pull-left">
                                <img class="media-object" src="images/custom_markers/accident_SD.png">
                             </a>
                            <div class="media-body">
                                <h4 class="media-heading">Accident</h4>
                                <p>If a road accident had occured, this marker will represent that on the map. This type of event takes a while to resolve.</p>
                            </div>

                            <a class="pull-left">
                                <img class="media-object" src="images/custom_markers/construction_SD.png">
                             </a>
                            <div class="media-body">
                                <h4 class="media-heading">Construction</h4>
                                <p>When there is a construction project on-going, it will be represented by this marker. This event takes a very long time to resolve and should be avoided as much as possible.</p>
                            </div>

                            <a class="pull-left">
                                <img class="media-object" src="images/custom_markers/flood_SD.png">
                             </a>
                            <div class="media-body">
                                <h4 class="media-heading">Flood</h4>
                                <p>On days when there is heavy rain present, floods are likely to occur. Areas that are affected will be presented with this. Some areas are passable depending on the terrain.</p>
                            </div>

                            <a class="pull-left">
                                <img class="media-object" src="images/custom_markers/traffic_SD.png">
                             </a>
                            <div class="media-body">
                                <h4 class="media-heading">Traffic</h4>
                                <p>This is a common problem in the Philippines and is considered more of a nuisance. Areas with heavy traffic are represented by this marker.</p>
                            </div>

                            <a class="pull-left">
                                <img class="media-object" src="images/custom_markers/other_SD.png">
                             </a>
                            <div class="media-body">
                                <h4 class="media-heading">Others</h4>
                                <p>If a situation arises and it is not listed within the choices, you can present it using this marker.</p>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </div>

		
		<div id="side-panel"  class = "col-lg-3">
		
	        <div class = "well appear">
	        		
	                <div id="directions-panel"></div>
			</div>
	
	        <div class = "linkappear">
	
		        <ul class = "nav nav-tabs">
		            <li><a href = "#tab1" data-toggle="tab">Directions</a></li>
		            <li><a href = "#tab2" data-toggle="tab">Map View</a></li>
		        </ul>
	
		        <div class = "tab-content">     
		            <div class = "tab-pane active" id ="tab1">
		                <h3> Directions </h3>
		            </div>
		
		            <div class = "tab-pane" id ="tab2">
		                <div id="sm-map-canvas"></div>
		            </div>
		
		        </div>
			</div>

        </div>

		<div class = "col-lg-9">
		
	        <div class = "appear">
	       		
		        <div id="map-canvas"></div>
	            
	        </div>

        </div>
		
        </div>



	<script src="js/jquery-1.10.2.js"></script>
    <script src = "js/bootstrap.js"></script>


</body>


</html>