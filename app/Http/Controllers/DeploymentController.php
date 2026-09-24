<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeploymentActionRequest;
use App\Services\DeploymentRunner;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DeploymentController extends Controller
{
    /**
     * Show the deployment panel with read-only diagnostics.
     */
    public function index(): Response
    {
        $request = request();
        $runner = new DeploymentRunner($request->user(), $request->ip());

        return Inertia::render('Deployment/Index', [
            'actions' => DeploymentRunner::descriptors(),
            'diagnostics' => $runner->diagnostics(),
        ]);
    }

    /**
     * Run a whitelisted deployment action.
     */
    public function store(DeploymentActionRequest $request): RedirectResponse
    {
        $runner = new DeploymentRunner($request->user(), $request->ip());
        $result = $runner->run($request->validated()['action']);

        Inertia::flash('deploymentResult', $result);
        Inertia::flash('toast', [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['success'] ? __('Akcja wykonana.') : __('Akcja nie powiodła się.'),
        ]);

        return back();
    }
}
