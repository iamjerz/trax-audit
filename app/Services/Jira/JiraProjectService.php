<?php

namespace App\Services\Jira;

use Illuminate\Http\Client\Response;

class JiraProjectService
{
    public function __construct(
        protected JiraService $jira
    ) {
    }

    /**
     * Get project details.
     */
    public function get(string $projectKey): Response
    {
        return $this->jira->get(
            "/rest/api/3/project/{$projectKey}"
        );
    }

    /**
     * Get issue types available for a project.
     */
    public function issueTypes(string $projectKey): Response
    {
        return $this->jira->get(
            "/rest/api/3/issue/createmeta/{$projectKey}/issuetypes"
        );
    }

    /**
     * Get create metadata for a project and issue type.
     */
    public function createMetadata(
        string $projectKey,
        string $issueTypeId
    ): Response {
        return $this->jira->get(
            "/rest/api/3/issue/createmeta/{$projectKey}/issuetypes/{$issueTypeId}"
        );
    }
}