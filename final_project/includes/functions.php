<?php
define('SALT', 'a_very_random_salt_for_this_app');
define('FILE_SIZE_LIMIT', 4000000);

define('DB_HOST',     '127.0.0.1');
define('DB_PORT',     '8111');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'comp3015');

function connect()
{
    $link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    if (!$link) {
        echo mysqli_connect_error();
        exit;
    }

    return $link;
}

/**
 * Look up the email & password pair from the database.
 *
 * Passwords are simple md5 hashed, but salted.
 *
 * Remember, md5() is just for demonstration purposes.
 * Do not do this in production for passwords.
 *
 * @param $email string The email to look up
 * @param $pass string The password to look up
 * @return bool true if found, false if not
 */
function findUser($email, $pass)
{
    $found = false;

    $link = connect();
    $hash = md5($pass . SALT);

    $query   = 'select * from users where email = "' . $email . '" and password = "' . $hash . '"';
    $results = mysqli_query($link, $query);

    if (mysqli_fetch_array($results)) {
        $found = true;
    }

    mysqli_close($link);

    return $found;
}

/**
 * Remember, md5() is just for demonstration purposes.
 * Do not do this in production for passwords.
 *
 * @param $data
 * @return bool
 */
function saveUser($data)
{
    $firstname   = trim($data['firstname']);
    $lastname   = trim($data['lastname']);
    $email   = trim($data['email']);
    $password   = md5($data['password'] . SALT);

    $link    = connect();
    $query   = 'insert into users(firstname, lastname, email, password) values("' . $firstname . '","' . $lastname . '","' . $email . '","' . $password . '")';
    $success = mysqli_query($link, $query); // returns true on insert statements

    mysqli_close($link);
    return $success;
}


/**
 * @param $data
 * @return bool
 */
function checkSignUp($data)
{
    $valid = true;

    // if any of the fields are missing
    if (
        trim($data['firstname']) == '' || trim($data['lastname'])        == '' ||
        trim($data['email']) == '' || trim($data['password'])        == '' ||
        trim($data['verify_password']) == ''
    ) {
        $valid = false;
    } elseif (!preg_match("/^([a-zA-Z' ]+)$/", (trim($data['firstname'])))) {
        $valid = false;
    } elseif (!preg_match("/^([a-zA-Z' ]+)$/", (trim($data['lastname'])))) {
        $valid = false;
    } elseif (!preg_match("/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/", (trim($data['email'])))) {
        $valid = false;
    } elseif (!preg_match('/((?=.*[a-z])(?=.*[0-9])(?=.*[!?|@])){8}/', trim($data['password']))) {
        $valid = false;
    } elseif ($data['password'] != $data['verify_password']) {
        $valid = false;
    }

    return $valid;
}

function filterEmail($email)
{
    return preg_replace("/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/", '', $email);
}

/**
 * @param $file
 * @return bool
 */
function checkPost($file)
{
    if ($file['picture']['size'] < FILE_SIZE_LIMIT && $file['picture']['type'] == 'image/jpeg') {
        return true;
    }

    return 'not able to upload picture';
}

function checkProduct($data)
{
    $valid = true;

    // if any of the fields are missing
    if (
        trim($data['title']) == '' || trim($data['price'])        == '' ||
        trim($data['desc']) == ''
    ) {
        $valid = false;
    } elseif (!preg_match("/^([a-zA-Z' ]+)$/", (trim($data['title'])))) {
        $valid = false;
    } elseif (!preg_match("/^\d+(?:\.\d{2})?$/", (trim($data['price'])))) {
        $valid = false;
    } elseif (!preg_match("/[^\w,.]/", (trim($data['desc'])))) {
        $valid = false;
    }

    return $valid;
}

/**
 * @param $username
 * @param $file
 * @return bool
 */
function saveProduct($data, $email)
{
    $picture = md5($email . time());
    $file = $_FILES['picture'];
    $moved   = move_uploaded_file($file['tmp_name'], 'products/' . $picture);

    if ($moved) {
        $link   = connect();
        $query  = 'insert into products(email, title, price, description, picture) values("' . $email . '","' . $data['title'] . '","' . $data['price'] . '","' . $data['desc'] . '","' . $picture . '")';
        $result = mysqli_query($link, $query);

        mysqli_close($link);
        return $result;
    }

    return false;
}

/**
 * @return bool|mysqli_result
 */
function getAllProducts()
{
    $link     = connect();
    $query    = 'select * from products';
    $products = mysqli_query($link, $query);

    mysqli_close($link);
    return $products;
}

/**
 * Delete a profile based on the ID and username combination
 *
 * @param $id
 * @param $username
 * @return bool returns true on deletion success or false on failure
 */
function deleteProduct($id)
{
    $link    = connect();
    $query   = 'delete from products where id = "' . $id . '" ';
    $success = mysqli_query($link, $query);
    mysqli_close($link);
    return $success;
}

function getUsername($email)
{
    $link    = connect();
    $query   = 'select * from users where email = "' . $email . '" ';
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);
    $username = $row['firstname'] . ' ' . $row['lastname'];

    return $username;
    mysqli_close($link);
}

function getProduct($id)
{
    $link    = connect();
    $query   = 'select * from products where id = "' . $id . '" ';
    $success = mysqli_query($link, $query);

    mysqli_close($link);
    return $success;
}

function searchProduct($word)
{
    $link    = connect();
    // $query   = 'select * from products where title like %' . $word . '% or description like %' . $word . '%';
    $query   = 'select * from products';
    $result = mysqli_query($link, $query);

    // $keyArray = array();

    while ($row = mysqli_fetch_array($result)) {

        foreach ($row as $index => $string) {
            if (strpos($string, $word) !== FALSE) {
                // $keyArray[] = $row['id'] . ',';
                // echo $keyArray;
                return $row['id'];
            }
        }
        // if (empty($keyArray)) {
        //     return false;
        // }
        // if (count($keyArray) == 1) {
        //     return $keyArray[0];
        // } else {
        // return $keyArray;
        // }
    }
    mysqli_close($link);
}

function saveUserInFile($data)
{

    $success = false;

    $fp = fopen('admin.ini', 'a+');

    if ($fp != false) {
        $username   = trim($data['username']);
        $password   = trim($data['password']);
        $results = fwrite($fp, $username . ',' . $password . PHP_EOL);

        fclose($fp);

        if ($results) {
            $success = true;
        }
    }

    return $success;
}


function updateProfile($username, $file)
{
    $picture = md5($username . time());
    $moved   = move_uploaded_file($file['picture']['tmp_name'], 'profiles/' . $picture);

    if ($moved) {
        $link   = connect();
        $query  = 'update profiles set picture =  "' . $picture . '" where username = "' . $username . '"';
        $result = mysqli_query($link, $query);

        mysqli_close($link);
        return $result;
    }

    return false;
}
