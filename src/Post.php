<?php

namespace Src;

use \Exception;

class Post
{
    private $conn;
    private $request_method;
    private $post_id;

    public function __construct($conn, $request_method, $post_id)
    {
        $this->conn = $conn;
        $this->request_method = $request_method;
        $this->post_id = $post_id;
    }

    public function find($id)
    {
        $sql = "SELECT * FROM post WHERE id = '$id'";

        try {
            $result = $this->conn->query($sql);
            if (!$result) {
                throw new Exception("Error : " . $result->error);
            }
            $row = $result->fetch_assoc();

            return $row;
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    private function validate_post($input)
    {
        if (!isset($input['title'])) {
            return false;
        }

        if (!isset($input['body'])) {
            return false;
        }

        return true;
    }

    private function unprocessable_entity_response()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode(['error' => 'Invalid input']);
        return $response;
    }

    private function not_found_response()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

    private function get_all_posts()
    {
        $sql = "SELECT * FROM post";

        try {
            // $statement = $this->db->query($query);
            $result = $this->conn->query($sql);
            // $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (!$result) {
                throw new Exception("Error : " . $result->error);
            }
            $row = $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($row);
        return $response;
    }

    private function get_post($id)
    {
        $result = $this->find($id);
        if (!$result) {
            return $this->not_found_response();
        }

        $rseponse['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function create_post()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validate_post($input)) {
            return $this->unprocessable_entity_response();
        }
        $title = $input['title'];
        $body = $input['body'];
        $author = $input['author'];
        $author_picture = 'https://secure.gravatar.com/avatar/' . md5(strtolower($input['author'])) . '.png?s=200';

        $sql = "INSERT INTO post 
                VALUES(
                    '$title', 
                    '$body', 
                    '$author', 
                    '$author_picture'
                )";

        try {
            $result = $this->conn->query($sql);

            if (!$result) {
                throw new Exception("Error : " . $result->error);
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['message' => 'Post Created']);
        return $response;
    }

    private function update_post($id)
    {
        $result = $this->find($id);
        if (!$result) {
            return $this->not_found_response();
        }

        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validate_post($input)) {
            return $this->unprocessable_entity_response();
        }

        $title = $input['title'];
        $body = $input['body'];
        $author = $input['author'];
        $author_picture = 'https://secure.gravatar.com/avatar/' . md5(strtolower($input['author'])) . '.png?s=200';

        $sql = "UPDATE post 
                SET 
                    title='$title', 
                    body='$body', 
                    uthor='$author', 
                    author_picture='$author_picture' 
                WHERE id = '$id'";

        try {
            $result = $this->conn->query($sql);

            if (!$result) {
                throw new Exception("Error : " . $result->error);
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Post Updated!'));
        return $response;
    }

    private function delete_post($id)
    {
        $result = $this->find($id);
        if (!$result) {
            return $this->not_found_response();
        }

        $sql = "DELETE FROM post WHERE id='$id'";

        try {
            $result = $this->conn->query($sql);

            if (!$result) {
                throw new Exception("Error : " . $result->error);
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Post Deleted!'));
        return $response;
    }

    public function process_request()
    {
        switch ($this->request_method) {
            case 'GET':
                if ($this->post_id) {
                    $response = $this->get_post($this->post_id);
                } else {
                    $response = $this->get_all_posts();
                }
                break;

            case 'POST':
                $response = $this->create_post();
                break;

            case 'PUT':
                $response = $this->update_post($this->post_id);
                break;

            case 'DELETE':
                $response = $this->delete_post($this->post_id);
                break;
            default:
                $response = $this->not_found_response();
                break;
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }
}
