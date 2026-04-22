<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

// CMD 1: List all tables
echo "=== COMMAND 1: All Tables ===" . PHP_EOL;
$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema='public' ORDER BY table_name");
foreach($tables as $t) { echo $t->table_name . PHP_EOL; }

// CMD 2: orders columns
echo PHP_EOL . "=== COMMAND 2: orders columns ===" . PHP_EOL;
$cols = DB::select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'orders' ORDER BY ordinal_position");
foreach($cols as $c) { echo $c->column_name . ' | ' . $c->data_type . ' | nullable:' . $c->is_nullable . PHP_EOL; }

// CMD 3: exams columns
echo PHP_EOL . "=== COMMAND 3: exams columns ===" . PHP_EOL;
$cols = DB::select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'exams' ORDER BY ordinal_position");
foreach($cols as $c) { echo $c->column_name . ' | ' . $c->data_type . ' | nullable:' . $c->is_nullable . PHP_EOL; }

// CMD 4: exam_requirements columns
echo PHP_EOL . "=== COMMAND 4: exam_requirements columns ===" . PHP_EOL;
$cols = DB::select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'exam_requirements' ORDER BY ordinal_position");
foreach($cols as $c) { echo $c->column_name . ' | ' . $c->data_type . ' | nullable:' . $c->is_nullable . PHP_EOL; }

// CMD 5: order_exam columns
echo PHP_EOL . "=== COMMAND 5: order_exam columns ===" . PHP_EOL;
$cols = DB::select("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'order_exam' ORDER BY ordinal_position");
foreach($cols as $c) { echo $c->column_name . ' | ' . $c->data_type . ' | nullable:' . $c->is_nullable . PHP_EOL; }
