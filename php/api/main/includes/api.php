<?php



//Validate client credentials
function api_client_validate($client_id, $client_password)
{
    $client_id = clean_data($client_id);
    $client_password = clean_data($client_password);

    $validate_result = validate_client_id_and_pass($client_id, $client_password);

    switch ($validate_result['response_code']) {
        case 1://success
            return response(["status" => true, "type" => "auth", "subType" => "getToken", "response_code" => 1.0, "client_token" => api_token_generate($client_id)]);
            break;

        case 1.1:////username_password_no_match
            action_log($client_id, 'auth_failure_api_client_validate_client_mismatch_password');
            return response(["status" => false, "type" => "auth", "subType" => "getToken", "response_code" => 0.0]);
            break;

        default://client_not_found
            action_log('unknown', 'auth_failure_api_client_validate_client_unknown');
            return response(["status" => false, "type" => "auth", "subType" => "getToken", "response_code" => 0.1]);
            break;
    }
}


//Check if token is valid
function api_client_valid_check($client_id, $client_token)
{
    $client_id = clean_data($client_id);
    $client_token = clean_data($client_token);
    $required_api_level = clean_data($required_api_level);

    //check client exists
    $query = "SELECT id FROM clients WHERE client_id='{$client_id}'";
    $result = sql_query('api_db', $query, false);

    if ($result->num_rows == 1) {
        $result_assoc = $result->fetch_assoc();
    } else {
        action_log($client_id, 'auth_failure_api_token_validate_client_unknown');
        return response(["status" => false, "type" => "auth", "subType" => "validateToken", "response_code" => 0.0]);
    }

    //check token valid
    $query = "SELECT id FROM tokens WHERE client_id='{$client_id}' AND client_token='{$client_token}'";
    $result = sql_query('api_db', $query, false);

    if ($result->num_rows != 1) {
        action_log($client_id, 'auth_failure_api_token_validate_id_mismatch_token');
        return response(["status" => false, "type" => "auth", "subType" => "validateToken", "response_code" => 1.0]);
    }

    //check client access level
    api_validate_level($client_id, $required_api_level);

    //delete old token
    api_token_delete($client_token);

    //return true
    return ["status" => true, "type" => "auth", "subType" => "validateToken", "response_code" => 3.0];
}

//Generate api access token
function api_token_generate($client_id)
{
    $client_token = gen(256);
    $query = "INSERT INTO tokens (client_id,client_token) VALUES ('{$client_id}','{$client_token}')";
    sql_query('api_db', $query, false);
    action_log($client_id, 'auth_success_api_token_generate');
    return $client_token;
}


//Delete used api access token //REMOVE
function api_token_delete($client_token)
{
    $query = "DELETE FROM tokens WHERE client_token='{$client_token}'";
    sql_query('api_db', $query, false);
    action_log($client_id, 'auth_success_api_token_delete');
}


//Validate api access token and access level REMOVE
function api_token_validate($client_id, $client_token, $required_api_level)
{
    $client_id = clean_data($client_id);
    $client_token = clean_data($client_token);
    $required_api_level = clean_data($required_api_level);

    //check client exists
    $query = "SELECT id FROM clients WHERE client_id='{$client_id}'";
    $result = sql_query('api_db', $query, false);

    if ($result->num_rows == 1) {
        $result_assoc = $result->fetch_assoc();
    } else {
        action_log($client_id, 'auth_failure_api_token_validate_client_unknown');
        return response(["status" => false, "type" => "auth", "subType" => "validateToken", "response_code" => 0.0]);
    }

    //check token valid
    $query = "SELECT id FROM tokens WHERE client_id='{$client_id}' AND client_token='{$client_token}'";
    $result = sql_query('api_db', $query, false);

    if ($result->num_rows != 1) {
        action_log($client_id, 'auth_failure_api_token_validate_id_mismatch_token');
        return response(["status" => false, "type" => "auth", "subType" => "validateToken", "response_code" => 1.0]);
    }

    //check client access level
    api_validate_level($client_id, $required_api_level);

    //delete old token
    api_token_delete($client_token);

    //return true
    return ["status" => true, "type" => "auth", "subType" => "validateToken", "response_code" => 3.0];
}


//validate client and api_level TODO
function api_validate_level($client_id, $required_api_level)
{
    $query = "SELECT client_level FROM clients WHERE client_id='{$client_id}'";
    $result = sql_query('api_db', $query, false);

    if ($result->num_rows == 1) {
        $result_assoc = $result->fetch_assoc();
        if ($required_api_level <= $result_assoc['client_level']) {
            action_log($client_id, 'auth_success_token_client_level');
            return ["response_code" => 1.0];
        } else {
            action_log($client_id, 'auth_failure_api_validate_level_client_level_too_low');
            return ["respone_code" => 1.1];
        }
    } else {
        action_log($client_id, 'auth_failure_api_validate_level_client_unknown');
        return ["respone_code" => 0.0];
    }
}


//Make api calls for GET, PUT, POST
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
