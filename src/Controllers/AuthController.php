<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    protected string $prefixTheme = 'theme-vinahentai';

    public function showLogin(Request $request)
    {
        $redirect = (string) $request->query('redirect', '');
        if ($redirect !== '') {
            $request->session()->put('url.intended', $redirect);
        }

        SEOTools::setTitle('Đăng nhập - '.env('APP_NAME'));
        SEOTools::setDescription('Đăng nhập '.env('APP_NAME').' để tiếp tục đọc, theo dõi và lưu truyện 18+ yêu thích. Trải nghiệm mượt, ít quảng cáo.');
        SEOMeta::addKeyword(active_theme_config('seo_home_keywords', ''));
        SEOTools::opengraph()->addProperty('type', 'website');
        SEOTools::opengraph()->addImage(active_theme_config('seo_home_image', ''));

        return view($this->prefixTheme.'::auth.login');
    }

    public function showRegister(Request $request)
    {
        $redirect = (string) $request->query('redirect', '');
        if ($redirect !== '') {
            $request->session()->put('url.intended', $redirect);
        }

        SEOTools::setTitle('Đăng ký - '.env('APP_NAME'));
        SEOTools::setDescription('Đăng ký '.env('APP_NAME').' để tiếp tục đọc, theo dõi và lưu truyện 18+ yêu thích. Trải nghiệm mượt, ít quảng cáo.');
        SEOMeta::addKeyword(active_theme_config('seo_home_keywords', ''));
        SEOTools::opengraph()->addProperty('type', 'website');
        SEOTools::opengraph()->addImage(active_theme_config('seo_home_image', ''));

        return view($this->prefixTheme.'::auth.register');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'Tài khoản hoặc mật khẩu không chính xác',
        ])->withInput($request->only('email', 'remember'));
    }

    public function postRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Tên người dùng là bắt buộc',
            'name.max' => 'Tên người dùng không được vượt quá 15 ký tự',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Tài khoản đã tồn tại',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'password.confirmed' => 'Mật khẩu không khớp',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    // ===== GOOGLE =====
    public function redirectGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogle()
    {
        $socialUser = Socialite::driver('google')->user();

        $user = User::where('google_id', $socialUser->id)->first();

        if (! $user && $socialUser->email) {
            $user = User::where('email', $socialUser->email)->first();

            if ($user) {
                $user->google_id = $socialUser->id;
                $user->timestamps = false;
                $user->saveQuietly();
                $user->timestamps = true;
            }
        }

        if (! $user) {
            $user = User::create([
                'name' => $socialUser->name ?? 'Google User',
                'email' => $socialUser->email,
                'google_id' => $socialUser->id,
                'avatar' => $socialUser->avatar,
                'password' => bcrypt(uniqid()),
            ]);
        }

        Auth::login($user);

        return redirect()->route('home');
    }

    // ===== DISCORD =====
    public function redirectDiscord()
    {
        return Socialite::driver('discord')->redirect();
    }

    public function handleDiscord()
    {
        $socialUser = Socialite::driver('discord')->user();

        $user = User::where('discord_id', $socialUser->id)->first();

        if (! $user && $socialUser->email) {
            $user = User::where('email', $socialUser->email)->first();

            if ($user) {
                $user->discord_id = $socialUser->id;
                $user->timestamps = false;
                $user->saveQuietly();
                $user->timestamps = true;
            }
        }

        if (! $user) {
            $user = User::create([
                'name' => $socialUser->name,
                'email' => $socialUser->email ?? $socialUser->id.'@discord.local',
                'discord_id' => $socialUser->id,
                'avatar' => $socialUser->avatar,
                'password' => bcrypt(uniqid()),
            ]);
        }

        Auth::login($user);

        return redirect()->route('home');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->back();
    }
}
