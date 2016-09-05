<?php namespace App\Http\Controllers;

use App\FootballModel;
use App\Http\Controllers\Controller;
use App\Logic\User\UserRepository;
use App\Logic\User\CaptureIp;
use App\Models\UsersRole;
use App\Models\Profile;
use App\Http\Requests;

use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades;
use Illuminate\Http\Request;

use Validator;
use Gravatar;
use Input;

class FootballController extends Controller {

	/**
	 * Show scores
	 *
	 * @return Response
	 */
	public function index()
	{


        dd('xxxxxxx');
        $user                   = \Auth::user();
        $users 			        = \DB::table('users')->get();
        $roles                  = \DB::table('role_user')->get();
        $total_users 	        = \DB::table('users')->count();

        $total_users_confirmed  = \DB::table('users')->count();
        $total_users_confirmed  = \DB::table('users')->where('active', '1')->count();
        $total_users_locked     = \DB::table('users')->where('resent', '>', 3)->count();

        $total_users_new        = \DB::table('users')->where('active', '0')->count();

        return view('football.show-scores', [
        		'user' 			          => $user,
                'users'                   => $users,
        		'total_users'             => $total_users,
                'total_users_confirmed'   => $total_users_confirmed,
                'total_users_locked'      => $total_users_locked,
                'total_users_new'         => $total_users_new,
                'roles'                   => $roles,
        	]
        );
	}
}
