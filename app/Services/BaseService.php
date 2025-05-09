<?php


namespace App\Services;

use App\Enums\AdConfirmationEnum;
use App\Traits\PhotoTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Class BaseService
 * Provides common functionalities to be used by other service classes.
 */
abstract class BaseService
{
    use PhotoTrait;

    /**
     * @var Model
     */
    public Model $model;

    /**
     * BaseService constructor.
     * @param Model $model The model to be used by the service.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all instances of the model.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getSelected(...$columns)
    {
        return $this->model->select(...$columns)->get();
    }

    /**
     * @return mixed
     */
    public function getDataTable(): mixed
    {
        return $this->model->latest()->get();
    }

    public function statusDatatable($obj)
    {
        return '
                    <div class="form-check form-switch">
                       <input style="margin-left: 0px;" class="tgl tgl-ios statusBtn form-check-input" data-id="' . $obj->id . '" name="statusBtn" id="statusUser-' . $obj->id . '" type="checkbox" ' . ($obj->status == 1 ? 'checked' : '') . '/>
                       <label class="tgl-btn" dir="ltr" for="statusUser-' . $obj->id . '"></label>
                    </div>';
    }

    public function acceptOrReject($obj, $column = 'ad_confirmation')
    {
        $acceptBtn = "<button class='btn btn-success' data-id='{$obj->id}' name='acceptBtn' id='acceptBtn-{$obj->id}' onclick='changeConfirmation({$obj->id}, \"" . AdConfirmationEnum::CONFIRMED->value . "\")'>
         " . ($obj->{$column} == AdConfirmationEnum::CONFIRMED->value ? trns('accepted') : trns('accept')) . "
            </button>";

        $acceptBtnDisabled = "<button disabled class='btn btn-success' data-id='{$obj->id}'>
            " . trns('accepted') . "
               </button>";
//dd($obj->id);
        $rejectBtn = "<button class='btn btn-danger refuse_reason_btn' data-refuse-id='{$obj->id}' name='rejectBtn'   data-bs-toggle='modal' data-bs-target='#refuse_reason_modal'>
       " . ($obj->{$column} == AdConfirmationEnum::REJECTED->value ? trns('rejected') : trns('reject')) . "
            </button>";

        $rejectBtnDisabled = "<button disabled class='btn btn-danger' data-id='{$obj->id}'>
        " . trns('rejected') . "
            </button>";

        if ($obj->{$column} == AdConfirmationEnum::REQUESTED->value) {
            return "<div class='form-input'>{$acceptBtn} {$rejectBtn}</div>";
        } elseif ($obj->{$column} == AdConfirmationEnum::CONFIRMED->value) {
            return "<div class='form-input'>{$acceptBtnDisabled}</div>";
        } else {
            return "<div class='form-input'>{$rejectBtnDisabled}</div>";
        }
    }


    public function StatusDatatableCustom($obj, $status = 1, $column = 'status')
    {
        return
            '<div class="form-check form-switch">
                <input style="margin-left: 0px;" class="tgl tgl-ios statusBtnOne form-check-input" data-id="' . $obj->id . '" name="statusBtnOne" id="statusBtnOne-' . $obj->id . '" type="checkbox" ' . ($obj->{$column} == $status ? 'checked' : '') . '/>
                <label class="tgl-btn" dir="ltr" for="statusUser-' . $obj->id . '"></label>
                </div>';
    }

//    public function acceptOrReject($obj,$column = 'ad_confirmation')
//    {
//        $acceptBtn = " <button class='btn btn-success' id='refuse_reason_btn' data-id='{$obj->id}' name='acceptBtn' id='acceptBtn-{$obj->id}' onclick='changeConfirmation({$obj->id}, \"" . AdConfirmationEnum::CONFIRMED->value . "\")'>
//             " . ($obj->{$column} == AdConfirmationEnum::CONFIRMED->value ? trns('accepted') : trns('accept')) . "
//                </button>";
//        $acceptBtnDisabled = " <button disabled class='btn btn-success' data-id='{$obj->id}' name='acceptBtn' id='acceptBtn-{$obj->id}' onclick='changeConfirmation({$obj->id}, \"" . AdConfirmationEnum::CONFIRMED->value . "\")'>
//                " . ($obj->{$column} == AdConfirmationEnum::CONFIRMED->value ? trns('accepted') : trns('accept')) . "
//                   </button>";
//
////        $rejectBtn = " <button class='btn btn-danger' data-id='{$obj->id}' name='rejectBtn' id='rejectBtn-{$obj->id}' data-bs-toggle='modal' data-bs-target='#refuse_reason_modal'  onclick='changeConfirmation({$obj->id}, \"" . AdConfirmationEnum::REJECTED->value . "\")' >
////       " . ($obj->{$column} == AdConfirmationEnum::REJECTED->value ? trns('rejected') : trns('reject')) . "
////                </button>";
//
//        $rejectBtn = " <button class='btn btn-danger' data-id='{$obj->id}' name='rejectBtn' id='rejectBtn-{$obj->id}' data-bs-toggle='modal' data-bs-target='#refuse_reason_modal'>
//       " . ($obj->{$column} == AdConfirmationEnum::REJECTED->value ? trns('rejected') : trns('reject')) . "
//                </button>";
//
//        $rejectBtnDisbled = " <button disabled class='btn btn-danger' data-id='{$obj->id}' name='rejectBtn' id='rejectBtn-{$obj->id}' data-refuse-id='$obj->id' onclick='changeConfirmation({$obj->id}, \"" . AdConfirmationEnum::REJECTED->value . "\")'>
//        " . ($obj->{$column} == AdConfirmationEnum::REJECTED->value ? trns('rejected') : trns('reject')) . "
//                    </button>";
//
//        if ($obj->{$column} == AdConfirmationEnum::REQUESTED->value) {
//            // $this->StatusDatatableCustom($obj,StatusEnum::ACTIVE->value);
//            return "<div class='form-input'>{$acceptBtn} {$rejectBtn}</div>";
//        } elseif ($obj->{$column} == AdConfirmationEnum::CONFIRMED->value) {
//            return "<div class='form-input'>{$acceptBtnDisabled}</div>";
//        } else {
//            return "<div class='form-input'>{$rejectBtnDisbled}</div>";
//        }
//    }
    /**
     * Get all instances of the model that match the given conditions.
     *
     * @param array $conditions
     * @return Collection
     */
    public function getWhere(array $conditions): Collection
    {
        $query = $this->model->query();

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->where($field, $value[0], $value[1]);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get();
    }

    public function firstWhere(array $conditions): ?Model
    {
        $query = $this->model->query();

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->where($field, $value[0], $value[1]);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->first();
    }

    /**
     * Get a single instance of the model by ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function getById(int $id): ?Model
    {
        return $this->model->findOrFail($id);
    }

    public function getActive($column)
    {
        return $this->model->where($column, 1)->get();
    }

    public function handleFile($file, $folder = null, $type = 'image')
    {
        return $this->saveImage($file, $folder, $type);
    }

    public function handleFiles($files, $folder = null)
    {
        $data = [];
        foreach ($files as $file) {
            $data[] = $this->saveImage($file, $folder);
        }

        return $data;
    }

    /**
     * Create a new instance of the model.
     * @param array $data
     */
    public function createData(array $data)
    {
        return $this->model->create($data);
    }

//   store images by mediaLiberary spatie
            //  $request -> file
            //  $model -> model
            //  $inputName -> file type (image , file)
            //  $collectionName -> folder to store

    public function storeMediaLibrary($data, $instance, $inputName = 'file', $collectionName = 'documents')
    {
        $files = $data;

        if ($instance->hasMedia($collectionName)) {
            $instance->clearMediaCollection($collectionName);
        }

        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file->isValid()) {
                     $instance->addMedia($file)->toMediaCollection($collectionName);
                }
            }
        } else {
            if ($files->isValid()) {
                 $instance->addMedia($files)->toMediaCollection($collectionName);
            }
        }

    }




    public function uploadImage($image, $folder = null)
    {

        return $image->store('uploads/ ' . $folder, 'public');
    }

    /**
     * Update an existing instance of the model.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateData(int $id, array $data  )
    {
        $model = $this->getById($id);
        return $model->update($data);
    }

    /**
     * Delete an instance of the model by ID and its associated files.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $model = $this->getById($id);


        if ($model) {
            // Check and delete any associated files
            if (isset($model->image)) {
                $this->deleteFile($model->image);
            }
            if (isset($model->breadcrumb)) {
                $this->deleteFile($model->breadcrumb);
            }
            if (isset($model->not_found_icon)) {
                $this->deleteFile($model->not_found_icon);
            }

            // Proceed with model deletion
            $model->delete();

            return response()->json(['status' => 200, 'message' => trns('deleted successfully')]);
        }
        return response()->json(['status' => 405, 'message' => trns('something went wrong')]);
    }

    public function changeStatus($request)
    {
        $obj = $this->getById($request->id);

        if ($obj) {
            $obj->status = $obj->status == 1 ? 0 : 1;

            $obj->save();
            return response()->json(['status' => 200]);
        }


        return response()->json(['status' => 405]);
    }


    public function updateColumn($id, $column, $value = null)
    {

        $obj = $this->getById($id);
        if ($value) {
            $obj->{$column} = $value;
            $obj->save();
            return true;
        } else {
            $obj->{$column} = !$obj->{$column};
            $obj->save();
            return true;
        }
    }


    /**
     * Delete files associated with the model.
     *
     * @param Model $model
     * @return void
     */
    protected function deleteAssociatedFiles(Model $model): void
    {
        // Check and delete single image or file
        if (!empty($model->image)) {
            $this->deleteFile($model->image);
        }

        // Check and delete multiple images or files
        $fields = ['images', 'files']; // Adjust according to your model's fields
        foreach ($fields as $field) {
            if (!empty($model->{$field})) {
                foreach ($model->{$field} as $file) {
                    $this->deleteFile($file);
                }
            }
        }
    }

    /**
     * Helper function to delete a file from storage.
     *
     * @param string $filePath
     * @return void
     */
    protected function deleteFile(string $filePath): void
    {
        if (File::exists(public_path($filePath))) {
            File::delete(public_path($filePath));
        }
    }

    /**
     * Get a pluck array from the model based on specified key and value.
     *
     * @param string $keyField The attribute to use as the key.
     * @param string $valueField The attribute to use as the value.
     * @return array
     */
    public function getPluckArray(string $keyField, string $valueField): array
    {
        return $this->model->pluck($valueField, $keyField)->toArray();
    }

    /**
     * @param $image
     * @return string
     */
    public function imageDataTable($image): string
    {
        if ($image)
            return '<img src="' . getFile($image) . '" onclick="window.open(' . "'" . getFile($image) . "'" . ')" class="avatar avatar-md rounded-circle" style="cursor:pointer;" width="100" height="100">';
        else
            $image = getFile('assets/uploads/empty.png');
        return '<img src="' . getFile($image) . '" onclick="window.open(' . "'" . getFile($image) . "'" . ')" class="avatar avatar-md rounded-circle" style="cursor:pointer;" width="100" height="100">';
    }

    /**
     * @param $request
     * @param $rules
     * @return false|JsonResponse
     */
    public function apiValidator($request, $rules,$messages = []): false|JsonResponse
    {
        $validator = Validator::make($request, $rules, $messages=[]);

        if ($validator->fails()) {
            return $this->responseMsg($validator->errors()->first(), null, 422);
        }

        return false;
    }

    /**
     * @param $msg
     * @param $data
     * @param int $status
     * @return JsonResponse
     */
    public function responseMsg($msg, $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => $msg,
            'status' => $status
        ]);
    }

    /**
     * @param $data
     * @return JsonResponse
     */
    public function successResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => trns('success_fetching_data'),
            'status' => 200
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function errorResponse($data=null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => trns('error_fetching_data'),
            'status' => 500
        ]);
    }

    /**
     * @param $data
     * @return JsonResponse
     */
    public function validationResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => trns('validation_error'),
            'status' => 422
        ]);
    }

    /**
     * @return string
     */
    protected function generateCode(): string
    {
        do {
            $code = Str::random(11);
        } while ($this->firstWhere(['code' => $code]));

        return $code;
    }

    public function generateOtp($qty = 6): int
    {
        return random_int(pow(10, $qty - 1), pow(10, $qty) - 1);
    }

    protected function updateEnvVariable($key, $value): void
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            // Read the .env file content
            $envContent = file_get_contents($path);

            // Find the variable in the .env file or add it if it doesn’t exist
            if (strpos($envContent, "{$key}=") !== false) {
                // Replace the value of the existing key
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                // Append new variable to the end of .env file
                $envContent .= "\n{$key}={$value}";
            }

            // Write the updated content back to the .env file
            file_put_contents($path, $envContent);
        }
    }

    public function deleteSelected($request)
    {
        try {

            $ids = $request->input('ids');
            if (is_array($ids) && count($ids)) {
                $this->model->whereIn('id', $ids)->delete();
                return response()->json(['status' => 200, 'message' => trns('deleted_successfully')]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => trns('something_went_wrong')]);
        }
    }


    public function updateColumnSelected($request, $column, $values = null)
    {

        try {
            $ids = $request->input('ids');
            if (is_array($ids) && count($ids)) {
                foreach ($ids as $id) {
                    $obj = $this->getById($id);
                    if (is_array($values)) {
                        $obj->{$column} == $values[0] ? $obj->{$column} = $values[1] : $obj->{$column} = $values[0];
                    } else {
                        $obj->{$column} = !$obj->{$column};
                    }
                    $obj->save();
                }
                return response()->json(['status' => 200, 'message' => trns('updated_successfully')]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => trns('something_went_wrong')]);
        }
    }

    public function generateUsername($name)
    {
        return str_replace(' ', '', strtolower($name)) . rand(1000, 9999);
    }


    public function subStrText($text){
        return '
        <span style="
                display: inline-block;max-width: 200px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;padding: 3px;transition: max-width 0.3s ease-in-out;position: relative;cursor: pointer;"onmouseover="this.style.maxWidth=\'100%\'; this.style.whiteSpace=\'normal\'; this.style.overflow=\'visible\';                    this.style.background=\'rgba(0, 0, 0, 0.7)\'; this.style.color=\'white\'; this.style.padding=\'8px\';
                this.style.borderRadius=\'5px\'; this.style.position=\'absolute\'; this.style.zIndex=\'1000\';"
                onmouseout="this.style.maxWidth=\'200px\'; this.style.whiteSpace=\'nowrap\'; this.style.overflow=\'hidden\';
                this.style.background=\'transparent\'; this.style.color=\'inherit\'; this.style.padding=\'5px\';
                this.style.borderRadius=\'0px\'; this.style.position=\'relative\'; this.style.zIndex=\'auto\';"
                title="' . e($text) . '">'
        . (!empty($text) ? e($text) : "-") .
        '</span>';
    }
}
