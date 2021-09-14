<?php

namespace App\Http\Controllers;

use App\Events\DiscordConnectionUpdated;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class DiscordConnectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        abort_if($request->has('error'), 428, $request->get('error_description'));

        try {
            $credentials = Socialite::driver('discord')->user();
        } catch (InvalidStateException $exception) {
            return redirect()->to('/connections/discord/authorize');
        }

        $existingUser = User::query()
            ->where('id', '!=', $request->user()->id)
            ->where('discord_api_id', $credentials->id)
            ->first();

        if ($existingUser) {
            $existingUser->discord_api_id = null;
            $existingUser->discord_api_nickname = null;
            $existingUser->discord_api_access_token = null;
            $existingUser->discord_api_refresh_token = null;
            $existingUser->save();

            DiscordConnectionUpdated::dispatch($existingUser);
        }

        $user = $request->user();
        $user->discord_api_id = $credentials->id;
        $user->discord_api_nickname = $credentials->getNickname();
        $user->discord_api_access_token = $credentials->token;
        $user->discord_api_refresh_token = $credentials->refreshToken;
        $user->save();

        DiscordConnectionUpdated::dispatch($user);

        return redirect()->to('https://discord.com/channels/592327939920494592/592327939920494594');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return Socialite::driver('discord')
            ->setScopes(['identify'])
            ->redirect();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
