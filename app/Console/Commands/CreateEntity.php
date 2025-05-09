<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateEntity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-entity {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create new model , migration , factory , seeder , request , controller , service , blade(index,create,edit) ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument("name");
        $namespace = 'App';
        $modelName = Str::studly($name);
        $serviceName = "{$modelName}Service";
        $controllerName = "{$modelName}Controller";
        $requestName = "{$modelName}Request";

        // Create Model
        $modelPath = app_path("Models/{$modelName}.php");
        if (!File::exists($modelPath)) {
            File::put($modelPath, $this->getModelStub($modelName));
            $this->info("Model {$modelName} created successfully.");
        }

        // Create Migration
        $tableName = Str::snake(Str::pluralStudly($name));
        $migrationName = "create_{$tableName}_table";
        $migrationPath = database_path("migrations/" . date('Y_m_d_His') . "_{$migrationName}.php");
        if (!File::exists($migrationPath)) {
            $this->call('make:migration', [
                'name' => $migrationName,
                '--create' => $tableName,
            ]);
            $this->info("Migration {$migrationName} created successfully.");
        }

        // Create Controller
        $controllerPath = app_path("Http/Controllers/Admin/{$controllerName}.php");
        if (!File::exists($controllerPath)) {
            File::ensureDirectoryExists(app_path('Http/Controllers/Admin'));
            File::put($controllerPath, $this->getControllerStub($modelName, $serviceName));
            $this->info("Controller {$controllerName} created successfully.");
        }

        // Create Service
        $servicePath = app_path("Services/Admin/{$serviceName}.php");
        if (!File::exists($servicePath)) {
            File::ensureDirectoryExists(app_path('Services/Admin'));
            File::put($servicePath, $this->getServiceStub($modelName));
            $this->info("Service {$serviceName} created successfully.");
        }

        // Create Request
        $requestPath = app_path("Http/Requests/{$requestName}.php");
        if (!File::exists($requestPath)) {
            File::ensureDirectoryExists(app_path('Http/Requests'));
            File::put($requestPath, $this->getRequestStub($modelName));
            $this->info("Request {$requestName} created successfully.");
        }
        // copy folder name example_crud to name new model in views
        $folderName = strtolower(Str::snake($modelName)); // Derive folder name from model
        $folderPath = resource_path("views/admin/{$folderName}");
        if (!File::exists($folderPath)) {
            File::ensureDirectoryExists(resource_path('views/admin'));
            File::copyDirectory(resource_path('views/example_crud'), $folderPath);
            $this->info("Folder {$folderName} created successfully.");
        }

        // Create Routes
        $this->addResourceRoute($modelName, $folderName);


    }


    private function getModelStub($modelName)
    {
        return <<<EOT
<?php

namespace App\Models;

class {$modelName} extends BaseModel
{
    protected \$fillable = [];
    protected \$casts = [];

}
EOT;
    }

    private function getControllerStub($modelName, $serviceName)
    {
        return <<<EOT
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\\{$modelName}Request as ObjRequest;
use App\Models\\{$modelName} as ObjModel;
use App\Services\Admin\\{$serviceName} as ObjService;
use Illuminate\Http\Request;

class {$modelName}Controller extends Controller
{
    public function __construct(protected ObjService \$objService) {}

    public function index(Request \$request)
    {
        return \$this->objService->index(\$request);
    }

    public function create()
    {
        return \$this->objService->create();
    }

    public function store(ObjRequest \$data)
    {
        \$data = \$data->validated();
        return \$this->objService->store(\$data);
    }

    public function edit(ObjModel \$model)
    {
        return \$this->objService->edit(\$model);
    }

    public function update(ObjRequest \$request, \$id)
    {
        \$data = \$request->validated();
        return \$this->objService->update(\$data, \$id);
    }

    public function destroy(\$id)
    {
        return \$this->objService->delete(\$id);
    }
        public function updateColumnSelected(\Request \$request)
    {
        return \$this->objService->updateColumnSelected(\$request,'status');
    }

    public function deleteSelected(\Request \$request){
        return \$this->objService->deleteSelected(\$request);
    }
}
EOT;
    }

    private function getServiceStub($modelName)
    {
        $folderName = strtolower(Str::snake($modelName)); // Derive folder name from model

        return <<<EOT
<?php

namespace App\Services\Admin;

use App\Models\\{$modelName} as ObjModel;
use App\Services\BaseService;
use Yajra\DataTables\DataTables;

class {$modelName}Service extends BaseService
{
    protected string \$folder = 'admin/{$folderName}';
    protected string \$route = '{$folderName}s';

    public function __construct(ObjModel \$objModel)
    {
        parent::__construct(\$objModel);
    }

    public function index(\$request)
    {
        if (\$request->ajax()) {
            \$obj = \$this->getDataTable();
            return DataTables::of(\$obj)
                ->addColumn('action', function (\$obj) {
                    \$buttons = '
                        <button type="button" data-id="' . \$obj->id . '" class="btn btn-pill btn-info-light editBtn">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-pill btn-danger-light" data-bs-toggle="modal"
                            data-bs-target="#delete_modal" data-id="' . \$obj->id . '" data-title="' . \$obj->name . '">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                    return \$buttons;
                })
                ->addIndexColumn()
                ->escapeColumns([])
                ->make(true);
        } else {
            return view(\$this->folder . '/index', [
                'createRoute' => route(\$this->route . '.create'),
                'bladeName' => trns(\$this->route),
                'route' => \$this->route,
            ]);
        }
    }

    public function create()
    {
        return view("{\$this->folder}/parts/create", [
            'storeRoute' => route("{\$this->route}.store"),
        ]);
    }

    public function store(\$data): \Illuminate\Http\JsonResponse
    {
        if (isset(\$data['image'])) {
            \$data['image'] = \$this->handleFile(\$data['image'], '{$modelName}');
        }

        try {
            \$this->createData(\$data);
            return response()->json(['status' => 200, 'message' => trns('Data created successfully.')]);
        } catch (\Exception \$e) {
            return response()->json(['status' => 500, 'message' => trns('Something went wrong.'), trns('error') => \$e->getMessage()]);
        }
    }

    public function edit(\$obj)
    {
        return view("{\$this->folder}/parts/edit", [
            'obj' => \$obj,
            'updateRoute' => route("{\$this->route}.update", \$obj->id),
        ]);
    }

    public function update(\$data, \$id)
    {
        \$oldObj = \$this->getById(\$id);

        if (isset(\$data['image'])) {
            \$data['image'] = \$this->handleFile(\$data['image'], '{$modelName}');

            if (\$oldObj->image) {
                \$this->deleteFile(\$oldObj->image);
            }
        }

        try {
            \$oldObj->update(\$data);
            return response()->json(['status' => 200, 'message' => trns('Data updated successfully.')]);
        } catch (\Exception \$e) {
            return response()->json(['status' => 500, 'message' => trns('Something went wrong.'), trns('error') => \$e->getMessage()]);
        }
    }
}
EOT;
    }



    private function getRequestStub($modelName)
    {
        return <<<EOT
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$modelName}Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if (\$this->isMethod('put')) {
            return \$this->update();
        } else {
            return \$this->store();
        }
    }

    protected function store(): array
    {
        return [

        ];
    }

    protected function update(): array
    {
        return [

        ];
    }
}
EOT;
    }


    private function addResourceRoute($modelName, $folderName)
    {
        $routeFile = base_path('routes/admin.php');

        // Check if the route already exists to avoid duplication
        $routePattern = "Route::resource('{$folderName}s'";
        if (!File::exists($routeFile) || strpos(file_get_contents($routeFile), $routePattern) !== false) {
            return; // Route already exists, do nothing
        }

        // If not, add the resource route at the end of the file
        File::append($routeFile, "\nRoute::resource('{$folderName}s', \App\Http\Controllers\Admin\\{$modelName}Controller::class);\n");

        $this->info("Resource route for {$folderName}s created successfully.");
    }

}
