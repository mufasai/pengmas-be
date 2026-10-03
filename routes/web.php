<?php 
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// halaman login (GET)
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

// proses login (POST)
Route::post('/login', function (Request $request) {

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect('/dashboard');
    }

    return back()->with('error', 'Email atau password salah!');
}); 
Route::get('/dashboard', function () {
    return "Selamat datang di dashboard kasir!";
})->middleware('auth'); 