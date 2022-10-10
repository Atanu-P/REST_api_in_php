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
            $row = $result->ftech_assoc();

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
}
