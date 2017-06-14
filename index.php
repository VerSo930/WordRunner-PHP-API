<?php
error_reporting( E_ALL );
date_default_timezone_set('UTC');
require_once 'include/functions.php';
require_once 'include/dumprequest.php';

$t = new DumpHTTPRequestToFile();
$t->execute('text.txt');

file_put_contents('test.txt', file_get_contents('php://input'));

$fun = new Functions();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->operation)) {

        $operation = $data->operation;

        if (!empty($operation)) {

            if ($operation == 'register') {

                if (isset($data->user) && !empty($data->user) && isset($data->user->name)
                    && isset($data->user->email) && isset($data->user->password)
                ) {

                    $user = $data->user;
                    $name = $user->name;
                    $email = $user->email;
                    $country = $user->country;

                    if (is_null($country)) {
                        $country = 0;
                    }
                    $password = $user->password;
                    $timezone = $user->timezone;

                    if ($fun->isEmailValid($email)) {

                        echo $fun->registerUser($name, $email, $country, $password, $timezone);

                    } else {

                        echo $fun->getMsgInvalidEmail();
                    }

                } else {

                    echo $fun->getMsgInvalidParam();

                }

            } else if ($operation == 'updsteps') {

                $fun->checkParams($data);
                $fun->updateSteps($data);


            } else if ($operation == 'upduser') {

                if (isset($data->user) && !empty($data->user) && isset($data->user->unique_id)) {

                    $user = $data->user;
                    $unique_id = $user->unique_id;
                    $height = $user->height;
                    $weight = $user->weight;
                    $country = $user->country;
                    $name = $user->name;

                    if (!isset($country)) {
                        $country = 1;
                    }
                    if (!isset($height)) {
                        $height = null;
                    }
                    if (!isset($weight)) {
                        $weight = null;
                    }
                    if (!isset($name)) {
                        $name = null;
                    }

                    echo $fun->updateUser($unique_id, $height, $weight, $country, $name);

                } else {

                    echo $fun->getMsgInvalidParam();

                }
            } else if ($operation == 'login') {

                if (isset($data->user) && !empty($data->user) && isset($data->user->email) && isset($data->user->password)) {

                    $user = $data->user;
                    $email = $user->email;
                    $password = $user->password;

                    echo $fun->loginUser($email, $password);

                } else {

                    echo $fun->getMsgInvalidParam();

                }
            } else if ($operation == 'chgPass') {

                if (isset($data->user) && !empty($data->user) && isset($data->user->email) && isset($data->user->old_password)
                    && isset($data->user->new_password)
                ) {

                    $user = $data->user;
                    $email = $user->email;
                    $old_password = $user->old_password;
                    $new_password = $user->new_password;

                    echo $fun->changePassword($email, $old_password, $new_password);

                } else {

                    echo $fun->getMsgInvalidParam();

                }
            }else if ($operation == 'test') {
                $i=100;
              $fun->test($i);

            }
            else if ($operation == 'getsteps') {

                    $fun->checkParams($data);
                    $fun->getSteps($data);

            }

        } else {

            echo $fun->getMsgParamNotEmpty();

        }
    } else {

        echo $fun->getMsgInvalidParam();

    }


}

?>