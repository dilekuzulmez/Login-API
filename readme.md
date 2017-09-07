<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

## Bu Repo Hakkında
 
 Bu projede Laravel Socialite kullanılarak login işlemi gerçekleştirilen bir uygulamanın Login-Register Tokenlarının nasıl alınacağı ile ilgilidir.
 
  Takip edebileceğiniz ilgili kaynaklar:

- [tymon/jwt-auth ](https://github.com/tymondesigns/jwt-auth).
- [Video ==Api Authentication in laravel using Jwt](https://www.youtube.com/watch?v=O-hVQG3_W6k).


## jwt-auth Kurulumu

- [Kurulum](https://github.com/tymondesigns/jwt-auth/wiki/Installation) 

Ben kendi projemdeki adımları anlatacağım. :blush:

- PHP 5.4 + için adımlar aşağıdaki şekildedir : 

composer ile jwt-auth yükleyebiliriz ya da [composer.json](https://github.com/dilekuzulmez/Login-API/blob/master/composer.json#L12) dosyasını düzenleyebiliriz.
<pre><code>
$ composer require tymon/jwt-auth
</code></pre>

<pre><code>
"require": {
    "tymon/jwt-auth": "^0.5.*"
}
</code></pre>

[app.php](https://github.com/dilekuzulmez/Login-API/blob/master/config/app.php#L181) dosyasına giderek, *providers* kısmına aşağıdaki satırı ekliyoruz:
<pre><code>
Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class, /* for Token */
</code></pre>

[app.php](https://github.com/dilekuzulmez/Login-API/blob/master/config/app.php#L233) dosyasında *aliases* kısmına giderek aşağıdaki satırı ekliyoruz.
<pre><code>
'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class, /* for Token */ 
</code></pre>

Aşağıdaki komutu terminalde(konsolda) çalıştırıyoruz:
<pre><code>
$ php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider"
</code></pre>

Aşağıdaki bildirim konsolda görülecektir. [config](https://github.com/dilekuzulmez/Login-API/blob/master/config/jwt.php) klasörümüzün altında *jwt.php* dosyası oluşmuş oldu.
<pre><code>
Copied File [/vendor/tymon/jwt-auth/src/config/config.php] To [/config/jwt.php]
Publishing complete.
</code></pre>

*Don't forget to set a secret key in the config file!*
Gizli anahtarımızı oluşturmayı unutmuyoruz! :blush:
[key](https://github.com/dilekuzulmez/Login-API/blob/master/config/jwt.php#L24) oluşturmak için yardımcı komut aşağıdaki şekildedir:
<pre><code>
$ php artisan jwt:generate
</code></pre>


## Kod Kısmında Neler Yapacağız ?

Öncelikle Login ve Register işlemimizi Socialite ile yaptığımız için uygulamada herhangi bir parola oluşturulmuyor. Parola(password) kısmı nullable() idi. Boş olabiliyordu yani. Şimdi token alırken parolaya ihtiyacımız olduğu için

[database/migrations/2014_10_12_000000_create_users_table.php](https://github.com/dilekuzulmez/Login-API/blob/master/database/migrations/2014_10_12_000000_create_users_table.php#L20) dosyamıza giderek,
<pre><code>
$table->string('password')->*nullable()*;
</code></pre> *nullable()* kısmını kaldırıyoruz. Kaldırmasakta Controller'a yazacağımız kod ile parola oluşuyor yine de nullable olmamasını tercih ettim.

[app/Http/Controllers/Auth/AuthController.php](https://github.com/dilekuzulmez/Login-API/blob/master/app/Http/Controllers/Auth/AuthController.php#L67) dosyamıza giderek
<pre><code>
 'password' => bcrypt(time()), 
</code></pre> satırını ekliyoruz. Böylece kullanıcı Kayıt olduğunda veritabanında bir parola oluşturulmuş olacaktır.


[app/Http/Controllers/Auth/AuthController.php](https://github.com/dilekuzulmez/Login-API/blob/master/app/Http/Controllers/Auth/AuthController.php) dosyamıza giderek
<pre><code>
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
</code></pre>

ve

<pre><code>
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
</code></pre>

[app/Http/Controllers/Auth/RegisterController.php](https://github.com/dilekuzulmez/Login-API/blob/master/app/Http/Controllers/Auth/RegisterController.php) dosyamıza giderek
<pre><code>
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
</code>

<code>
public function __construct()
{
  // $this->middleware('guest');
}
</code></pre>

<pre><code>
public function register(Request $request)
{
        // grab credentials from the request
        $user = User::where('email', '=', $request->get('email'))->first();
        // dd($credentials);
        try {
           $token = JWTAuth::fromUser($user);
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
</code></pre>

[app/Http/Kernel.php](https://github.com/dilekuzulmez/Login-API/blob/master/app/Http/Kernel.php#L35) dosyasına giderek
<pre><code>
// \App\Http\Middleware\VerifyCsrfToken::class, 
</code></pre> yorum satırı yapıyoruz.

[routes/web.php](https://github.com/dilekuzulmez/Login-API/blob/master/routes/web.php) dosyasına giderek yönlendirmeleri yapıyoruz.
<pre><code>
Route::post('register', 'Auth\RegisterController@register');
Route::post('login', 'Auth\AuthController@authenticate'); 
</code></pre>

## Postman

Bilgisayarımıza Postman indirerek.
POST isteğini http://localhost:8000/register ve http://localhost:8000/login sayfalarına yaparak, body kısmında ilgili yerleri doldurarak,
Formatı JSON olarak seçtiğimizde aşağıdaki tarzda bir çıktı almamız gerekmektedir.
<pre><code>
{"status":true,"data":{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjIsImlzcyI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9yZWdpc3RlciIsImlhdCI6MTUwNDc3NjU4NCwiZXhwIjoxNTA0NzgwMTg0LCJuYmYiOjE1MDQ3NzY1ODQsImp0aSI6IkJnenNzV0RzQ0pUMVpHSXUifQ.CNhluN4fTA8Shs0_xRRvfmhlL4j4IXTrF52CYBjVA_8"}}
</code></pre>

## Lisans

Laravel framework'u, lisanslı açık kaynaklı yazılımdır. [MIT license](http://opensource.org/licenses/MIT).
