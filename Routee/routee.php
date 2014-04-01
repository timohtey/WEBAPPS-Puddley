<!DOCTYPE html>
<html>
    <head>

        <meta charset="utf-8">
        <title>Routee</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <link href="css/simple.css" rel="stylesheet">        
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
                $source = 'From where?';
                $destination = 'To where?';
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
            var eventDates = new Array();
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
                        var date_events = $(this).attr('date');
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
                        eventDates.push(date_events + " " + type + " " + point);

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
                var options = {
                    componentRestrictions: {country: "ph"}
                };
                var autocomplete = new google.maps.places.Autocomplete(sourceInput, options);
                var autocomplete2 = new google.maps.places.Autocomplete(destinationInput, options);

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



            /*----------------- AUTO DELETION SCRIPTS  ------------------*/
            /*----------------- RUNS EVERY MIDNIGHT FOR TYPES [CONSTRUCTION, FLOOD AND OTHERS]  ------------------*/
            var now = new Date();
            var timeForRefresh = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0) - now;

            if (timeForRefresh < 0)
            {
                timeForRefresh += 86400000;
            }
            var six_hours = 21600000;

            /*Time function per 24 hours at 00:00*/
            setTimeout(function() {

                for (var i = 0; i < eventDates.length; i++)
                {
                    refreshDel(eventDates[i], i);
                }

            }, timeForRefresh);
            /*Interval function per 6 hours*/
            setInterval(function() {

                for (var i = 0; i < eventDates.length; i++)
                {
                    refreshDel(eventDates[i], i);
                }


            }, six_hours);

            /*----------------- DELETION SUPPORT FUNCTION ------------------*/
            function refreshDel(date_event, index)
            {
                var oneDay = 24 * 60 * 60 * 1000;
                date_event = date_event.split(" ");
                var parts_date = date_event[0].split("-");
                var parts_time = date_event[1].split(":");
                var query_date = new Date(parts_date[0], parts_date[1] - 1, parts_date[2], parts_time[0], parts_time[1], parts_time[2]);
                var diffDays = Math.round(Math.abs((now.getTime() - query_date.getTime()) / (oneDay)));

                var oneHour = 3600000;
                var diffHours = Math.round(Math.abs((now.getTime() - query_date.getTime()) / (oneHour)));


                type = date_event[2];

                if (diffDays >= 1 && (type === "Flood" || type === "Construction" || type === "Others"))
                {
                    var pos = events[index];
                    var marker = new google.maps.Marker({
                        position: pos,
                        draggable: false
                    });
                    remove_marker(marker);
                    events.pop(events[index]);
                    eventDates.pop(date_event);
                } else if (diffDays < 1 && diffHours >= 2 && type === "Heavy Traffic")
                {
                    var pos = events[index];
                    var marker = new google.maps.Marker({
                        position: pos,
                        draggable: false
                    });
                    remove_marker(marker);
                    events.pop(events[index]);
                    eventDates.pop(date_event);
                } else if (diffDays < 1 && diffHours >= 4 && type === "Accident")
                {
                    var pos = events[index];
                    var marker = new google.maps.Marker({
                        position: pos,
                        draggable: false
                    });
                    remove_marker(marker);
                    events.pop(events[index]);
                    eventDates.pop(date_event);
                }
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

            function placeMarker(location) {
                var image;

                if (startIsSet === true) {

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
            function save_marker(Marker, mDesc, mType, replaceWin, mAddress)
            {
                //Save new marker using jQuery Ajax
                var mLatLang = Marker.getPosition().toUrlValue();
                var date = new Date();
                date = date.getFullYear() + '-' +
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
                else if (mType === "Flood")
                    iconPath = "images/custom_markers/marker_flood.png";
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
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            alert(thrownError); //throw any errors
                        }
                    });
                }
            }
           
            //VARIABLES FROM INDEX PHP INPUT
            function routeAddress() {
                source = document.getElementById('sourceTextBox').value;
                destination = document.getElementById('destinationTextBox').value;
                
                if(source != 'From where?' && destination != 'To where?'){
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
                                            /*alert(response.routes.length);*/
                                            var index = 0;
                                            var bounds;

                                            response.routes.forEach(function(route) {
                                                var rendererOptions = {
                                                    preserveViewport: true,
                                                };
                                                directionsService = new google.maps.DirectionsService();
                                                var request = {
                                                    origin: startLocation,
                                                    destination: destinationLocation,
                                                    travelMode: google.maps.TravelMode.DRIVING
                                                };



                                                var polyline = new google.maps.Polyline({
                                                    path: [],
                                                    strokeColor: '#FF0000',
                                                    strokeWeight: 3
                                                });
                                                bounds = new google.maps.LatLngBounds();


                                                var legs = response.routes[index++].legs;
                                                if (index == 0) {
                                                    center = legs.start_location;
                                                }
                                                for (i = 0; i < legs.length; i++) {
                                                    var steps = legs[i].steps;
                                                    for (j = 0; j < steps.length; j++) {
                                                        var nextSegment = steps[j].path;
                                                        for (k = 0; k < nextSegment.length; k++) {
                                                            polyline.getPath().push(nextSegment[k]);
                                                            bounds.extend(nextSegment[k]);
                                                        }
                                                    }
                                                }
                                                var count = 0;
                                                events.forEach(function(element, index) {

                                                    if (google.maps.geometry.poly.isLocationOnEdge(element, polyline, .0001)) {
                                                        console.log(element + " YES");
                                                        count++;
                                                    } else {
                                                        console.log(element + " not on edge");
                                                    }

                                                });


                                                directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
                                                directionsDisplay.setOptions({directions: response, routeIndex: index});
                                                directionsDisplay.setMap(map);

                                            });

                                            /* - edit panel to display count somewhere here
                                             - sets directions panel
                                             */
                                            document.getElementById('directions-panel').innerHTML = "";
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
            }

            /*calls css map animation*/
            function popsidebar(mapcvs) {

                mapcvs.style.webkitAnimationName = 'movemap';
                mapcvs.style.webkitAnimationDuration = '1s';


                setTimeout(function() {
                    mapcvs.style.webkitAnimationName = '';
                    mapcvs.style.marginRight = "280px";
                }, 1000);

            }

            /*event listener for button click - launches animation and routes*/
            window.onload = function() {
                document.getElementById('findItButton').onclick = function() {
                    routeAddress();
                    popsidebar(document.getElementById('map-content'));
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
                <a href = "index.php" class = "navbar-brand"><img src="images/routee.png" style="height: 20px; width: 20px"></a>
                <button class = "navbar-toggle" data-toggle = "collapse" data-target = ".navHeaderCollapse">
                    <span class = "icon-bar"></span>
                    <span class = "icon-bar"></span>
                    <span class = "icon-bar"></span>
                </button>
                <div class = "collapse navbar-collapse navHeaderCollapse">
                    <ul class = "nav navbar-nav navbar-right">
                        <form class="navbar-form navbar-left" role="search">
                            <div class="form-group">
                                <input id="sourceTextBox" type="text" class="form-control" placeholder = "From where?" value='<?php echo $source; ?>'>
                                <input id="destinationTextBox" type="text" class="form-control" placeholder = "To where?" value='<?php echo $destination; ?>'>
                                <input id="findItButton" type="button" class="btn btn-default" value="Find It">
                            </div>
                        </form>
                    </ul>
                </div>
            </div>
        </div>


        <div id="map-content">
            <div id="map-canvas" class="col-lg-12"></div>
            <div id="wrapper">

                <div id="directions-panel"></div>
                <div style="clear: both;"></div>
            </div>
        </div>

        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src = "js/bootstrap.js"></script>



    </body>
</html>