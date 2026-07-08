<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class ExternalApi extends Controller
{
    public function fetchUser($userId)
    {
        $client = \Config\Services::curlrequest();

        // Replace this URL with the actual API URL from the website
        $url = "https://jsonplaceholder.typicode.com/users/{$userId}";

        try {
            $response = $client->get($url);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                echo '<pre>';
                print_r($data);
                echo '</pre>';
            } else {
                echo "API returned status code: " . $response->getStatusCode();
            }
        } catch (\Exception $e) {
            echo "Error fetching API: " . $e->getMessage();
        }
    }
}