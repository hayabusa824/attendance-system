<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        // 管理者 or 一般ユーザーの取得
        $user = Auth::guard('admin')->user();
        $guard = 'admin';

        if (!$user) {
            $user = Auth::guard('web')->user();
            $guard = 'web';
        }

        if (!$user) {
            abort(403, '未ログインです');
        }

        $status = $request->input('status', 'pending');

        $query = RequestModel::with('user');

        // 一般ユーザーなら自分の申請だけ
        if ($guard === 'web') {
            $query->where('user_id', $user->id);
        }

        if (in_array($status, ['pending', 'approved'])) {
            $query->where('status', $status);
        }

        $requests = $query->orderByDesc('applied_date')->get();

        // ビューを分岐
        $view = $guard === 'admin' ? 'adminRequest' : 'requestList';

        return view($view, [
            'requests' => $requests,
            'status' => $status,
        ]);
    }
}
