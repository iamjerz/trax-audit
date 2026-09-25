<?php

namespace App\Http\Controllers;
use App\Services\Jira\JiraUserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class JiraController extends Controller
{
    public function __construct(
        private JiraUserService $jiraUserService
    ) {
    }

    public function index()
    {
        $email = Auth::user()->email;

        $response = $this->jiraUserService->assignable(
            'ATP',
            $email
        );

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Jira users.',
                'error' => $response->body(),
            ], $response->status());
        }

        $users = $response->json();

        return view('jiraticket', compact('users'));
    }
}