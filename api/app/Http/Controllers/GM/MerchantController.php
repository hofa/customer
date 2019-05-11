<?php
namespace App\Http\Controllers\GM;

use App\Http\Controllers\Controller;
use App\Models\MerchantAccount;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:gm');
    }

    public function account(Request $request)
    {
        $model = new MerchantAccount;
        $request->input('merchantShortName') && $model = $model->where('merchantShortName', $request->input('merchantShortName'));
        $data = $model->orderBy('_id', 'desc')->select(['username', 'merchantShortName', 'status'])->paginate(15)->toArray();
        return $this->table($data['total'], $data['data']);
    }

    public function doCreate(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|min:6|max:32|unique:MerchantAccount',
            'password' => 'required|min:6|max:32',
            'merchantShortName' => 'required|min:2|max:100',
            'merchantName' => 'required|min:2|max:100',
        ]);

        $data = $request->only('username', 'password', 'merchantShortName', 'merchantName');
        $data['status'] = 'Normal';
        $data['password'] = app('hash')->make($data['password']);
        // dd($data);
        MerchantAccount::create($data);
        return $this->success();
    }

    public function doUpdate(Request $request, $id)
    {
        $model = MerchantAccount::find($id);
        if (!$model) {
            return $this->error(40003);
        }

        $this->validate($request, [
            'merchantShortName' => 'required|min:2|max:100',
            'merchantName' => 'required|min:2|max:100',
            'status' => 'required|in:Normal,Close',
        ]);

        $data = $request->only('merchantShortName', 'merchantName', 'status');
        $model->save($data);
        return $this->success();
    }

    public function doUpdatePassword(Request $request, $id)
    {
        $model = MerchantAccount::find($id);
        if (!$model) {
            return $this->error(40003);
        }

        $this->validate($request, [
            'password' => 'required|password',
        ]);

        $data = $request->only('password');
        $model->save($data);
        return $this->success();
    }

    public function detail(Request $request, $id)
    {
        $model = MerchantAccount::find($id);
        if (!$model) {
            return $this->error(40003);
        }
        return $this->success($model);
    }

}
