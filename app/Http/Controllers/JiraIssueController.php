<?php

namespace App\Http\Controllers;
use App\Services\Jira\JiraDescriptionService;
use App\Services\Jira\JiraIssueService;
use Illuminate\Http\Request;

class JiraIssueController extends Controller
{
    //
    public function __construct(
        protected JiraIssueService $jiraIssueService,
        protected JiraDescriptionService $jiraDescriptionService
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'reporter' => ['required', 'string'],
            'assignee' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'application' => ['required', 'string'],
            'category' => ['required', 'string'],
        ]);

        $fields = [
            'project' => [
                'key' => 'ATP',
            ],

            'issuetype' => [
                'id' => '10010',
            ],

            'reporter' => [
                'accountId' => $validated['reporter'],
            ],

            'summary' => $validated['summary'],

            'labels' => [
                $validated['application'],
                $validated['category'],
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | Description
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['description'])) {
            $fields['description'] = $this->jiraDescriptionService
                ->fromQuill($validated['description']);
        }


        if (!empty($validated['priority'])) {
            $fields['priority'] = [
                'id' => $validated['priority'],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Assignee
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['assignee'])) {
            $fields['assignee'] = [
                'accountId' => $validated['assignee'],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Priority
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['priority'])) {
            $fields['priority'] = [
                'id' => $validated['priority'],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Due Date
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['due_date'])) {
            $fields['duedate'] = $validated['due_date'];
        }

        \Log::info('Jira Create Issue Payload', $fields);
        $response = $this->jiraIssueService->create($fields);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Jira issue.',
                'error' => $response->json(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'message' => 'Jira issue created successfully.',
            'data' => $response->json(),
        ]);
    }
}
