<html lang = "en">

    <meta charset="utf-8">
    <title>Routee</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="css/simple.css" rel="stylesheet">


<head>
    <meta charset="utf-8">
    <title>Routee</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="css/simple.css" rel="stylesheet">
    <style>
    body
    {
        background-image: url(roadflat.jpg);
        background-repeat:no-repeat;
        background-size:cover;
        background-position:center;
    }
    </style>
    
</head>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js">
</script>

<script>
$(document).ready(function(){
  $("#rr").click(function(){
    $("#rr").fadeOut();
    $("#rp").fadeOut();
    $("#route").fadeIn();
  });
});

$(document).ready(function(){
  $("#rp").click(function(){
    $("#rp").fadeOut();
    $("#rr").fadeOut();
    $("#report").fadeIn();
  });
});

$(document).ready(function(){
  $("#getmrp").click(function(){
    $("#rp").fadeIn();
    $("#rr").fadeIn();
    $("#report").fadeOut();
  });
});

$(document).ready(function(){
  $("#getmrr").click(function(){
    $("#rp").fadeIn();
    $("#rr").fadeIn();
    $("#route").fadeOut();
  });
});

</script>

<body>

<nav class="navbar navbar-default navbar-static-top" role="navigation">
  <div class="container">
  </div>
</nav>

<br>
<br>
<br>
<br>
<br>
<br>

<div class = "container">
    
    <div class = "row">
        <div class = "col-lg-4">
            <button type="button" class="btn btn-primary btn-lg btn-block" id = "rr">Reroute</button>
        </div>

        <div class = "col-lg-4">
        </div>

        <div class = "col-lg-4">
            <button type="button" class="btn btn-primary btn-lg btn-block" id = "rp">Report</button>
        </div>

    </div>

    <br><br>

    <div class = "row">
        <div class = "col-lg-4" style = "display:none;" id = "route">
            <div class = "well">
                
                <h3 align = "center">Stuck?</br> No need to worry. </h3>
                <div class = "row">
                
                    <div class="col-lg-12">
                        <input type="text" class="form-control" placeholder="From where?">
                    </div>
                </div>
                
                <br/>

                <div class = "row">
                    <div class="col-lg-12">
                        <input type="text" class="form-control" placeholder="To where?">
                    </div>
                </div>

                <br/>
                
                      <div class="btn-group btn-group-justified">
                             <div class="btn-group">
                                <button type="button" class="btn btn-success">Show me the way</button>
                            </div>
                             <div class="btn-group">
                                <button type="button" class="btn btn-danger" id = "getmrr">Changed my mind</button>
                            </div>
                        </div>

                
            
                
            </div><!--well end-->

        </div> <!--col-lg-4 end-->
            
        <div class = "col-lg-8" style = "display:none;" id = "report">
                    
            <div class = "row">
                <div class = "col-lg-6">
                    <div class = "well">
                        <h3 align = "center"> Please give us areas that need attention. </h3></br>
                        
                        <div class = "row">
                            <div class="col-lg-12">
                                <input type="text" class="form-control" placeholder = "What's the situation?">
                            </div>
                        </div>  
                        <br/>
                        <div class = "row">
                            <div class="col-lg-12">
                                <input type="text" class="form-control" placeholder = "And where is it happening?">
                            </div>
                        </div>  
                        <br/>

                        <div class="btn-group btn-group-justified">
                             <div class="btn-group">
                                <button type="button" class="btn btn-success">Contribute</button>
                            </div>
                             <div class="btn-group">
                                <button type="button" class="btn btn-danger" id = "getmrp">Changed my mind</button>
                            </div>
                        </div>

                    </div>
                </div>
                
                
                
            </div>
                    

            </div> <!-- col-lg-8 end-->
            
            
        </div>
    </div>


</body>

</html>