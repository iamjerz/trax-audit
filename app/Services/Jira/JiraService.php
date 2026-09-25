<?php

namespace App\Services\Jira;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class JiraService
{
    protected string $baseUrl;
    protected string $email;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.jira.url'), '/');
        $this->email = config('services.jira.email');
        $this->token = config('services.jira.token');
    }

    /**
     * Create the authenticated Jira HTTP client.
     */
    protected function client(): PendingRequest
    {
        return Http::withBasicAuth(
            $this->email,
            $this->token
        )
            ->withoutVerifying()
            ->acceptJson()
            ->timeout(30);
    }

    /**
     * GET request to Jira API.
     */
    public function get(
        string $endpoint,
        array $query = []
    ): Response {
        return $this->client()->get(
            $this->url($endpoint),
            $query
        );
    }

    /**
     * POST request to Jira API.
     */
    public function post(
        string $endpoint,
        array $data = []
    ): Response {
        return $this->client()->post(
            $this->url($endpoint),
            $data
        );
    }

    /**
     * PUT request to Jira API.
     */
    public function put(
        string $endpoint,
        array $data = []
    ): Response {
        return $this->client()->put(
            $this->url($endpoint),
            $data
        );
    }

    /**
     * DELETE request to Jira API.
     */
    public function delete(
        string $endpoint,
        array $data = []
    ): Response {
        return $this->client()->delete(
            $this->url($endpoint),
            $data
        );
    }

    /**
     * Build the full Jira API URL.
     */
    protected function url(string $endpoint): string
    {
        return $this->baseUrl . '/' . ltrim($endpoint, '/');
    }
}