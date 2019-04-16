<?php

namespace app\commands;

use yii\console\Exception;

class Parser
{
    /**
     * @var string Delimiter
     */
    public $del;

    /**
     * @var string Task type
     */
    public $task;

    const peoplePath = __DIR__ . '/files/people.csv';
    const textsPath = __DIR__ . '/files/texts';
    const outputPath = __DIR__ . '/files/output_texts';

    /**
     * Parser constructor.
     * @param string $del
     * @param string $task
     * @throws Exception
     */
    public function __construct(string $del, string $task)
    {
        if (!in_array($del, self::getDelimiters()) || !in_array($task, self::getTaskTypes())) {
            throw new Exception('Not valid separator or task type');
        }

        $this->del = $del === 'comma' ? ',' : ';';
        $this->task = $task;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function process() : void
    {
        // если файлы не большие предпочтительнее file_get_contents
        $peoplePath = __DIR__ . '/files/people.csv';


        if ($f = fopen($peoplePath, 'r')) {
            $this->task === 'countAverageLineCount' ? $this->countAverage($f) : $this->replaceDates($f);
        }
    }

    /**
     * @param resource $f
     * @return void
     * @throws Exception
     */
    public function countAverage($f) : void
    {
        print('Average count lines: ' . PHP_EOL);

        while ($people = fgetcsv($f,   100, $this->del)) {
            $sum = 0;
            $counter = 0;

            if (!array_key_exists(1, $people)) {
                throw new Exception('The delimiter cannot split the string');
            }

            $username = $people[1];

            foreach (glob( self::textsPath . '/' . $people[0] . '-*.txt') as $file) {
                $sum += sizeof(file($file));
                $counter++;
            }

            printf('%s - %d' . PHP_EOL, $username , $sum/$counter);
        }
    }

    /**
     * @param resource $f
     * @return void
     */
    public function replaceDates($f) : void
    {
        print('Start replace dates: ' . PHP_EOL);

        while ($people = fgetcsv($f,   100, $this->del)) {
            $count = 0;

            foreach (glob(self::textsPath . '/' . $people[0] . '-*.txt') as $file) {
                $text = file_get_contents($file);
                $pattern = '/(3[01]|[12][0-9]|0?[1-9])\/(1[012]|0?[1-9])\/(\d{2})/';

                $c = 0;

                $text = preg_replace_callback($pattern, function ($match) {
                    if ($match[3] > 20) {
                        $mil = 19;
                    } else {
                        $mil = 20;
                    }

                    $year = $mil * 100 + $match[3];
                    $date = mktime(0, 0, 0, (int) $match[2], (int) $match[1], $year);

                    return date('d-m-Y', $date);
                }, $text, -1, $c);

                $count += $c;
                $path = explode('/', $file);
                $name = $path[count($path) - 1];
                $newFile = fopen(self::outputPath . '/' . $name, "w");

                fwrite($newFile, $text);
            }

            printf('Replaced count: %d for user: %s ' . PHP_EOL, $count , $people[1]);
        }
    }

    /**
     * @return array
     */
    public static function getDelimiters() : array
    {
        return [
            'comma', 'semicolon'
        ];
    }

    /**
     * @return array
     */
    public static function getTaskTypes() : array
    {
        return [
            'countAverageLineCount', 'replaceDates'
        ];
    }
}