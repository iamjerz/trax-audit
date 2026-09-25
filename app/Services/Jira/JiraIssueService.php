<?php

namespace App\Services\Jira;

use Illuminate\Http\Client\Response;

class JiraIssueService
{
    public function __construct(
        protected JiraService $jira
    ) {}

    /**
     * Create a Jira issue.
     */
    public function create(array $fields): Response
    {
        return $this->jira->post('/rest/api/3/issue', [
            'fields' => $fields,
        ]);
    }

    /**
     * Get an issue.
     */
    public function get(string $issueKey): Response
    {
        return $this->jira->get(
            "/rest/api/3/issue/{$issueKey}"
        );
    }

    /**
     * Update an issue.
     */
    public function update(string $issueKey, array $fields): Response
    {
        return $this->jira->put(
            "/rest/api/3/issue/{$issueKey}",
            [
                'fields' => $fields,
            ]
        );
    }
}