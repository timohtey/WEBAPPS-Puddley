<!DOCTYPE html>
<html>
    <head>

        <meta charset="utf-8">
        <title>Routee</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <link href="css/simple.css" rel="stylesheet">        
        <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDQFSdn0OTS5bgEVYvfGMBWmkC54uk-6PM&sensor=false"></script>
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>

        <?php
        try {
            if (!empty($_REQUEST['pType']) && !empty($_REQUEST['placeName']) && !empty($_REQUEST['pDesc'])) {
                $type = $_REQUEST['pType'];
                $place = $_REQUEST['placeName'];
                $Description = $_REQUEST['pDesc'];
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

            var currLetter = 65
            var markers = [];

            //Existing points
            var Existing_points = new Array();
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

                if (place != '' && type != '' && desc != '')
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
                            if (type == "Accident")
                            {
                                typeConcat = '<option value="Accident" selected= "selected">Accident</option>' +
                                        '<option value="Flood">Flood</option>' +
                                        '<option value="Construction">Construction</option>' +
                                        '<option value="Heavy Traffic">Heavy Traffic</option>' +
                                        '<option value="Others">Others</option>';
                            }
                            else if (type == "Flood")
                            {
                                typeConcat = '<option value="Accident" >Accident</option>' +
                                        '<option value="Flood" selected= "selected">Flood</option>' +
                                        '<option value="Construction">Construction</option>' +
                                        '<option value="Heavy Traffic">Heavy Traffic</option>' +
                                        '<option value="Others">Others</option>';
                            }
                            else if (type == "Construction")
                            {
                                typeConcat = '<option value="Accident" >Accident</option>' +
                                        '<option value="Flood" >Flood</option>' +
                                        '<option value="Construction" selected= "selected">Construction</option>' +
                                        '<option value="Heavy Traffic">Heavy Traffic</option>' +
                                        '<option value="Others">Others</option>';
                            }

                            else if (type == "Heavy Traffic")
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
                                    '<label for="pType"><span>Area Type :</span> <select name="pType" class="save-type">' + typeConcat + '</select></label>' +
                                    '<label for="pDesc"><span>Event Details</span><textarea name="pDesc" class="save-desc" placeholder="Enter Details" maxlength="200">' + desc + '</textarea></label>' +
                                    '</form>' +
                                    '</div></p><button name="save-marker" class="save-marker">Save Report!</button>';

                            add_marker(pos, 'Report Area', Report_Form, true, true, true, "");
                        }


                    });
                }

                //Get the existing points
                $.get("dbControl.php", function(data) {
                    $(data).find("marker").each(function() {

                        var type = $(this).attr('type');
                        var desc = '<p>' + $(this).attr('description') + '</p>';
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
                        Existing_points.push(point);
                        add_marker(point, type, desc, true, false, false, iconPath);
                    });
                });



                //Right Click to Drop a New Marker
                google.maps.event.addListener(map, 'rightclick', function(event) {
                    //form to be displayed with new marker
                    var lat = event.latLng.lat();
                    var lng = event.latLng.lng();
                    var latlng = new google.maps.LatLng(lat, lng);
                    geocoder.geocode({'latLng': latlng}, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                marker = new google.maps.Marker({
                                    position: latlng,
                                    map: map
                                });
                                infowindow.setContent(results[1].formatted_address);
                                infowindow.open(map, marker);
                                var place = results[1].formatted_address;
                                alert(place);
                            } else {
                                alert('No results found');
                            }
                        } else {
                            alert('Geocoder failed due to: ' + status);
                        }
                    });
                    var Report_Form = '<p><div class="marker-edit">' +
                            '<form action="ajax-save.php" method="POST" name="SaveMarker" id="SaveMarker">' +
                            '<label for="pType"><span>Area Type :</span> <select name="pType" class="save-type"><option value="Accident">Accident</option><option value="Flood">Flood</option>' +
                            '<option value="Construction">Construction</option><option value="Heavy Traffic">Heavy Traffic</option><option value="Others">Others</option></select></label>' +
                            '<label for="pDesc"><span>What Happened here ?</span><textarea name="pDesc" class="save-desc" placeholder="Enter Details" maxlength="200"></textarea></label>' +
                            '</form>' +
                            '</div></p><button name="save-marker" class="save-marker">Save Report!</button>';

                    add_marker(event.latLng, 'Report Area', Report_Form, true, true, true, "");
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
                        var mType = contentString.find('select.save-type')[0].value; //type of marker
                        var mDesc = contentString.find('textarea.save-desc')[0].value; //description input field value

                        if (mDesc === '')
                        {
                            alert("Please enter Description!");
                        } else {
                            save_marker(marker, mDesc, mType, mReplace);
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

            function placeMarker(location) {
                var image;

                if (startIsSet == true) {

                    image = "images/markers/brown_Marker";
                    image = image.concat(String.fromCharCode(currLetter++));
                    image = image.concat(".png");

                    var marker = new google.maps.Marker({
                        position: destinationLocation,
                        map: map,
                        icon: image
                    });
                    startIsSet = false;

                } else {
                    image = "images/markers/blue_Marker";
                    image = image.concat(String.fromCharCode(currLetter));
                    image = image.concat(".png");

                    var marker = new google.maps.Marker({
                        position: startLocation,
                        map: map,
                        icon: image

                    });
                    startIsSet = true;
                }
                markers.push(marker);

            }
            //------------------SAVE MARKER TO DB FUNCTION---------------------------
            function save_marker(Marker, mDesc, mType, replaceWin)
            {
                //Save new marker using jQuery Ajax
                var mLatLang = Marker.getPosition().toUrlValue();
                var myData = {description: mDesc, latlang: mLatLang, type: mType};
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


            function routeAddress() {
                var source = document.getElementById("sourceTextBox").value;
                var destination = document.getElementById("destinationTextBox").value;
                geocoder.geocode({'address': source}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        startLocation = results[0].geometry.location;
                        placeMarker(startLocation);

                        geocoder.geocode({'address': destination}, function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                destinationLocation = results[0].geometry.location;
                                placeMarker(destinationLocation);
                                var request = {
                                    origin: startLocation,
                                    destination: destinationLocation,
                                    provideRouteAlternatives: true,
                                    travelMode: google.maps.TravelMode.DRIVING
                                };

                                directionsService.route(request, function(response, status) {
                                    if (status == google.maps.DirectionsStatus.OK)
                                    {
                                        map.fitBounds(response.routes[0].bounds);
                                        //directionsDisplay.setDirections(response);
                                        createPolyline(response);
                                        var nPoints = response.routes[0].overview_path.length;
                                        for (var i = 0; i < nPoints; i++)
                                        {
                                            var myLatlng = new google.maps.LatLng(response.routes[0].overview_path[i].lat(), response.routes[0].overview_path[i].lng());
                                            if(google.maps.geometry.poly.isLocationOnEdge(myLatlng, Existing_points))
                                                 alert(myLatlng);
                                        }
                                    } else
                                        alert("Routing failed!");

                                });



                            }
                        });

                    }
                });

            }

            function createPolyline(directionResult) {
                var line = new google.maps.Polyline({
                    path: directionResult.routes[0].overview_path, strokeColor: '#FF0000',
                    strokeOpacity: 0.5,
                    strokeWeight: 4
                });

                line.setMap(map);

                for (var i = 0; i < line.getPath().length; i++) {
                    var marker = new google.maps.Marker({
                        icon: {path: google.maps.SymbolPath.CIRCLE, scale: 3},
                        position: line.getPath().getAt(i),
                        map: map
                    });
                }
            }



            google.maps.event.addDomListener(window, 'load', initialize);
            google.maps.event.addDomListener(window, "resize", function() {
                var center = map.getCenter();
                google.maps.event.trigger(map, "resize");
                map.setCenter(center);
            });

        </script>     


    </head>
    <body >

        <div class = "navbar navbar-default navbar-fixed-top">
            <div class = "container">
                <a href = "#" class = "navbar-brand">Routee</a>
                <button class = "navbar-toggle" data-toggle = "collapse" data-target = ".navHeaderCollapse">
                    <span class = "icon-bar"></span>
                    <span class = "icon-bar"></span>
                    <span class = "icon-bar"></span>
                </button>
                <div class = "collapse navbar-collapse navHeaderCollapse">
                    <ul class = "nav navbar-nav navbar-right">
                        <form class="navbar-form navbar-left" role="search">
                            <div class="form-group">
                                <input id="sourceTextBox" type="text" class="form-control" placeholder="From where?">
                                <input id="destinationTextBox" type="text" class="form-control" placeholder="To where?">
                                <input type="button" onclick="routeAddress()" class="btn btn-default" value="Find It">
                            </div>
                            <!--
                            <button type="submit" onclick="routeAddress()" class="btn btn-default">Find it</button>
                            -->

                        </form>
                    </ul>
                </div>
            </div>
        </div>


        <div id="map-canvas"></div>

        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src = "js/bootstrap.js"></script>



    </body>
</html>