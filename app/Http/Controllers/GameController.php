<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Game;
use Auth;
use App\GameSynonym;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $games = Game::get();
        return view('games/index', ['games' => $games]);
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

    /**
     * Synonyms game get
     */
    public function synonyms()
    {
        $synonyms = GameSynonym::orderByRaw('RAND()')->take(4)->get();

        return view('games/synonyms', ['synonyms' => $synonyms]);
    }

    /**
     * Synonyms game post
     */
    public function post_synonyms(Request $request)
    {
        $points = 0;

        $user = Auth::user();

        $rep1 = explode('-', $request->radio1); // 0 = the choice, 1 = the id
        $rep2 = explode('-', $request->radio2);
        $rep3 = explode('-', $request->radio3);
        $rep4 = explode('-', $request->radio4);

        $word1 = GameSynonym::find($rep1[1]);
        $word2 = GameSynonym::find($rep2[1]);
        $word3 = GameSynonym::find($rep3[1]);
        $word4 = GameSynonym::find($rep4[1]);

        $valid1 = false; $valid2 = false; $valid3 = false; $valid4 = false;

        if($rep1[0] == $word1->response) // check if response 1 is good
        {
            $valid1 = true;
            $points += 10;
        }

        if($rep2[0] == $word2->response) // check if response 2 is good
        {
            $valid2 = true;
            $points += 10;
        }

        if($rep3[0] == $word3->response) // check if response 3 is good
        {
            $valid3 = true;
            $points += 10;
        }

        if($rep4[0] == $word4->response) // check if response 3 is good
        {
            $valid4 = true;
            $points += 10;
        }

        $results = array(
            array(
                'word' => $word1->word,
                'choice' => $rep1[0],
                'response' => $word1->response,
                'valid' => $valid1
            ),
            array(
                'word' => $word2->word,
                'choice' => $rep2[0],
                'response' => $word2->response,
                'valid' => $valid2
            ),
            array(
                'word' => $word3->word,
                'choice' => $rep3[0],
                'response' => $word3->response,
                'valid' => $valid3
            ),
            array(
                'word' => $word4->word,
                'choice' => $rep4[0],
                'response' => $word4->response,
                'valid' => $valid4
            )
        );

        $user->points = $points;
        $user->save();

        return view('games/synonyms_submit', ['results' => $results, 'points' => $points]);
    }
}
