<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// ------------------- HELPER FUNCTIONS ------------------- //
function insertRecords($db, $data) {
  $sql = "INSERT INTO users (
           name, username, email, street, suite, city, zipcode, 
           geo_lat, geo_lng, phone, website, comp_name, 
           comp_catchPhrase, comp_bs) VALUES 
           (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

  echo "$sql<br>";  // delete later

  $dbc = $db->prepare($sql);
  foreach($data as $key => $val) {
    $dbc->bind_param("sssssssddsssss",
       $val->name, $val->username, $val->email, $val->address->street, $val->address->suite,
       $val->address->city, $val->address->zipcode, $val->address->geo->lat,
       $val->address->geo->lng, $val->phone, $val->website, $val->company->name, 
       $val->company->catchPhrase, $val->company->bs
    );
    $dbc->execute();
  }
  $dbc->close();
}

function viewRecordsByName($db) {
  $sortedData = array();
  $sql = "SELECT id, name, username, email, street, suite, city, zipcode, 
          geo_lat, geo_lng, phone, website, comp_name, 
          comp_catchPhrase, comp_bs
          FROM users
          ORDER BY name ASC;";

  $dbc = $db->query($sql);
  if(!empty($dbc) && $dbc->num_rows > 0) {
    while($row = $dbc->fetch_assoc()) {
      $sortedData[] = array(
        "id" => $row["id"],
        "name" => $row["name"],
        "username" => $row["username"],
        "email" => $row["email"],
        "street" => $row["street"],
        "suite" => $row["suite"],
        "city" => $row["city"],
        "zipcode" => $row["zipcode"],
        "geo_lat" => $row["geo_lat"],
        "geo_lng" => $row["geo_lng"],
        "phone" => $row["phone"],
        "website" => $row["website"],
        "comp_name" => $row["comp_name"],
        "comp_catchPhrase" => $row["comp_catchPhrase"],
        "comp_bs" => $row["comp_bs"]
      );
    }
  }

  // echo "<pre>" . print_r($sortedData, true) . "</pre>";
  return $sortedData;
}

function updateEmailAddressOfUser($db, $userId, $userEmail) {
  $sql = "UPDATE users SET email = ? WHERE id = ?";

  $dbc = $db->prepare($sql);
  $dbc->bind_param("si", $userEmail, $userId);
  $dbc->execute();

  $dbc->close();
}

function viewUsersByLng($dbs, $longtitude) {
  $data = array();
  $sql = "SELECT id, name, username, email, geo_lng FROM users
          WHERE geo_lng > ?
          ORDER BY geo_lng ASC";

  $dbc = $dbs->prepare($sql);
  $dbc->bind_param("d", $longtitude);
  $dbc->execute();
  $dbc->bind_result($id, $name, $username, $email, $geo_lng);
  while($dbc->fetch()) {
    $data[] = array(
      "id" => $id,
      "name" => $name,
      "username" => $username,
      "email" => $email,
      "geo_lng" => $geo_lng
    );
  }
  $dbc->close();
  
  echo "<pre>" . print_r($data, true) . "</pre>";
  return $data;
}


// ------------------- MAIN ------------------- //

// FORKED from https://replit.com/@huntergj/PHP-MySQL for creating DB on Replit //
$host = "127.0.0.1";
$user = getenv("db_user");
$pass = getenv("db_pass");
$dbName = getenv("db_name");
$dbs = new mysqli($host, $user, $pass, $dbName);

if ($dbs->connect_error)
    die("Connection failed: " . $dbs->connect_error);

// Code starts here //
// #1 - Fetch user data
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, 'https://jsonplaceholder.typicode.com/users');
$result = curl_exec($ch);
curl_close($ch);

$userData = json_decode($result);

// #2 - Display Records
// echo "<pre>" . print_r($userData[0], true) . "</pre>";

// #3 - Create SQL Table
// $sqlTable = "CREATE TABLE users (
//   id INT AUTO_INCREMENT,
//   name varchar(255) NOT NULL,
//   username varchar(255) NOT NULL,
//   email varchar(50) NOT NULL,
//   street varchar(255) NOT NULL,
//   suite varchar(255) NOT NULL,
//   city varchar(255) NOT NULL,
//   zipcode varchar(20) NOT NULL,
//   geo_lat DECIMAL(10, 7) NOT NULL,
//   geo_lng DECIMAL(10, 7) NOT NULL,
//   phone varchar(25) NOT NULL,
//   website TEXT,
//   comp_name varchar(255) NOT NULL,
//   comp_catchPhrase TEXT,
//   comp_bs TEXT,
//   PRIMARY KEY(id)
// );";

// if ($dbs->query($sqlTable) === TRUE) echo "success";
// else echo "error: " . $dbs->error;

// #4 - Insert Records
// insertRecords($dbs, $userData);

// #5 - Sort Records by Name
// $dataSortedByName = sortRecordsByName($dbs);

// #6 - Update Email
// The parameters are not needed but it'll make it easier to change ID and email for any other user
// updateEmailAddressOfUser($dbs, 9, "coding@arrowheadcu.org");

// #7 - Filter by Longtitude
// Same as #6; make it easier to change longtitude value
$usersByLng = viewUsersByLng($dbs, -110.455);


// $dataSortedByName = viewRecordsByName($dbs);
// echo "<pre>" . print_r($dataSortedByName, true) . "</pre>";
?>

<!DOCTYPE html>
<html lang="en-US">
  <head>
    <title>PHP + MySQL</title>
    <meta charset="UTF-8">
  </head>
  <body>
    <?php
      // echo "<pre>" . print_r($obj, true) . "</pre>";
    ?>
  </body>
</html>

<script type="text/javascript">

</script>
