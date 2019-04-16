<?php

namespace app\commands;

use yii\base\InvalidConfigException;
use yii\console\{Controller, ExitCode};

class ParseController extends Controller
{
    /**
     * @param string $del
     * @param string $task
     * @return int Exit code
     * @throws InvalidConfigException
     */
    public function actionStart(string $del = 'semicolon', string $task = 'countAverageLineCount') : int
    {
        $parser = new Parser($del, $task);
        $parser->process();

        return ExitCode::OK;
    }
}
