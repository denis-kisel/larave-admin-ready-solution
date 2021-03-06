<?php


namespace DenisKisel\Constructor\Commands;


use Illuminate\Console\Command;

class PageTranslationCommand extends Command
{
    protected $signature = 'construct:paget {model} {--fields=} {--a} {--i} {--m}';
    protected $description = 'Constructor of page translation';


    public function handle()
    {
        $this->callModel();
        $this->callAdmin();
    }

    //STACK
    protected function callModel()
    {
        $this->call('construct:modelt', [
            'model' => $this->argument('model'),
            '--fields' => $this->fields(),
            '--i' => $this->option('i'),
            '--m' => $this->option('m')
        ]);
    }

    protected function callAdmin()
    {
        if ($this->option('a')) {
            $this->call('construct:admint', [
                'model' => $this->argument('model'),
                '--fields' => $this->fields(),
                '--i' => $this->option('i'),
            ]);
        }
    }

    //HELPER
    protected function fields()
    {
        $fields = include(__DIR__ . '/../../resources/patterns/page.php');
        if ($this->option('fields')) {
            $fields = str_replace('{option_fields}', $this->option('fields'), $fields);
        }

        $fields = str_replace(',{option_fields}', '', $fields);
        return $fields;
    }
}