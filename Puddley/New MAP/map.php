<!DOCTYPE html>
<html>
    <head>
        <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDQFSdn0OTS5bgEVYvfGMBWmkC54uk-6PM&sensor=false">
        </script>

        <meta charset="utf-8">
        <title>Puddley</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <link href="Styles/bootstrap.css" rel="stylesheet">

        <script>
            var map;
            var centerOfMap = new google.maps.LatLng(14.56486, 120.99370);

            var startIsSet = false;
            var startLocation;
            var destinationLocation;
            var waypts = [];

            var currLetter = 65;
            

            var markers = [];

            var directionsDisplay;
            var directionsService = new google.maps.DirectionsService();
          

            function getCurrentLocation(){
                navigator.geolocation.getCurrentPosition(onSuccess, onError);
            }

            function onSuccess(position) {
                centerOfMap = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                
                initialize();
            }

       
             function onError(error) {
                alert('code: ' + error.code + '\n' +
                    'message: ' + error.message + '\n');
            }

            function initialize()
            {
                directionsDisplay = new google.maps.DirectionsRenderer();
                var mapInitialize = {
                    center: centerOfMap,
                    zoom: 15,
                    disableDoubleClickZoom: true,
                    mapTypeId: google.maps.MapTypeId.ROADMAP

                };
                map = new google.maps.Map(document.getElementById("PuddleyMap")
                        , mapInitialize);

                directionsDisplay.setMap(map);
                directionsDisplay.setOptions( { suppressMarkers: true } );
                google.maps.event.addListener(map, "dblclick", function (e) { 
                         if(startIsSet == true){
                            destinationLocation = new google.maps.LatLng(e.latLng.lat(), e.latLng.lng());
                            
                            placeMarker(destinationLocation);
                            startIsSet = false;
                            var request = {
                              origin: startLocation,
                              destination: destinationLocation,
                              waypoints: waypts,
                              optimizeWaypoints: true,
                              travelMode: google.maps.TravelMode.DRIVING
                            };

                            directionsService.route(request, function(response, status) {
                                if (status == google.maps.DirectionsStatus.OK) {
                                  directionsDisplay.setDirections(response);
                                }else
                                    alert("Routing failed!");
                                  
                            });

                         }else{
                            startLocation = new google.maps.LatLng(e.latLng.lat(), e.latLng.lng());
                           
                            placeMarker(startLocation);
                            startIsSet = true;
                         }
                             
                }); 


                $.get("dbControl.php", function(data) {
                    $(data).find("marker").each(function() {

                        var type = $(this).attr('type');
                        var desc = '<p>' + $(this).attr('description') + '</p>';
                        var point = new google.maps.LatLng(parseFloat($(this).attr('lat')), parseFloat($(this).attr('lng')));

                        //Iterate through types of icons
                        add_marker(point, type, desc, true, false, false, "");
                    });
                });
                //Right Click to Drop a New Marker
                google.maps.event.addListener(map, 'rightclick', function(event) {
                    //form to be displayed with new marker
                    var Report_Form = '<p><div class="marker-edit">' +
                            '<form action="ajax-save.php" method="POST" name="SaveMarker" id="SaveMarker">' +
                            '<label for="pType"><span>Area Type :</span> <select name="pType" class="save-type"><option value="Accident">Accident</option><option value="Flood">Flood</option>' +
                            '<option value="Construction">Construction</option><option value="Heavy Traffic">Heavy Traffic</option></select></label>' +
                            '<label for="pDesc"><span>What Happened here ?</span><textarea name="pDesc" class="save-desc" placeholder="Enter Details" maxlength="200"></textarea></label>' +
                            '</form>' +
                            '</div></p><button name="save-marker" class="save-marker">Save Report!</button>';
                    //Drop a new Marker with our Edit Form 
                    //CHANGE LAST PARAM TO CHOSEN ICON
                    add_marker(event.latLng, 'Report Area', Report_Form, true, true, true, "");
                });
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


                var infowindow = new google.maps.InfoWindow();
                infowindow.setContent(contentString[0]);

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
                        infowindow.open(map, marker);
                    }
                }
                
                 google.maps.event.addListener(marker, 'click', function() {
                        infowindow.open(map, marker);
                    });


            }

            function placeMarker(location) {
                var image;

                if(startIsSet == true){

                    image = "markers/brown_Marker";
                    image = image.concat(String.fromCharCode(currLetter++));
                    image = image.concat(".png");
                    
                    var marker = new google.maps.Marker({
                        position: destinationLocation, 
                        map: map,
                        icon: image

                    });

                } else {
                    image = "markers/blue_Marker";
                    image = image.concat(String.fromCharCode(currLetter));
                    image = image.concat(".png");
                    
                    var marker = new google.maps.Marker({
                        position: startLocation, 
                        map: map,
                        icon: image

                    });
                }
                

               

                markers.push(marker);

            }

            //------------------SAVE MARKER TO DB FUNCTION---------------------------
            function save_marker(Marker, mDesc, mType, replaceWin)
            {
                //Save new marker using jQuery Ajax
                var mLatLang = Marker.getPosition().toUrlValue();
                var myData = {description: mDesc, latlang: mLatLang, type: mType};
                console.log(replaceWin);
                $.ajax({
                    type: "POST",
                    url: "dbControl.php",
                    data: myData,
                    success: function(data) {
                        replaceWin.html(data);
                        Marker.setDraggable(false);
                        // REPLACE ICON HERE
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
                            alert(data);
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            alert(thrownError); //throw any errors
                        }
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
        <style type="text/css">
            /* Marker Add form */
            .marker-edit label{display:block; margin-bottom: 8px;}
            .marker-edit label span {width: 200px;float: left;}
            .marker-edit label input, .marker-edit label select{height: 29px;}
            .marker-edit label textarea{height: 60px; }
            .marker-edit label input, .marker-edit label select, .marker-edit label textarea {width: 100%;margin:0px;padding-left: 5px; padding-right: 5px;border: 1px solid #DDD;border-radius: 3px;}

            /* Marker Info Window */
            h3.marker-heading{color: #585858;margin: 0px;padding: 0px;font: 25px "Trebuchet MS", Arial!important;border-bottom: 1px dotted #D8D8D8;}
            div.marker-info-win {margin-right: -20px;max-width: 300px;}
            div.marker-info-win p{padding: 0px;margin: 10px 0px 10px 0;}
            div.marker-inner-win{padding: 5px;}
            button.save-marker, button.remove-marker{border: none;background: rgba(0, 0, 0, 0);color: #00F;padding: 0px;text-decoration: underline;margin-right: 10px;cursor: pointer;}
        </style>
    </head>

    <body>

        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation" >  
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.php"></a>
                </div> 
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">                              
                        <li class="active"></li>                                              
                    </ul>    
                </div>
            </div> <!-- NAV BAR CONTAINER-->
        </nav>  <!-- NAV BAR END-->

        <div class="container" style='padding-top: 50px; padding-left: 0; padding-right: 0' >
            <div class="row">
                <div id="PuddleyMap" style='width:100%; height:800px'> </div>

            </div>
        </div>


        <script src = "js/bootstrap.js"></script>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->  
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->  
        <script src="Javascript/bootstrap.min.js"></script>  

    </body>
</html>