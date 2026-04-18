<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.
'
bootstrap/app.php
'
;
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo '=== TABLES ===' . PHP_EOL;
$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 
'
public
'
 ORDER BY table_name");
foreach($tables as $t) { echo $t->table_name . PHP_EOL; }
echo PHP_EOL . '=== COLUMNS ===' . PHP_EOL;
$cols = DB::select("SELECT table_name, column_name, data_type, is_nullable FROM information_schema.columns WHERE table_schema = 
'
public
'
 ORDER BY table_name, ordinal_position");
foreach($cols as $c) { echo $c->table_name . " | " . $c->column_name . " | " . $c->data_type . " | nullable=" . $c->is_nullable . PHP_EOL; }
