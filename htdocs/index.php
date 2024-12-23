<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

// ------------------- HELPER FUNCTIONS ------------------- //
function insertRecords($db, $data) {
  $sql = "INSERT INTO users (
           name, username, email, street, suite, city, zipcode, 
           geo_lat, geo_lng, phone, website, comp_name, 
           comp_catchPhrase, comp_bs) VALUES 
           (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

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
          ORDER BY name ASC";

  $dbc = $db->query($sql);
  if(!empty($dbc) && $dbc->num_rows > 0) {
    while($row = $dbc->fetch_assoc()) {
      $sortedData[] = array(
        "id" => $row["id"],
        "name" => $row["name"],
        "username" => $row["username"],
        "email" => $row["email"],
        "address" => $row["street"] . " " . $row["suite"] . " " . $row["city"] . ", " . $row["zipcode"],
        "geo" => $row["geo_lat"] . ", " . $row["geo_lng"],
        "phone" => $row["phone"],
        "website" => $row["website"],
        "comp_name" => $row["comp_name"],
        "comp_catchPhrase" => $row["comp_catchPhrase"],
        "comp_bs" => $row["comp_bs"]
      );
    }
  }
  
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
  $sql = "SELECT id, name, username, email, street, suite, city, zipcode, 
          geo_lat, geo_lng, phone, website, comp_name, 
          comp_catchPhrase, comp_bs 
          FROM users
          WHERE geo_lng > ?
          ORDER BY geo_lng ASC";

  $dbc = $dbs->prepare($sql);
  $dbc->bind_param("d", $longtitude);
  $dbc->execute();
  $dbc->bind_result(
    $id, $name, $username, $email, $street, $suite, $city, $zipcode, 
    $geo_lat, $geo_lng, $phone, $website, $comp_name, 
    $comp_catchPhrase, $comp_bs
  );
  
  while($dbc->fetch()) {
    $data[] = array(
      "id" => $id,
      "name" => $name,
      "username" => $username,
      "email" => $email,
      "address" => $street . " " . $suite . " " . $city . ", " . $zipcode,
      "geo" => $geo_lat . ", " . $geo_lng,
      "phone" => $phone,
      "website" => $website,
      "comp_name" => $comp_name,
      "comp_catchPhrase" => $comp_catchPhrase,
      "comp_bs" => $comp_bs
    );
  }
  $dbc->close();
  
  return $data;
}

function loadHTMLStyle($data, $identif=-1) {
  $sortedDataTableBody = "";
  foreach($data as $row => $col) {
    $addIdf = ($col["id"] == $identif) ? "style = 'background: var(--offset_white);'" : "";

    $sortedDataTableBody .= "
      <div class='user-data' $addIdf>
        <div class='user-data-id'>User ID: {$col["id"]}</div>
        <div class='user-data-name'><h2>{$col["name"]}</h2></div>
        <hr>
        <div class='user-data-add'>
          <span>Email:</span>
          <span>{$col["email"]}</span>
        </div>
        <div class='user-data-add'>
          <span>Address:</span>
          <span>{$col["address"]}</span>
        </div>
        <div class='user-data-add'>
          <span>Location:</span>
          <span>{$col["geo"]}</span>
        </div>
        <div class='user-data-add'>
          <span>Phone #:</span>
          <span>{$col["phone"]}</span>
        </div>
        <div class='user-data-add'>
          <span>Website:</span>
          <span>{$col["website"]}</span>
        </div>
        <div class='user-data-add'>
          <span>Company Name:</span>
          <span>{$col["comp_name"]}</span>
        </div>
        <div class='user-data-add'>
          <span>Company Catchphrase:</span>
          <span>{$col["comp_catchPhrase"]}</span>
        </div>
        <div class='user-data-add'>
          <span>Company BS:</span>
          <span>{$col["comp_bs"]}</span>
        </div>
      </div>
      ";
  }
  
  return $sortedDataTableBody;
}


// ------------------- MAIN ------------------- //
$host = "127.0.0.1";
$user = getenv("db_user");
$pass = getenv("db_pass");
$dbName = getenv("db_name");
$dbs = new mysqli($host, $user, $pass, $dbName);

if ($dbs->connect_error)
    die("Connection failed: " . $dbs->connect_error);

$disable = false;  // this is for setting the initial DB commands

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

if($disable) {
  // #3 - Create SQL Table
  $sqlTable = "CREATE TABLE users (
    id INT AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    username varchar(255) NOT NULL,
    email varchar(50) NOT NULL,
    street varchar(255) NOT NULL,
    suite varchar(255) NOT NULL,
    city varchar(255) NOT NULL,
    zipcode varchar(20) NOT NULL,
    geo_lat DECIMAL(10, 7) NOT NULL,
    geo_lng DECIMAL(10, 7) NOT NULL,
    phone varchar(25) NOT NULL,
    website TEXT,
    comp_name varchar(255) NOT NULL,
    comp_catchPhrase TEXT,
    comp_bs TEXT,
    PRIMARY KEY(id)
  );";

  if ($dbs->query($sqlTable) === TRUE) echo "success";
  else echo "error: " . $dbs->error;

  // #4 - Insert Records
  insertRecords($dbs, $userData);
}

// #5 - Sort Records by Name
$usersSortedByName = viewRecordsByName($dbs);
$htmlViewByName = loadHTMLStyle($usersSortedByName, 9);


if($disable) {
  // #6 - Update Email
  // The parameters are not needed but it'll make it easier to change ID and email for any other user
  updateEmailAddressOfUser($dbs, 9, "coding@arrowheadcu.org");
}

// #7 - Filter by Longtitude
// Same as #6; make it easier to change longtitude value
$usersByLng = viewUsersByLng($dbs, -110.455);
$htmlViewByLng = loadHTMLStyle($usersByLng);
?>

<!DOCTYPE html>
<html lang="en-US">
  <head>
    <title>ARCU Basic</title>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="index.css">

    <style>
      @import url('https://fonts.googleapis.com/css2?family=Asap:ital,wght@0,100..900;1,100..900&family=Assistant:wght@200..800&family=Inter:wght@100..900&display=swap');
    </style>
  </head>
  <body>
    <div class="row header">
      <div class="header-title">
        <h1 style="font-size:60pt; margin:5px;">ARCU</h1>
        <h3 style="margin-top:0px;">A very red UI for your eyes</h3>
      </div>

      <div class="header-btns" style="margin-bottom:15px;">
        <button id="view-data-btn"><a href="https://jsonplaceholder.typicode.com/users" target="_blank">View Original Data</a></button>
        <button id="reset-db-btn" onclick="alertWarning();">Reset Database</button>
      </div>
    </div>

    <div class="row data-cont view-data-opts">
      <div>To switch between viewing users <b>sorted by name</b> or users with a <b>longtitude greater than -110.455</b>, select the button below to toggle the data.</div>
      <div><button onclick="toggleView(this)">Toggle View</button></div>
    </div>

    <div class="row data-cont user-view" id="default-view">
      <?php echo $htmlViewByName ?>
    </div>

    <!-- Since the data is small, doing this is fine. -->
    <div class="row data-cont user-view" id="lng-view" style="display:none;">
      <?php echo $htmlViewByLng ?>
    </div>
    
    <div class="row footer">
      <div>Made for fun by <b>Jenny</b>. All rights reserved.</div>
      <div><a href="https://github.com/genjen123/ARCU" target="_blank">View Repo</a></div>
    </div>
  </body>
</html>

<script type="text/javascript">
  function alertWarning() {
    alert("Why would you hit this button instead of the actual red button next to it? ఠ _ ఠ");
  }

  function toggleView(ele) {
    let views = document.getElementsByClassName("user-view");
    
    // Since there's only 2, there's no need for a loop
    if(views[0].style.display != "none") {
      views[0].style.display = "none";
      views[1].style.display = "flex";
    }
    else {
      views[0].style.display = "flex";
      views[1].style.display = "none";
    }
  }  
</script>
