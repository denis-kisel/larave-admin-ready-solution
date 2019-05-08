<?php


namespace DenisKisel\Constructor\Commands;

use DenisKisel\Constructor\Services\FieldsService;
use DenisKisel\Constructor\Services\MigrationService;
use Illuminate\Console\Command;

class ModelTranslationCommand extends Command
{
    /**
     * The name and signature of the console command.
     * fields: field_name:{type}:{length}[null]
     * {type} - string|integer|text
     *
     * ct - custom translation
     *
     * @var string
     */
    protected $signature = 'construct:modelt {model} {fields} {--i} {--m}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Constructor for translation models';


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
    }

    protected function makeModel()
    {
        if ($this->option('i')) {
            return;
        }
        $this->call("make:model", [
            'name' => $this->argument('model'),
        ]);

        $this->call("make:model", [
            'name' => $this->translationModelClass(),
        ]);
    }

    protected function baseNameModelClass()
    {
        return class_basename($this->argument('model'));
    }

    protected function translationModelClass()
    {
        return $this->argument('model') . 'Translation';
    }

    protected function translationBaseModelClass()
    {
        return class_basename($this->translationModelClass());
    }

    protected function fields()
    {
        return FieldsService::parse($this->argument('fields'));
    }

    protected function models()
    {
        return [
            [
                'class' => $this->argument('model'),
                'basename_class' => $this->baseNameModelClass(),
                'template' => 'model'
            ],
            [
                'class' => $this->translationModelClass(),
                'basename_class' => $this->translationBaseModelClass(),
                'template' => 'translation_model'
            ]
        ];
    }

    protected function makeMigration()
    {
        foreach ($this->models() as $model) {
            $stub = __DIR__ . "/../../resources/custom/model.stub";
            MigrationService::create($model['basename_class'], $stub, ['{fields}', $this->makeMigrationFields(
                ($model['template'] == 'translation_model'),
                $this->baseNameModelClass()
            )]);
            $this->info('Migration is created!');

            if ($this->option('m')) {
                $this->call('migrate');
            }
        }
    }

    protected function makeMigrationFields($isTranslation, $modelClassName)
    {
        return MigrationService::makeMigrationFields($this->fields(), $isTranslation, $modelClassName);
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

        if (class_exists($this->translationModelClass())) {
            $this->warn('This translation model is already exists!');
            die();
        }
    }
}
