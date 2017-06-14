<?php
require_once 'include/DBOperations.php';

class Functions
{

    private $db;

    public function __construct()
    {
        $this->db = new DBOperations();

    }

    public function registerUser($name, $email, $country, $password, $timezone)
    {

        $db = $this->db;

        if (!empty($name) && !empty($email) && !empty($password)) {

            if ($db->checkUserExist($email)) {

                $response["result"] = "failure";
                $response["message"] = "User Already Registered !";
                return json_encode($response);

            } else {

                $result = $db->insertData($name, $email, $country, $password, $timezone);

                if ($result) {

                    $response["result"] = "success";
                    $response["message"] = "User Registered Successfully !";
                    return json_encode($response);

                } else {

                    $response["result"] = "failure";
                    $response["message"] = "Registration Failure";
                    return json_encode($response);

                }
            }
        } else {

            return $this->getMsgParamNotEmpty();

        }
    }
    public function test ($i){

        $db = $this->db;
        $db->test($i);

    }

    public function loginUser($email, $password)
    {

        $db = $this->db;

        if (!empty($email) && !empty($password)) {

            if ($db->checkUserExist($email)) {

                $result = $db->checkLogin($email, $password);

                if (!$result) {

                    $response["result"] = "failure";
                    $response["message"] = "Invaild Login Credentials";
                    return json_encode($response);

                } else {

                    $response["result"] = "success";
                    $response["message"] = "Login Sucessful";
                    $response["user"] = $result;
                    return json_encode($response);

                }
            } else {

                $response["result"] = "failure";
                $response["message"] = "Invaild Login Credentials";
                return json_encode($response);

            }
        } else {

            return $this->getMsgParamNotEmpty();
        }
    }

    public function updateSteps($data)
    {

        $db = $this->db;
        if (!empty($unique_id) && !empty($steps)) {

            if (!$db->checkUserExistId($unique_id)) {

                return $this->getMsgInvalidId();

            } else {

                $result = $db->updateSteps($unique_id, $steps);


                if ($result) {

                    $response["result"] = "success";
                    $response["message"] = "Steps updated Successfully";
                    return json_encode($response);

                } else {

                    $response["result"] = "failure";
                    $response["message"] = "Error Updating Steps";
                    return json_encode($response);

                }
            }
        } else {

            return $this->getMsgParamNotEmpty();
        }
    }

    public function getSteps($data)
    {


        $db = $this->db;
        if (!empty($data->user->unique_id)) {

            if (!$db->checkUserExistId($data->user->unique_id)) {

                return $this->getMsgInvalidId();

            } else {

                return json_encode($db->getSteps($unique_id));

            }
        } else {

            return $this->getMsgParamNotEmpty();
        }
    }

    public function updateUser($unique_id, $height, $weight, $country, $name)
    {

        $db = $this->db;
        if (!empty($unique_id)) {

            if (!$db->checkUserExistId($unique_id)) {

                return $this->getMsgInvalidId();

            } else {

                $result = $db->updateUser($unique_id, $height, $weight, $country, $name);


                if ($result) {

                    $response["result"] = "success";
                    $response["message"] = "User data updated Successfully";
                    return json_encode($response);

                } else {

                    $response["result"] = "failure";
                    $response["message"] = "Error Updating User data";
                    return json_encode($response);

                }
            }
        } else {

            return $this->getMsgParamNotEmpty();
        }
    }

    public function changePassword($email, $old_password, $new_password)
    {

        $db = $this->db;

        if (!empty($email) && !empty($old_password) && !empty($new_password)) {

            if (!$db->checkLogin($email, $old_password)) {

                $response["result"] = "failure";
                $response["message"] = 'Invalid Old Password';
                return json_encode($response);

            } else {

                $result = $db->changePassword($email, $new_password);

                if ($result) {

                    $response["result"] = "success";
                    $response["message"] = "Password Changed Successfully";
                    return json_encode($response);

                } else {

                    $response["result"] = "failure";
                    $response["message"] = 'Error Updating Password';
                    return json_encode($response);

                }
            }
        } else {

            return $this->getMsgParamNotEmpty();
        }
    }

    public function isEmailValid($email)
    {

        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function isUserValid($email)
    {

        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function getMsgParamNotEmpty()
    {

        $response["result"] = "failure";
        $response["message"] = "Parameters should not be empty !";
        echo json_encode($response);
        http_response_code(400);
        die ();

    }

    public function getMsgInvalidParam()
    {

        $response["result"] = "failure";
        $response["message"] = "Invalid Parameters";
        echo json_encode($response);
        http_response_code(400);
        die ();

    }

    public function getMsgInvalidEmail()
    {

        $response["result"] = "failure";
        $response["message"] = "Invalid Email";
        echo json_encode($response);
        http_response_code(400);
        die ();

    }

    public function getMsgInvalidId()
    {

        $response["result"] = "failure";
        $response["message"] = "Invalid User ID";
        echo json_encode($response);
        http_response_code(400);
        die();

    }

    public function getHeaderList()
    {
        $headerList = [];
        foreach ($_SERVER as $name => $value) {
            if (preg_match('/^HTTP_/', $name)) {

                $name = strtr(substr($name, 5), '_', ' ');
                $name = ucwords(strtolower($name));
                $name = strtr($name, ' ', '-');

                $headerList[$name] = $value;
            }
        }
        return $headerList;
    }

    public function checkParams($data) {
        if (!isset($data->user) && empty($data->user) && !isset($data->user->unique_id)) {
            getMsgInvalidParam();
        }

    }
    public function response($bool, $message) {
        if($bool) {
            $response["result"] = "success";
            $response["message"] = $message;
            http_response_code(200);
            return json_encode($response);
        } else {
            $response["result"] = "failed";
            $response["message"] = $message;
            http_response_code(400);
            return json_encode($response);
        }
    }

}