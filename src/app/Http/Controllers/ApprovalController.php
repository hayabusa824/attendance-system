<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function show($id)
    {
        $request = RequestModel::with('user')->findOrFail($id);

        $clockIn = $request->clock_in;
        $clockOut = $request->clock_out;
        $breaks = json_decode($request->breaks, true);

        return view('approvalDetail', [
            'request' => $request,
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'breaks' => $breaks,
        ]);
    }

    public function approve($id)
    {
        $request = RequestModel::findOrFail($id);

        // すでに承認済みなら何もしない
        if ($request->status === 'approved') {
            return back();
        }

        $request->status = 'approved';
        $request->save();

        return back();
    }
}
