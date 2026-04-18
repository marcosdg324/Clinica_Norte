use App\Domains\Auth\Models\Role;
foreach(Role::with('permissions')->get() as $r) {
    echo $r->name . ': ' . $r->permissions->count() . ' permisos' . PHP_EOL;
}
