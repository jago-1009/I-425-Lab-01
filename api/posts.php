<?php
$url = $_SERVER["REQUEST_URI"];

//checking if slash is first character in route otherwise add it
if (strpos($url, "/") !== 0) {
    $url = "/" . $url;
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
function getAllPosts($db)
{
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
    } else {
        throw new Exception("No posts found", 404);
    }
    return $posts;
}


//create a new post
if ($url == "/posts" && $_SERVER["REQUEST_METHOD"] == "POST") {
    $input = $_POST;

    $postId = addPost($input, $dbConn);

    echo json_encode($postId);
    return;

}


function addPost($input, $db)
{
    $title = $input["title"] ?? null;
    $status = $input["status"] ?? null;
    $content = $input["content"] ?? null;
    $user_id = $input["user_id"] ?? null;
    if (is_null($title) || is_null($status) || is_null($content) || is_null($user_id)) {
        throw new Exception("Error Processing Request: Not enough Parameters", 500);
        die;
    }
    if ($status != "draft" && $status != "published") {
        throw new Exception("Error processing Request: status field must be either 'draft' or 'published'", 500);
        die;
    } else {
        $statement = ("INSERT INTO posts (title, status, content, user_id) VALUES ('$title', '$status', '$content', '$user_id')");

        $db->query($statement);

        $postId = $db->insert_id;

        return array([
            "http_status" => "success",
            "id" => $postId,
            "title" => $title,
            "status" => $status,
            "content" => $content,
            "user_id" => $user_id,
            "URI" => "/posts/$postId"]);

    }


}

//NOTE: Uses exact URL, so that way the post doesn't load whenever comments are ran
if (preg_match("/^\/posts\/([0-9]+)$/", $url, $matches) && $_SERVER["REQUEST_METHOD"] == "GET") {
    $postId = $matches[1];
    $post = getPost($dbConn, $postId);

    echo json_encode($post);
    return;
}


/**
 * Get post based on ID
 *
 * @param $db
 * @param $id
 *
 * @return array
 */

function getPost($db, $id)
{
    $statement = "SELECT * FROM posts WHERE id = '$id'";
    $result = $db->query($statement);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        throw new Exception("Comment not found", 404);
    }
}

//update a post
//Update a post with error handling
if (preg_match("/posts\/([0-9]+)/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PATCH') {
    $input = $_GET;
    $postId = $matches[1];

    try {
        updatePost($input, $dbConn, $postId);
        $post = getPost($dbConn, $postId);

        if ($post) {
            echo json_encode($post);
        } else {
            throw new Exception("Post not found", 404);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

/**
 * Update Post with error handling
 *
 * @param $input
 * @param $db
 * @param $postId
 * @return integer
 */
function updatePost($input, $db, $postId) {
    $fields = getParams($input);

    if (empty($fields)) {
        throw new Exception("No fields to update", 400);
    }

    $statement = "UPDATE posts SET $fields WHERE id = " . $postId;

    // Error handling for the update
    try {
        $result = $db->query($statement);

        if ($db->affected_rows > 0) {
            return $postId;
        } else {
            throw new Exception("Post not found or no fields updated", 404);
        }
    } catch (Exception $e) {
        throw new Exception("Error updating post: " . $e->getMessage(), 500);
    }
}

/**
 * Get fields as parameters to set in record
 *
 * @param $input
 * @return string
 */
function getParams($input) {
    $allowedFields = ['title', 'status', 'content', 'user_id'];
    $filterParams = [];

    foreach ($input as $param => $value) {
        if (in_array($param, $allowedFields)) {
            $filterParams[] = "$param='$value'";
        }
    }

    return implode(", ", $filterParams);
}


//delete a post

if (preg_match('/posts\/(1000|[1-9][0-9]{0,2})/', $url, $matches) && $_SERVER["REQUEST_METHOD"] == "DELETE") {
    $postId = $matches[1];
    try {
        $post = getPost($dbConn, $postId);
        if (count($post) > 3) {
            echo deletePost($dbConn, $postId);
        }
    } catch (Exception $e) {
        throw new Exception("Error: Post not found", 404);
    }
}


/**
 * Delete post based on ID
 *
 * @param $db
 * @param $id
 *
 */
function deletePost($db, $id)
{
    $statement = "DELETE FROM posts WHERE id = " . $id;

    // Error handling for the delete
    try {
        $result = $db->query($statement);
        if ($db->affected_rows > 0) {
            return json_encode([
                'id' => $id,
                'deleted' => 'true'
            ]);
        } else {
            throw new Exception("Post not found", 404);
        }
    } catch (Exception $e) {
        throw new Exception("Error deleting post: " . $e->getMessage(), 500);
    }
}