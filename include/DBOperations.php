<?php

class DBOperations
{

    private $host = 'localhost';
    private $user = 'root';
    private $db = 'step_api';
    private $pass = 'vuta91929394';

    private $timezone;
    private $conn;

    function randomDate($start_date, $end_date)
    {
        // Convert to timetamps
        $min = strtotime($start_date);
        $max = strtotime($end_date);

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return date('Y-m-d H:i:s', $val);
    }

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db, $this->user, $this->pass);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }


    public function insertData($name, $email, $country, $password, $timezone)
    {

        $unique_id = uniqid('', true);
        $hash = $this->getHash($password);
        $encrypted_password = $hash["encrypted"];
        $salt = $hash["salt"];

        $sql = "INSERT INTO users (unique_id, name, email, country, encrypted_password, salt, timezone) VALUES ( :unique_id, :name, :email, :country, :encrypted_password, :salt, :timezone)";

        $query = $this->conn->prepare($sql);
        $query->execute(array('unique_id' => $unique_id, ':name' => $name, ':email' => $email, ':country' => $country,
            ':encrypted_password' => $encrypted_password, ':salt' => $salt, ':timezone' => $timezone));

        if ($query) {

            return true;

        } else {

            return false;

        }
    }

    public function checkLogin($email, $password)
    {

        $sql = 'SELECT * FROM users WHERE email = :email';
        $query = $this->conn->prepare($sql);
        $query->execute(array(':email' => $email));
        $data = $query->fetchObject();
        $salt = $data->salt;
        $db_encrypted_password = $data->encrypted_password;

        if ($this->verifyHash($password . $salt, $db_encrypted_password)) {

            $user["name"] = $data->name;
            $user["email"] = $data->email;
            $user["unique_id"] = $data->unique_id;
            return $user;

        } else {

            return false;
        }
    }

    public function changePassword($email, $password)
    {

        $hash = $this->getHash($password);
        $encrypted_password = $hash["encrypted"];
        $salt = $hash["salt"];

        $sql = 'UPDATE users SET encrypted_password = :encrypted_password, salt = :salt WHERE email = :email';
        $query = $this->conn->prepare($sql);
        $query->execute(array(':email' => $email, ':encrypted_password' => $encrypted_password, ':salt' => $salt));

        if ($query) {

            return true;

        } else {

            return false;

        }
    }

    public function updateSteps($user_id, $steps)
    {
        if ($this->checkLastUpdateSteps($user_id)) {
            $sql = 'UPDATE steps SET steps = ?, lastupdate= NOW() WHERE user_id = ? AND DATE(lastupdate) = DATE(NOW())';
            $query = $this->conn->prepare($sql);
            $query->execute(array($steps, $user_id));
        } else {
            $sql = 'INSERT INTO steps (user_id, steps, lastupdate) VALUES( ?, ?, NOW())';
            $query = $this->conn->prepare($sql);
            $query->execute(array($user_id, $steps));
        }

        if ($query->rowCount()) {

            return true;

        } else {

            return false;
        }
    }

    public function test ($i){

        $pdo = new PDO('mysql:host=localhost;dbname=step_api', 'root', 'vuta91929394', array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ));



//We start our transaction.
        $pdo->beginTransaction();

//We will need to wrap our queries inside a TRY / CATCH block.
//That way, we can rollback the transaction if a query fails and a PDO exception occurs.
        try{

            for($i=0; $i<100000; $i++) {
                //Query 1: Attempt to insert the payment record into our database.
                $sql = "INSERT INTO steps (user_id, steps, lastupdate) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(rand(1, 6), rand(10, 203004), $this->randomDate('2017-05-20 00:00:00', '2017-06-20 00:00:00')));
            }

            //We've got this far without an exception, so commit the changes.
            $pdo->commit();

            echo "OK 100000";

        }
        catch(Exception $e){
            //An exception has occured, which means that one of our database queries
            //failed.
            //Print out the error message.
            echo $e->getMessage();
            //Rollback the transaction.
            $pdo->rollBack();
        }

    }

    public function getSteps($unique_id)
    {
        try{
            //$sql = "SELECT steps,  convert_tz(lastupdate,'+00:00','".$this->timezone."') as lastupdate, user_id FROM steps WHERE DATE (lastupdate) = DATE( convert_tz(NOW(),'+00:00','".$this->timezone."') ) AND user_id = :unique_id";
            $sql = "SELECT steps,  convert_tz(lastupdate,'+00:00','".$this->timezone."') as lastupdate, user_id FROM steps  ORDER BY lastupdate";
            $query = $this->conn->prepare($sql);
            $query->execute(array(':unique_id' => $unique_id));

            $i=0;
            //$data['steps'] = $query->fetchAll(PDO::FETCH_ASSOC);
            while ($row = $query->fetch(PDO::FETCH_ASSOC))
            {
                $i++;
                //$data[$row['user_id']][] = array('steps' => $row['steps'], 'lastupdate'=>$row['lastupdate']);
                $data[$row['user_id']]['steps'] += $row['steps'];
            }

            $data['message'] = "Succesfull data return: ".$i;
            $data['result'] = "success";
        }
        catch(PDOException $exception){
            $data['steps'] = null;
            $data['message'] = "Query error :: Exception";
            $data['result'] = "failed";
        }

        return $data;
    }

    public function updateUser($unique_id, $height, $weight, $country, $name)
    {
        $bindParam = array(':height' => $height, ':weight' => $weight, ':country' => $country, 'unique_id' => $unique_id);

        $sql = 'UPDATE users SET height = :height, weight= :weight, country = :country ';
        if (!is_null($name)) {
            $sql .= ', name = :name';
            $bindParam[] = array(':name' => $name);
        }
        $sql .= 'WHERE unique_id = :unique_id ';
        $query = $this->conn->prepare($sql);
        $query->execute($bindParam);

        if ($query->rowCount()) {

            return true;

        } else {

            return false;
        }
    }


    public function checkUserExist($email)
    {

        $sql = 'SELECT COUNT(*) from users WHERE email =:email';
        $query = $this->conn->prepare($sql);
        $query->execute(array('email' => $email));

        if ($query) {

            $row_count = $query->fetchColumn();

            if ($row_count == 0) {

                return false;

            } else {

                return true;

            }
        } else {

            return false;
        }
    }

    public function checkLastUpdateSteps($user_id)
    {
        // OLD VERSION OF SELECTS
        //$sql = 'SELECT COUNT(*) from steps WHERE DATE(lastupdate) = DATE(NOW()) AND user_id = :user_id';

        // NEW version of standardizing
        // TODO: CHECK how it's work, keep or change
        $sql = "SELECT COUNT(*) from steps WHERE DATE(lastupdate) = DATE(convert_tz(now(),'+00:00','Europe/Paris')) AND user_id = :user_id";
        $query = $this->conn->prepare($sql);
        $query->execute(array('user_id' => $user_id));

        if ($query) {

            $row_count = $query->fetchColumn();

            if ($row_count == 0) {

                return false;

            } else {

                return true;

            }
        } else {

            return false;
        }
    }

    public function checkUserExistId($unique_id)
    {

        //$sql = 'SELECT COUNT(*) from users WHERE unique_id =:unique_id';
        // Convert to UTC
        // TODO : Check if work and after keep or remove
        // $sql ="select * from steps where DATE(lastupdate) = DATE(convert_tz(now(),'+00:00','Antarctica/Palmer'))";

        $sql = 'SELECT timezone from users WHERE unique_id =:unique_id';
        $query = $this->conn->prepare($sql);
        $query->execute(array('unique_id' => $unique_id));

        if ($query) {

            $row = $query->fetchAll(PDO::FETCH_COLUMN);

            if (sizeof($row) == 0) {

                return false;

            } else {

                $this->timezone = $row[0];
                return true;

            }
        } else {

            return false;
        }
    }

    public function getHash($password)
    {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = password_hash($password . $salt, PASSWORD_DEFAULT);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);

        return $hash;

    }

    public function verifyHash($password, $hash)
    {
        return password_verify($password, $hash);
    }
}

?>