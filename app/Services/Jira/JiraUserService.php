<?php

namespace App\Services\Jira;

use Illuminate\Http\Client\Response;

class JiraUserService
{
    public function __construct(
        protected JiraService $jira
    ) {
    }

    /**
     * Get the currently authenticated Jira user.
     */
    public function myself(): Response
    {
        return $this->jira->get(
            '/rest/api/3/myself'
        );
    }

    /**
     * Search for Jira users.
     */
    public function search(string $query): Response
    {
        return $this->jira->get(
            '/rest/api/3/user/search',
            [
                'query' => $query,
            ]
        );
    }

    /**
     * Search users using Jira's user picker.
     */
    public function picker(string $query): Response
    {
        return $this->jira->get(
            '/rest/api/3/user/picker',
            [
                'query' => $query,
            ]
        );
    }

    /**
     * Search users/groups using the group user picker.
     */
    public function groupUserPicker(string $query): Response
    {
        return $this->jira->get(
            '/rest/api/3/groupuserpicker',
            [
                'query' => $query,
            ]
        );
    }

    /**
     * Get users assignable to a project.
     */
    public function assignable(
        string $projectKey,
        ?string $query = null
    ): Response {
        $params = [];

        if ($query !== null) {
            $params['query'] = $query;
        }

        return $this->jira->get(
            '/rest/api/3/user/assignable/search',
            array_merge(
                [
                    'project' => $projectKey,
                ],
                $params
            )
        );
    }

    /**
     * Get a specific Jira user by account ID.
     */
    public function get(string $accountId): Response
    {
        return $this->jira->get(
            '/rest/api/3/user',
            [
                'accountId' => $accountId,
            ]
        );
    }
}