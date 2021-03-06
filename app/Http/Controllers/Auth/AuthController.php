<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateUserHandle;
use App\Models\User;
use App\Jobs\UpdateUserAvatar;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use InvalidArgumentException;
use Socialite;
use Validator;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    protected $loginPath = '/';

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    // http://goodheads.io/2015/08/24/using-twitter-authentication-for-login-in-laravel-5/
    // protected $redirectPath = '/home';

    /**
     * Redirect the user to the Twitter authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('twitter')->redirect();
    }

    /**
     * Obtain the user information from Twitter.
     *
     * @return User
     */
    private function handleProviderCallback()
    {
        try {
            $user = Socialite::driver('twitter')->user();
        } catch (InvalidArgumentException $e) {
            return [redirect('/'), false];
        } catch (\Exception $e) {
            return [redirect('auth/twitter'), false];
        }

        list($authUser, $created) = $this->findOrCreateUser($user);

        Auth::login($authUser, true);

        return [$authUser, $created];
    }

    /**
     * Obtain the user information from Twitter and redirect.
     *
     * @return Response
     */
    public function handleProviderCallbackAsRedirect()
    {
        list($user, $created) = $this->handleProviderCallback();

        if (Auth::check()) {
            if ($created) {
                return redirect()->route('settings');
            }
            return redirect()->route('profile', ['handle' => $user->handle]);
        } else {
            return redirect('/');
        }
    }

    /**
     * Obtain the user information from Twitter and return Json.
     *
     * @return Json
     */
    public function handleProviderCallbackAsJson(Request $request)
    {
        list($user, $created) = $this->handleProviderCallback();

        if (Auth::check()) {
            $token = $request->session()->get('_token');
            if ($created) {
                $arr = array('success' => true, 'token' => $token, 'created' => true);
            } else {
                $arr = array('success' => true, 'token' => $token, 'created' => false);
            }
        } else {
            $arr = array('error' => 'login failed');
        }
        return json_encode($arr);
    }

    /**
     * Return user if exists; create and return if doesn't
     *
     * @param $twitterUser
     * @return User
     */
    private function findOrCreateUser($twitterUser)
    {
        $authUser = User::where('twitter_id', $twitterUser->id)->first();
        $nickUser = User::where('handle', $twitterUser->nickname)->first();

        if ($authUser) {
            if ($twitterUser->nickname != $authUser->handle) {
                if ($nickUser) {
                    $nickUser->handle = $nickUser->generateUniqueHandler();
                    $nickUser->save();
                    // update user handle asynchronously
                    $this->dispatch(new UpdateUserHandle($nickUser));
                }
                $authUser->handle = $twitterUser->nickname;
                $authUser->save();
            }
            return [$authUser, false];
        }

        if ($nickUser) {
            $nickUser->handle = $nickUser->generateUniqueHandler();
            $nickUser.save();
            // update user handle asynchronously
            $this->dispatch(new UpdateUserHandle($nickUser));
        }
        
        $url = '';
        if (isset($twitterUser->user['entities']['url']['urls'][0]['expanded_url'])) {
            $url = $twitterUser->user['entities']['url']['urls'][0]['expanded_url'];
        };

        $user = User::create([
            'name' => $twitterUser->name,
            'handle' => $twitterUser->nickname,
            'twitter_id' => $twitterUser->id,
            'avatar' => $twitterUser->avatar_original,
            'url' => $url
        ]);
        
        $this->dispatch(new UpdateUserAvatar($user));
        
        return [$user, true];
    }

    /**
     * Logs user out
     *
     * @return Response
     */
    public function logout()
    {
        Auth::logout();

        return redirect('/');
    }
}
