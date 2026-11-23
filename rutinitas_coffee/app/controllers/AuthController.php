<?php
class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) $this->redirect('dashboard');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->csrf();
            $v = Validator::make($_POST, ['username' => 'required', 'password' => 'required'], ['username' => 'Username', 'password' => 'Password']);
            if ($v->fails()) {
                flash('error', implode(' ', $v->errors()));
            } elseif (Auth::attempt(trim($_POST['username']), $_POST['password'])) {
                $this->redirect('dashboard');
            } else {
                flash('error', 'Username atau password salah.');
            }
        }
        $this->view('auth/login');
    }

    public function logout(): void
    {
        $this->csrf();
        Auth::logout();
        $this->redirect('auth/login');
    }
}
