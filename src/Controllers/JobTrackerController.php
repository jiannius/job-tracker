<?php

namespace Jiannius\JobTracker\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\JobTracker;

class JobTrackerController extends Controller
{
    public function get(Request $request)
    {
        $job = null;

        if ($id = $request->input('id')) $job = JobTracker::find($id);
        else if ($uuid = $request->input('uuid')) $job = JobTracker::where('uuid', $uuid)->first();
        else {
            $name = $request->input('name');
            $user = $request->input('user');

            $job = JobTracker::query()
                ->whereIn('name', (array) $name)
                ->when($user, fn($q) => $q->where('user_id', $user))
                ->latest()
                ->first();
        }

        return response()->json($job);
    }

    public function delete(Request $request)
    {
        $job = JobTracker::find($request->id)
            ?? JobTracker::where('uuid', $request->uuid)->first();

        if ($job) $job->delete();

        return response()->json(true);
    }

    public function download(Request $request)
    {
        $tracker = JobTracker::withoutGlobalScopes()->find($request->id);

        if (!file_exists(storage_path('app/'.$tracker->path))) abort(404);

        return response()->download(storage_path('app/'.$tracker->path));
    }
}
