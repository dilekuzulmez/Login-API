<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\User;
use Auth;
use Socialite; /*for Socialite */

class AuthController extends Controller
{
    /**
     * Kullanıcıyı OAuth Providera yönlendiriyoruz.
     * @return Response
     */

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Kullanıcı bilgilerini sağlayıcıdan(provider) ediniyoruz. Kullanıcının zaten var olup olmadığını kontrol ediyoruz.
     * Veritabanında provider_id var mı bakıyoruz.
     * Kullanıcı mevcutsa, mevcut olan kaydı döndürüyoruz. Aksi halde, yeni bir kullanıcı oluşturtup daha sonra bu kullanıcı ile giriş yaptırıyoruz. Bundan sonra
     * Kimliği doğrulanmış kullanıcının ana sayfasına yönlendiriyoruz.
     * @return Response
     */

    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();

        //dd($user->token);

        $authUser = $this->findOrCreateUser($user, $provider);
        Auth::login($authUser, true);
        return redirect('/home');
    }



    /**
     *Eğer bu üye daha önce sosyal medya hesaplarından biri ile giriş                 
     *yapmamışsa yeni bir üye oluşturuyoruz, eğer kaydı varsa ilgili kaydı bulup
     *kullanıcıyı login ediyoruz
     * @param  $user Socialite kullanıcısı nesnesi
     * @param $provider Social kimlik doğrulama sağlayıcısı
     * @return  User
     */
    public function findOrCreateUser($user, $provider)
    {
        $authUser = User::where('provider_id', $user->id)->first();

        if ($authUser) {
            return $authUser;
        }

        $newUser = User::create([
            'name'     => $user->name,
            'email'    => $user->email,
            'password' => bcrypt(time()),
            'provider' => $provider,
            'provider_id' => $user->id,
        ]);

        return $newUser;
    }

    public function authenticate(Request $request)
    {
        // grab credentials from the request
        $user = User::where('email', '=', $request->get('email'))->first();
       // dd($credentials);
        try {
            $token = JWTAuth::fromUser($user); /* modelden alıyor direkt */
            // attempt to verify the credentials and create a token for the user
            if (! $token ) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(['status' => true, 'data' => compact('token')]);
    }

}