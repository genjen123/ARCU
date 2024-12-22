# ArrowheadCU (PHP + MySQL Stack)

This is a coding challenge given by Arrowhead Credit Union that has been completed and coded in PHP and MySQL.
The answers are listed below, in the order of each task.

> The main file is **htdocs/index.php**. Please view this file if you would like to see the code in action.

## Tasks (Q&A)

#### 1. Fetch User Data: Retrieve user data from the following [URL](https://jsonplaceholder.typicode.com/users).

```php
<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, 'https://jsonplaceholder.typicode.com/users');
$result = curl_exec($ch);
curl_close($ch);

// string $result
// obj    $userData
$userData = json_decode($result);
?>
```

#### 2. Display Records: Display all the user records.

```php
<?php
// For PHP, this will output the JSON in a readable, associative array.
echo "<pre>" . print_r($userData, true) . "</pre>";
?>
```

#### 3. Create SQL Table: Design a SQL table to store the user data.

In general, all of these data can be NOT NULL since it is all filled out, but, 
when we consider user information like Website, Company Catchphrase, and Company BS, it might not be mandatory.  

`PRIMARY KEY` is set to `id` since it should be considered the unique identifier (no duplicates). 
It is also set to `AUTO_INCREMENT` to prevent any counting issues during DB insert.

`varchar(255)` is the standard string type for the table. Any other lower `varchar()` is to provide a limiter.<br>
`varchar(20)` for **zipcode** is a buffer for international zip codes. The USA has mostly numeric digits but other countries may be alphanumeric.<br>
`TEXT` is to handle potentially long and random string formats.<br>
`DECIMAL(10, 7)` is to handle **latitude** and **longitude**. This makes it easier for data handling rather than using a string.<br>
Based on research, the 7 decimal digits is Google's standard. 8 is rare so I think 7 is a good spot.<br>

```mysql
CREATE TABLE users (
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
);
```

#### 4. Insert Records: Write a function to insert the user data into the SQL table.

`$db` -> DB object connection.<br>
`$data` -> Decoded JSON object from the given URL.<br>

```php
<?php
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
?>
```

#### 5. Sort Records: Write a SQL query to sort the records by name.

```mysql
SELECT id, name, username, email, street, suite, city, zipcode, 
       geo_lat, geo_lng, phone, website, comp_name, 
       comp_catchPhrase, comp_bs
FROM users
ORDER BY name ASC;
```

#### 6. Update Email: Update the email address for the user with ID 9 to coding@arrowheadcu.org.

> The extra parameters aren't necessary for the task. It is added in to make it easier to change any user's email in the code (just in case).

`$db` -> DB object connection.<br>
`$userId` -> (int) The user ID.<br>
`$userEmail` -> (string) The email to change to.<br>

```php
<?php
function updateEmailAddressOfUser($db, $userId, $userEmail) {
  $sql = "UPDATE users SET email = ? WHERE id = ?";

  $dbc = $db->prepare($sql);
  $dbc->bind_param("si", $userEmail, $userId);
  $dbc->execute();
  $dbc->close();
}
?>
```

#### 7. Filter by Longitude: Write a SQL query to retrieve all users with a longitude greater than -110.455.

In the code, the value for longtitude is replaced by `?` to be used with prepared statements.

```mysql
SELECT id, name, username, email, geo_lng 
FROM users
WHERE geo_lng > -110.455
ORDER BY geo_lng ASC
```

## Note

* All executable files are placed in the `htdocs` folder. This is the area where the PHP server looks for files to execute.
* Adminer (adminer.php + adminer.css) is a web interface for managing the database. It is automatically downloaded with the template.

## Credits
[PHP + MySQL Template for Replit](https://replit.com/@huntergj/PHP-MySQL#README.md) - by huntergj
