<?php

namespace app\commands;

use yii\console\{
    Controller, Exception, ExitCode
};

class ParseController extends Controller
{
    /**
     * @param string $del
     * @param string $task
     * @return int Exit code
     * @throws Exception
     */
    public function actionStart(string $del = 'semicolon', string $task = 'countAverageLineCount') : int
    {
        $parser = new Parser($del, $task);
        $parser->process();

        return ExitCode::OK;
    }
}
