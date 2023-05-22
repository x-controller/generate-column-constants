<?php

namespace XController\GenerateColumnConstants;

use Illuminate\Support\ServiceProvider;
use XController\GenerateColumnConstants\MakeColumnConstCommand;

class ColumnConstGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            MakeColumnConstCommand::class,
        ]);
    }
}
