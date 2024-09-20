<?php
class DB {
    function connect($db) {
        $conn = new mysqli($db['host'], $db['username'], $db['password'],
            $db['database'], $db['port']);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error
                . $conn->connect_error);

        }
        return $conn;
    }
}