<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\UserSerivce;
use Illuminate\Http\Request;
use App\Models\User as ObjModel;


class UserController extends Controller
{
    public function __construct(protected UserSerivce $objService) {}

    public function index(Request $request)
    {
        return $this->objService->index($request);
    }

    public function show($id)
    {
        return response()->json("sdf");
    }
    public function create()
    {
        return $this->objService->create();
    }

    public function store(Request $data)
    {
        $data = $data->validated();
        return $this->objService->store($data);
    }

    public function edit(ObjModel $model)
    {
        return $this->objService->edit($model);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validated();
        return $this->objService->update($data, $id);
    }

    public function destroy($id)
    {
        return $this->objService->delete($id);
    }
    public function updateColumnSelected(Request $request)
    {
        return $this->objService->updateColumnSelected($request,'status');
    }

    public function deleteSelected(Request $request){
        return $this->objService->deleteSelected($request);
    }
}
