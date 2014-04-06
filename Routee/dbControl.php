<?php

// database settings 
$db_username = 'root';
$db_password = 'berserkx';
$db_name = 'impassableareas';
$db_host = 'localhost';

//mysqli
$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);

if (mysqli_connect_errno()) {
    header('HTTP/1.1 500 Error: Could not connect to db!');
    exit();
}

if ($_POST) {
    $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    if (!$xhr) {
        header('HTTP/1.1 500 Error: Request must come from Ajax!');
        exit();
    }

    $mLatLang = explode(',', $_POST["latlang"]);
    $mLat = filter_var($mLatLang[0], FILTER_VALIDATE_FLOAT);
    $mLng = filter_var($mLatLang[1], FILTER_VALIDATE_FLOAT);

    if (isset($_POST["del"]) && $_POST["del"] == true) {
        $results = $mysqli->query("DELETE FROM markers WHERE lat=$mLat AND lng=$mLng");
        if (!$results) {
            header('HTTP/1.1 500 Error: Could not delete Markers!');
            exit();
        }
        exit("Done!");
    }

    $mDesc = filter_var($_POST["description"], FILTER_SANITIZE_STRING);
    $mType = filter_var($_POST["type"], FILTER_SANITIZE_STRING);
    $mAddress = filter_var($_POST["address"], FILTER_SANITIZE_STRING);
    $mDate = filter_var($_POST["date"], FILTER_SANITIZE_STRING);

    $results = $mysqli->query("INSERT INTO markers (description, lat, lng, type, address, date) VALUES ('$mDesc',$mLat, $mLng, '$mType', '$mAddress', '$mDate')");
    if (!$results) {
        header('HTTP/1.1 500 Error: Could not create marker!');
        exit();
    }

    $output = '<h3 class="marker-heading">' . $mType . '</h3><h6>' . $mDate. '</h6><p>' . $mAddress . '</p><hr><p>' . $mDesc .'</p>';
    exit($output);
}

//Create a new DOMDocument object
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers"); //Create new element node
$parnode = $dom->appendChild($node); //make the node show up 
// Select all the rows in the markers table
$results = $mysqli->query("SELECT * FROM markers WHERE 1");
if (!$results) {
    header('HTTP/1.1 500 Error: Could not get markers!');
    exit();
}

//set document header to text/xml
header("Content-type: text/xml");

// Iterate through the rows, adding XML nodes for each
while ($obj = $results->fetch_object()) {
    $node = $dom->createElement("marker");
    $newnode = $parnode->appendChild($node);
    $newnode->setAttribute("description", $obj->description);
    $newnode->setAttribute("lat", $obj->lat);
    $newnode->setAttribute("lng", $obj->lng);
    $newnode->setAttribute("type", $obj->type);
    $newnode->setAttribute("Address", $obj->Address);
    $newnode->setAttribute("date", $obj->date);
}
echo $dom->saveXML();
?>
