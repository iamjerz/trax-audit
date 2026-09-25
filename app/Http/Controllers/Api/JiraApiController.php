<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Jira\JiraUserService;
use Illuminate\Http\JsonResponse;

class JiraApiController extends Controller
{
    //
    //
    public function __construct(
        protected JiraUserService $jiraUserService
    ) {
    }

    /**
     * Get the currently authenticated Jira user.
     */
    public function myself(): JsonResponse
    {
        $response = $this->jiraUserService->myself();

        return response()->json(
            $response->json(),
            $response->status()
        );
    }

    /**
     * Search Jira users.
     */
    public function search(string $query): JsonResponse
    {
        $response = $this->jiraUserService->search($query);

        return response()->json(
            $response->json(),
            $response->status()
        );
    }

    /**
     * Search Jira users using the user picker.
     */
    public function picker(string $query): JsonResponse
    {
        $response = $this->jiraUserService->picker($query);

        return response()->json(
            $response->json(),
            $response->status()
        );
    }

    /**
     * Search users assignable to a Jira project.
     */
    public function assignable(
        string $projectKey,
        string $query
    ): JsonResponse {
        $response = $this->jiraUserService->assignable(
            $projectKey,
            $query
        );

        return response()->json(
            $response->json(),
            $response->status()
        );
    }
}
