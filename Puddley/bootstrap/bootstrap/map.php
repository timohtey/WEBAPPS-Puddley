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
        <link rel="shortcut icon" href="images/puddley_icon_header.png">

        <script>
            var map;
            function initialize()
            {
                
                var centerOfMap = new google.maps.LatLng(14.56486, 120.99370);                
                var mapInitialize = {
                    center: centerOfMap,
                    zoom: 13,
                    mapTypeId: google.maps.MapTypeId.ROADMAP

                };
				var marker =new google.maps.Marker({
					position:centerOfMap;
					icon: 'puddley_icon_header';
				});
                map = new google.maps.Map(document.getElementById("PuddleyMap")
                        , mapInitialize);              
            }
            google.maps.event.addDomListener(window, 'load', initialize);
            google.maps.event.addDomListener(window, "resize", function() {
                var center = map.getCenter();
                google.maps.event.trigger(map, "resize");
                map.setCenter(center);
            });
        </script>

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
                    <a class="navbar-brand" href="index.php">
                        <img src="images/jeep_header.png">
                        <img src="images/reroute_header.png">
                    </a>
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