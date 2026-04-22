App\Domains\Patients\Models\Patient::select('id','first_name','last_name','ci')->take(5)->get()->each(function($p){ echo $p->id.' | '.$p->first_name.' '.$p->last_name.' | CI:'.$p->ci.PHP_EOL; });
