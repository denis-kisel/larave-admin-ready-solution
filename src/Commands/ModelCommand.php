<?php


namespace DenisKisel\Constructor\Commands;

use DenisKisel\Constructor\Services\FieldsService;
use DenisKisel\Constructor\Services\MigrationService;
use Illuminate\Console\Command;

class ModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     * fields: field_name:data_type:length{migration_methods}
     * {type} - string|integer|text
     *
     * Example: construct:model App\\Models\\Page name:string,description:text{nullable},is_active:boolean{nullable+default:1}
     *
     * @var string
     */
    protected $signature = 'construct:model {model} {fields} {--i} {--m} {--a}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Constructor for models';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->stopIfModelExists();
        $this->makeModel();
        $this->makeMigration();
        $this->makeAdminController();
    }

    protected function makeModel()
    {
        if ($this->option('i')  && class_exists($this->argument('model'))) {
            return;
        }
        $this->call("make:model", [
            'name' => $this->argument('model'),
        ]);
    }

    protected function baseNameModelClass()
    {
        return class_basename($this->argument('model'));
    }

    protected function fields()
    {
        return FieldsService::parse($this->argument('fields'));
    }

    protected function makeMigration()
    {
        $stub = __DIR__ . '/../../resources/custom/migration.stub';
        MigrationService::create($this->baseNameModelClass(), $stub, ['{fields}', $this->makeMigrationFields()]);
        $this->info('Migration is created!');

        if ($this->option('m')) {
            $this->call('migrate');
        }
    }

    protected function makeMigrationFields()
    {
        return MigrationService::generateMigrationFields($this->fields());
    }

    protected function makeAdminController()
    {
        if ($this->option('a')) {
            $this->call('construct:admin', [
                'model' => $this->argument('model'),
                'fields' => $this->argument('fields'),
                '--i' => ($this->option('i')) ? '--i' : null,
            ]);
        }
    }

    protected function stopIfModelExists()
    {
        if ($this->option('i')) {
            return;
        }
        if (class_exists($this->argument('model'))) {
            $this->warn('This model is already exists!');
            die();
        }
    }
}