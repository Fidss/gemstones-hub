<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class SupabaseService
{
    protected $client;
    protected $baseUrl;
    protected $headers;

    public function __construct()
    {
        $this->baseUrl = Config::get('supabase.url');
        $this->headers = Config::get('supabase.headers');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $this->headers,
        ]);
    }

    public function get($endpoint, $query = [])
    {
        try {
            $response = $this->client->get($endpoint, [
                'query' => $query,
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function post($endpoint, $data = [])
    {
        try {
            $response = $this->client->post($endpoint, [
                'json' => $data,
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function patch($endpoint, $data = [])
    {
        try {
            $response = $this->client->patch($endpoint, [
                'json' => $data,
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return null;
        }
    }
}