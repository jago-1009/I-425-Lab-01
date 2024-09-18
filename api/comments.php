<?php
$url = $_SERVER["REQUEST_URI"];

if (strpos($url, "/") !== 0) {
    $url = "/" . $url;
}

//connect to database
$dbInstance = new DB();
$dbConn = $dbInstance->connect($db);



header("Content-type: application/json");
if (preg_match("/posts\/([0-9]+)\/comments/", $url, $matches) && $_SERVER["REQUEST_METHOD"] == "GET") {
    $postId = $matches[1];
    $comments = getAllComments($dbConn, $postId);
    echo json_encode($comments);
}
function getAllComments($db, $postId) {

    $statement = "SELECT * FROM comments WHERE post_id = $postId";
    $result = $db->query($statement);
    $comments = array();
    if ($result && $result->num_rows > 0) {

        while ($result_row = $result->fetch_assoc()) {
            $comment = array('id' => $result_row['id'],
                'comment' => $result_row['comment'],
                'user_id' => $result_row['user_id']);
            $comments[] = $comment;
        }
    }
    return $comments;
}

?>