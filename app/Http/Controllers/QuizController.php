<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\QuizModel;
use App\Shared\SessionHandler;

use Illuminate\Routing\Controller as BaseController;

class QuizController extends BaseController
{

    public function startQuiz()
    {

        /* If session needs to be continued */
        if (SessionHandler::get('mode')) {
            if (!SessionHandler::get('tests')) $this->generateTests();
            else SessionHandler::put('currentQuestionIndex', SessionHandler::get('currentQuestionIndex') - 1);
            return response()->json($this->getNextQuestion());
        } /* If session is fresh */
        else {
            SessionHandler::put('mode', 'yesno');
            SessionHandler::put('correctAnswers', 0);
            $this->generateTests();

            DB::table('sessions')->insert(
                [
                    'session_id' => SessionHandler::getId(),
                    'start_time' => now(),
                    'finish_time' => now(),
                ]
            );

            return response()->json($this->getNextQuestion());
        }
    }

    public function generateTests()
    {
        $tests = QuizModel::getRandomTests();
        SessionHandler::put('tests', $tests);
        SessionHandler::put('currentQuestionIndex', 0);

        return $tests;
    }

    public function answerQuestion($ans)
    {
        $correct = $ans == QuizModel::answerCurrentQuestion($ans)->index;
        if ($correct) {
            SessionHandler::put('correctAnswers', request()->session()->get('correctAnswers') + 1);
        }

        return response()->json([
            'correct' => $correct,
            'answer' => QuizModel::answerCurrentQuestion($ans)->actual,
            'nextQuestion' => $this->getNextQuestion(request())
        ]);
    }

    public function getNextQuestion()
    {
        if (SessionHandler::get('finished')) {
            return [
                'correctAnswers' => SessionHandler::get('correctAnswers'),
                'mode' => SessionHandler::get('mode')
            ];
        } else {
            $curIndex = SessionHandler::get('currentQuestionIndex') ? SessionHandler::get('currentQuestionIndex') : 0;
            if ($curIndex >= 10) {
                SessionHandler::put('finished', true);
                return $this->submitQuiz();
            } else {
                SessionHandler::put('currentQuestionIndex', $curIndex + 1);
                $next = SessionHandler::get('tests')[$curIndex];
                $next->currentIndex = $curIndex + 1;
                $next->mode = SessionHandler::get('mode');
                unset($next->answer);
                return $next;
            }
        }
    }

    public function submitQuiz()
    {
        return QuizModel::submitQuiz();
    }

    public function restartQuiz()
    {
        $old_mode = SessionHandler::get('mode');
        SessionHandler::reset();
        SessionHandler::put('mode', $old_mode);
        return $this->startQuiz();
    }

    public function changeMode($mode)
    {
        SessionHandler::reset();
        SessionHandler::put('mode', $mode);
        return $this->startQuiz();
    }
}
