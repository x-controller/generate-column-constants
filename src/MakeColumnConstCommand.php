<?php

namespace XController\GenerateColumnConstants;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeColumnConstCommand extends Command
{
    protected $signature = 'make:column_const';

    protected $description = 'Generate column constants for all tables in the database';

    public function handle()
    {
        $this->clearColumnsDirectory();

        $tables = $this->getAllTables();

        foreach ($tables as $table) {
            $this->line($table);
            $fields = $this->getTableFields($table);
            $this->generateConstants($table, $fields);
        }

        $this->info('Column constants generated successfully!');
    }

    protected function clearColumnsDirectory()
    {
        $constantsDirectory = app_path('Constants/Columns/');

        $files = glob($constantsDirectory . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    protected function getAllTables()
    {
        $databaseName = DB::getDatabaseName();
        $tables = DB::select("SHOW TABLES FROM $databaseName");

        return array_map('current', $tables);
    }

    protected function getTableFields($table)
    {
        $fields = DB::getSchemaBuilder()->getColumnListing($table);

        return $fields;
    }

    protected function generateConstants($table, $fields)
    {
        $constants = '';

        foreach ($fields as $field) {
            $constantName = strtoupper($field);
            $constantValue = "'" . $field . "'";

            $fieldComment = $this->getFieldComment($table, $field);
            $constants .= "    /**\n     * $fieldComment\n     */\n";
            $constants .= "    public const $constantName = $constantValue;\n\n";
        }

        $namespace = 'App\\Constants\\Columns';
        $fileName = \Illuminate\Support\Str::studly($table) . 'Columns.php';
        $filePath = app_path('Constants/Columns/' . $fileName);

        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileContent = "<?php\n\nnamespace $namespace;\n\nclass " . \Illuminate\Support\Str::studly($table) . "Columns\n{\n$constants}\n";
        file_put_contents($filePath, $fileContent);
    }

    protected function getFieldComment($table, $field)
    {
        $query = "SELECT COLUMN_COMMENT
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_NAME = ? AND COLUMN_NAME = ?";
        $comment = DB::selectOne($query, [$table, $field])->COLUMN_COMMENT;

        return $comment;
    }
}
