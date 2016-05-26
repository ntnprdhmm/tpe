<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\MCQ;
use App\Question;
use App\Answer;
use App\Game;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MCQController extends Controller
{
    /**
     * get all the MCQ from the database and return the MCQ index view
     */
    public function index()
    {
        $mcq = MCQ::get();
        return view('administration/mcq/index', ["mcq" => $mcq]);
    }

    /**
     * create a new mcq
     * @param Request $request => data from mcq form create
     * @return the index view
     */
    public function createMCQ(Request $request)
    {
        if($request->name)
        {
            $mcq = new MCQ();
            $mcq->name = $request->name;
            $mcq->save();
        }

        return redirect()->action('MCQController@index');
    }

    /**
     * show all the questions of the mcq
     * @param $id => the mcq id
     * @return the mcq view
     */
    public function getMCQ($id)
    {
        $mcq = MCQ::where('id', $id)->get();

        $questions = Question::where('question.id_mcq', $id)
                        ->get();

        return view('administration/mcq/mcq', ["mcq" => $mcq[0], "questions" => $questions]);
    }

    /**
     * create a question
     * @param $id => mcq id
     * @param Request $request => form inputs
     * @return the mcq view
     */
    public function createQuestion($id, Request $request)
    {
        if($request->question)
        {
            $q = new Question();
            $q->question = $request->question;
            $q->id_mcq = $id;
            $q->save();
        }

        return redirect()->action('MCQController@getMCQ', ["id" => $id]);
    }

    /**
     * remove a question
     * @param $mcq_id
     * @param $q_id => question id
     * @return the mcq view
     */
    public function removeQuestion($mcq_id, $q_id)
    {
        Answer::where('id_question', '=', $q_id)->delete();
        Question::destroy($q_id);

        $this->setMCQPlayable($mcq_id);

        return redirect()->action('MCQController@getMCQ', ["id" => $mcq_id]);
    }

    /**
     * show all the answers for the question
     * @param $mcq_id
     * @param $q_id => question id
     * @return the answers view
     */
    public function getAnswers($mcq_id, $q_id)
    {
        $question = Question::where("id", $q_id)->get();
        $answers = Answer::where("id_question", $q_id)->get();

        return view('administration/mcq/answers', ["question" => $question[0], "answers" => $answers, "mcq_id" => $mcq_id]);
    }

    /**
     * add an answer to the question
     * @param $mcq_id
     * @param $q_id => question id
     * @return redirect to the answers view
     */
    public function addAnswer($mcq_id, $q_id, Request $request)
    {
        if($request->answer && $request->answer_correct)
        {
            $nbAnswers = Answer::where('id_question', $q_id)->count();

            if($nbAnswers < 5 ) // 5 answers max per question
            {
                $a = new Answer();
                $a->answer = $request->answer;

                if($request->answer_correct == "false")
                {
                    $a->correct = false;
                }
                else
                {
                    $a->correct = true;
                }

                $a->id_question = $q_id;
                $a->save();
            }
        }

        $this->setQuestionPlayable($q_id);
        $this->setMCQPlayable($mcq_id);

        return redirect()->action('MCQController@getAnswers', ["mcq_id" => $mcq_id, "q_id" => $q_id]);
    }

    /**
     * remove an answer
     * @param $mcq_id
     * @param $q_id => question id
     * @param $a_id => answer id
     * @return redirect to the answers view
     */
    public function removeAnswer($mcq_id, $q_id, $a_id)
    {
        Answer::destroy($a_id);

        $this->setQuestionPlayable($q_id);
        $this->setMCQPlayable($mcq_id);

        return redirect()->action('MCQController@getAnswers', ["mcq_id" => $mcq_id, "q_id" => $q_id]);
    }

    /**
     * remove a mcq from the database
     * @param $id
     * @return mcq view
     */
    public function removeMCQ($id)
    {
        MCQ::destroy($id);

        return redirect()->action('MCQController@index');
    }

    /**
     * @param $id => mcq id
     * @return the game view
     */
    public function playMCQ($id)
    {
        $mcq = MCQ::where("id", $id)->get();

        $questions = Question::where('id_mcq', $id)->with('answers')->get();

        return view('games/mcq', ['mcq' => $mcq, 'questions' => $questions]);
    }

    public function postMCQ($id, Request $request)
    {
        $games = Game::get();
        $mcq = MCQ::where("playable", true)->get(); // get all playable mcq
        $points = 0;

        // question 1
        $answer = Answer::where('id_question', $request->q0)->where('correct', true)->first();

        if($answer['answer'] == $request->rep1)
        {
            $points++;
        }

        // question 2
        $answer = Answer::where('id_question', $request->q1)->where('correct', true)->first();

        if($answer['answer'] == $request->rep2)
        {
            $points++;
        }

        // question 1
        $answer = Answer::where('id_question', $request->q2)->where('correct', true)->first();

        if($answer['answer'] == $request->rep3)
        {
            $points++;
        }

        $message = "you win ".$points." points !";

        return view('games/index', ["message" => $message, "mcq" => $mcq, "games" => $games]);
    }

    /**
     * set the playable attribute of the mcq
     * @param $id => mcq id
     */
    private function setMCQPlayable($id)
    {
        $playable = false;

        $nbQuestions = Question::where('id_mcq', $id)
                            ->count();

        if($nbQuestions > 2)
        {
            $playable = true;
        }

        $mcq = MCQ::find($id);
        if($mcq->playable != $playable)
        {
            $mcq->playable = $playable;
            $mcq->save();
        }
    }

    /**
     * set the playable attribute of the question
     * @param $id => question id
     */
    private function setQuestionPlayable($id)
    {
        $playable = false;

        $nbAnswers = Answer::where('id_question', $id)
                        ->count();

        if($nbAnswers > 1)
        {
            $nbCorrect = Answer::where('id_question', $id)
                            ->where('correct', true)
                            ->count();

            if($nbCorrect > 0)
            {
                // the question is playable ( it respects all the conditions to be playable )
                $playable = true;
            }

        }

        $question = Question::find($id);
        if($question->playable != $playable)
        {
            $question->playable = $playable;
            $question->save();
        }
    }
}
