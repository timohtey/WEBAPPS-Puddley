<!DOCTYPE html>
<html>
    <div id ="container">
        <head>
            <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDQFSdn0OTS5bgEVYvfGMBWmkC54uk-6PM&sensor=false">
            </script>

            <script>
                function initialize()
                {
                    var centerOfMap = new google.maps.LatLng(14.56486, 120.99370);
                    var myHouse = new google.maps.LatLng(14.43243, 120.98703);

                    var mapInitialize = {
                        center: centerOfMap,
                        zoom: 13,
                        mapTypeId: google.maps.MapTypeId.ROADMAP

                    };
                    var map = new google.maps.Map(document.getElementById("PuddleyMap")
                            , mapInitialize);

                    var marker = new google.maps.Marker({
                        position: centerOfMap,
                        animation: google.maps.Animation.BOUNCE,
                    });

                    var houseMarker = new google.maps.Marker({
                        position: myHouse,
                        animation: google.maps.Animation.BOUNCE,
                    });

                    var houseLine = [centerOfMap, myHouse];
                    var Path = new google.maps.Polyline({
                        path: houseLine,
                        strokeColor: "#DC143C",
                        strokeOpacity: 0.8,
                        strokeWeight: 2
                    });


                    Path.setMap(map);
                    houseMarker.setMap(map);
                    marker.setMap(map);
                }
                google.maps.event.addDomListener(window, 'load', initialize);

            </script>

            <meta charset="utf-8">
            <title>Puddley</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="">
            <meta name="author" content="">

            <link href="Styles/bootstrap.css" rel="stylesheet">

        </head>

        <body>
            <!-- <div id="PuddleyMap" style="width:700px;height:700px;"></div> -->

            <div class="masthead">
                <img src="Images/shot0415.png">
            </div>

            <div class="container">
                <div class="row">
                    <div class="col-lg-7">
                        
                    </div>
                    <div class="col-lg-5">
                         
                    </div>
                </div>
            </div>


            <script src = "js/bootstrap.js"></script>

            <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->  
            <script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>

            <!-- Include all compiled plugins (below), or include individual files as needed -->  
            <script src="Javascript/bootstrap.min.js"></script>  

        </body>
        <footer>
            <p>(C) 2014 </p>
        </footer>
    </div>
</html>