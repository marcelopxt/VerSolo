<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.loginRegister');
    }

    public function register(Request $request)
    {
        $messages = [
            'password.confirmed' => 'As senhas informadas devem ser iguais. Tente novamente.',
            'password.min' => 'A senha deve ter no mínimo :min caracteres.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'access_code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], $messages);

        if ($validator->fails()) {
            throw (new ValidationException($validator))
                ->errorBag('register')
                ->redirectTo(route('login'));
        }

        $company = Company::where('access_code', $request->access_code)->first();

        if (!$company) {
            throw ValidationException::withMessages([
                'access_code' => 'Código de acesso inválido.',
            ])->errorBag('register');
        }

        $employee = Employee::create([
            'name' => $request->name,
            'password' => $request->password,
            'company_id' => $company->id,
        ]);

        Auth::guard('employee')->login($employee);

        return redirect()->route('dashboard')->with('success', 'Cadastro realizado com sucesso! Bem-vindo(a)!');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('employee')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard')->with('success', 'Bem-vindo(a) de volta!');
        }

        throw ValidationException::withMessages([
            'name' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}