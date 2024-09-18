<?php
$url = $_SERVER["REQUEST_URI"];

//checking if slash is first character in route otherwise add it
if(strpos($url,"/") !== 0) {
    $url = "/".$url;
}

//connect to database
$dbInstance = new DB();
$dbConn = $dbInstance->connect($db);

header("Content-type: application/json");

//list all posts
if ($url == "/posts" && $_SERVER["REQUEST_METHOD"] == "GET") {
    $posts = getAllPosts($dbConn);
    echo json_encode($posts);
}
function getAllPosts($db) {
    $statement = "SELECT * FROM posts";
    $result = $db->query($statement);
    if ($result && $result->num_rows > 0) {
        $posts = array();
        while ($result_row = $result->fetch_assoc()) {
            $post = array('id' => $result_row['id'],
                'title' => $result_row['title'],
                'status' => $result_row['status'],
                'content' => $result_row['content'],
                'user_id' => $result_row['user_id']);
            $posts[] = $post;
        }
    }
    return $posts;
}


//create a new post
 if ($url == "/posts" && $_SERVER["REQUEST_METHOD"] == "POST") {
     $input = $_POST;

     $postId = addPost($input, $dbConn);
     if ($postId) {
         $input['id'] = $postId;
         $input['link'] = "/posts/".$postId;
     }
     echo json_encode($input);
 }

/**
 * Add post
 *
 * @param $input
 * @param $db
 * @return integer
 */

function AddPost ($input, $db) {
    $title = $input["title"];
    $status = $input["status"];
    $content = $input["content"];
    $users_id = $input["user_id"];

    $statement = "INSERT INTO posts (title, status, content, user_id) VALUES ('$title', '$status', '$content', '$users_id')";

    $db->query($statement);

    return $db->insert_id;
}


if (preg_match("/posts\/([0-9])+/", $url, $matches) && $_SERVER["REQUEST_METHOD"] == "GET")  {
    $postId = $matches[1];
    $post = getPost($dbConn, $postId);

    echo json_encode($post);
}


/**
 * Get post based on ID
 *
 * @param $db
 * @param $id
 *
 * @return Associative Array
 */

function getPost($db, $id) {
    $statement = "SELECT * FROM posts WHERE id = '$id'";
    $result = $db->query($statement);
    $result_row = $result->fetch_assoc();

    return $result_row;
}

//update a post

if(preg_match("/posts\/([0-9])+/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'PATCH'){
    $input = $_GET;
    $postId = $matches[1];
    echo $url;
    print_r($matches);
    updatePost($input, $dbConn, $postId);
    $post = getPost($dbConn, $postId);
    echo json_encode($post);
}


/**
 * Update Post
 *
 * @param $input
 * @param $db
 * @param $postId
 * @return Integer
 */
/**
 *
 * Get Fields as parameters to set in record
 *
 * @param $input
 * @return string
 */

function getParams($input) {
    $allowedFields = ['title', 'status', 'content', 'user_id'];
    $filterParams = [];
    foreach($input as $param => $value){
        if(in_array($param, $allowedFields)){
            $filterParams[] = "$param='$value'";
        }
    }
    return implode(", ", $filterParams);
}
function updatePost($input, $db, $postId) {
    $fields = getParams($input);

    $statement = "UPDATE posts SET $fields WHERE id = " . $postId;
    echo $statement;
    $db->query($statement);
    return $postId;

}

//delete a post

if(preg_match('/posts\/([0-9])+/', $url, $matches) && $_SERVER["REQUEST_METHOD"] == "DELETE")  {
    $postId = $matches[1];

    deletePost($dbConn, $postId);

    echo json_encode([
        'id' => $postId,
        'deleted'=>'true'
    ]);

}


/**
 * Delete post based on ID
 *
 * @param $db
 * @param $id
 *
 */
function deletePost ($db, $id) {
    $statement = "DELETE FROM posts WHERE id = " . $id;
    $db->query($statement);
}