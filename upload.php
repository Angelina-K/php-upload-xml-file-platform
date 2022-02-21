<?php
include 'includes/dbService.inc.php';

if(isset($_POST['submit'])) {
    $file=$_FILES['file'];
    $fileName=$_FILES['file']['name'];
    $fileTmpName=$_FILES['file']['tmp_name'];
    $filSize=$_FILES['file']['size'];
    $fileError=$_FILES['file']['error'];
    $fileType=$_FILES['file']['type'];

    $fileExt= explode('.', $fileName);
    $fileActualExt= strtolower(end($fileExt));

    $typeAllowed='xml';

    if ($fileActualExt===$typeAllowed) {
        $fileDestination = 'uploads/'. $fileName;
        move_uploaded_file($fileTmpName, $fileDestination);
        echo "File uploaded successfully";

        uploadToDb($fileName);
    } else {
        echo "Only .xml file are allowed";
    }
}


function uploadToDb($fileName){ 
$data=simplexml_load_file('uploads/' . $fileName) or die("Error: Cannot create object");

$dataAttributes= $data->results->player->attributes();
$columns=(array) null;
$columnNames=(array) null;
$columnType="varchar(255) NOT NULL";

foreach ($dataAttributes  as $key => $value) {
    array_push($columns, "{$key}" . " " . $columnType );
    array_push($columnNames, $key );
}

createTable($columns,$columnType);

foreach ($data->results->player  as $player) {
    $attributes= $player->attributes();
    $values=(array) null;
    foreach ($attributes as $key => $value) {
        array_push($values, "'{$value}'" );   
    }
    insertIntoTable($columnNames,$values);
    }
}

function createTable($columns, $columnType){
    global $dbName;
    global $tableName;
    global $connection;
    $sql= "CREATE TABLE IF NOT EXISTS $dbName . $tableName (" . implode(', ', $columns) . ") ;";

    mysqli_query($connection, $sql);
}

function insertIntoTable($columns,$values){
    global $tableName;
    global $connection;
    $sql = "INSERT INTO $tableName (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");";

    mysqli_query( $connection, $sql);
}
