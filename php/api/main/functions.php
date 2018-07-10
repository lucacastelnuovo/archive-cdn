<?php

$config = parse_ini_file('../config/authentication.ini');

//Connect to database
function sql_connect($database = null)
{
    $config = parse_ini_file('../config/authentication.ini');

    $database = isset($database) ? $database : $config['database'];

    $conn = new mysqli($config['host'], $config['username'], $config['password'], $database);

    if ($conn->connect_error) {
        exit();
    } else {
        return $conn;
    }
}

//Close sql connection to save resources
function sql_disconnect($conn)
{
    mysqli_close($conn);
}


//Execute sql query's and have to possibility to return an associative array
function sql_query($database, $query, $assoc = true)
{
    $conn = sql_connect($database);

    $result = $conn->query(escape_string($query));

    sql_disconnect($conn);

    if ($assoc) {
        return $result->fetch_assoc();
    } else {
        return $result;
    }
}


//Clean user submitted data
function clean_data($data, $disable = 'none')
{
    if ($disable != 'sql') {
        //connect to db to use escape_string()
        $conn = sql_connect();
        $data = $conn->escape_string($data);
        sql_disconnect($conn);
    }

    if ($disable != 'trim') {
        $data = trim($data);
    }

    if ($disable != 'html') {
        $data = htmlspecialchars($data);
    }

    if ($disable != 'slash') {
        $data = stripslashes($data);
    }

    return $data;
}

//encode array to json
function response($output)
{
    return json_encode($output);
}

function api_call($method, $url, $data = false)
{
    $curl = curl_init();
    switch ($method) {
    case "POST":
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        break;

    case "PUT":
        curl_setopt($curl, CURLOPT_PUT, 1);
        break;

    default:
        if ($data) {
            $url = sprintf("%s?%s", $url, http_build_query($data));
        }
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result, true);
}