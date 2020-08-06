<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Shared\SessionHandler;

class QuizModel
{
    public static function getRandomTests()
    {
        return DB::table(config('constants.testTables.' . SessionHandler::get('mode')))->inRandomOrder()->limit(10)->get()->toArray();
    }

    public static function answerCurrentQuestion($userAnswer)
    {
        $table = config('constants.testTables.' . SessionHandler::get('mode'));
        $questionId = SessionHandler::get('tests')[SessionHandler::get('currentQuestionIndex') - 1]->id;
        $questionRow = DB::table($table)->where('id', $questionId)->first();

        $answer = $questionRow->answer;

        if (SessionHandler::get('mode') === 'yesno')
            $actual = $questionRow->answer ? '"yes"' : '"no"';
        else $actual = '"'.$questionRow->{"option".$questionRow->answer}.'"';

        DB::table('answers')->insert([
            'session_id' => SessionHandler::getId(),
            'question_type' => SessionHandler::get('mode'),
            'question_id' => $questionId,
            'user_answer' => $userAnswer,
            'correct' => $userAnswer == $answer ? true : false
        ]);
        $return_data = new \stdClass();
        $return_data->index = $answer;
        $return_data->actual = $actual;

        return $return_data;
    }

    public static function submitQuiz()
    {
        DB::table('sessions')
            ->where('session_id',SessionHandler::getId())
            ->update(
                [
                    'session_id' =>SessionHandler::getId(),
                    'finish_time' => now(),
                ]
            );

        return [
            'correctAnswers' => SessionHandler::get('correctAnswers'),
            'mode' => SessionHandler::get('mode')
        ];
    }
}
