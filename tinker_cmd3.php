use Illuminate\Support\Facades\Hash;
use App\Models\User;
$u = User::where('email','admin@clinicanorte.com')->first();
echo 'Password valid: ' . (Hash::check('Admin@2026!', $u->password) ? 'YES' : 'NO') . PHP_EOL;
