<?php
$url = $_SERVER["REQUEST_URI"];

if (strpos($url, "/") !== 0) {
    $url = "/" . $url;
}


//connect to database
$dbInstance = new DB();
$dbConn = $dbInstance->connect($db);


header("Content-type: application/json");






//Gets all comments for a certain post ID
if (preg_match("/posts\/([0-9]+)\/comments/", $url, $matches) && $_SERVER["REQUEST_METHOD"] == "GET") {
    $postId = $matches[1];
    $comments = getAllCommentsForPost($dbConn, $postId);

        echo json_encode($comments);
    return;
}
//getAllCommentsforPost function: gets all comments relating to a certain post.
function getAllCommentsForPost($db, $postId)
{

    $statement = "SELECT id,comment,user_id FROM comments WHERE post_id = $postId";
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
    else {
        throw new Exception("No Comments Found",404);
    }
    return $comments;
}

//Posts comments for a certain post ID
if (preg_match("/posts\/([0-9]+)\/comments/", $url, $matches) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $postId = $matches[1];
    $input = $_POST;
    $postComments = postComments($input,$dbConn,$postId);
    echo json_encode($postComments);
    return;
}

//postComments function; Grabs inputs from $_POST superglobal variable and creates SQL query inserting comments into table
function postComments($input, $db, $postId)
{
    $userId = $input["user_id"] ?? null;
    $comment = $input["comment"] ?? null;
    if (is_null($userId) || is_null($comment)) {
        throw new Exception("Error Processing Request: Not enough Parameters", 500);
        die;
    }
    else {
    $statement = "INSERT INTO comments (user_id,comment,post_id) VALUES ('$userId', '$comment', '$postId')";
    $db->query($statement);
    $commentId = $db->insert_id;



    return array([
        "status"=>"success",
        "comment_id"=>$commentId,
        "user_id"=>$userId,
        "endpoint"=>"/comments/$commentId"
    ]);
    }
}


//gets all comments
if ($url == "/comments" && $_SERVER["REQUEST_METHOD"] == "GET") {
    $comments = getAllComments($dbConn);
    echo json_encode($comments);
}

//getAllComments Function: Gets all comments in the database.

function getAllComments($db)
{
    $statement = "SELECT * FROM comments";
    $result = $db->query($statement);
    if ($result && $result->num_rows > 0) {
        $comments = array();
        while ($result_row = $result->fetch_assoc()) {
            $comment = array('id' => $result_row['id'],
                'comment' => $result_row['comment'],
                'post_id' => $result_row['post_id'],
                'user_id' => $result_row['user_id']);
            $comments[] = $comment;
        }
    }
    else {
        throw new Exception("No Comments Found", 404);
    }

    return $comments;
}


//gets a specific comment from the database
if (preg_match("/comments\/([0-9]+)/", $url, $matches) && $_SERVER["REQUEST_METHOD"] == "GET") {
    $commentId = $matches[1];

    $comments = getComment($dbConn, $commentId);

    echo json_encode($comments);
    return;
}

//getComment function: retrieves the comment with the specific ID

function getComment($db, $id) {
    $statement = "SELECT * FROM comments WHERE id = '$id'";
    $result = $db->query($statement);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        throw new Exception("Comment not found", 404);
    }


}




//ERROR HANDLING
if (preg_match("/comments\/([0-9]+)/", $url, $matches) && $_SERVER["REQUEST_METHOD"] == "POST") {
    throw new Exception("Invalid Method", 405);
}
?>