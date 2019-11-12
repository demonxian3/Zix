<?php

namespace Khazix\Sdk;

/** 
 *
 * 文档地址: https://timetable.m.sxisa.com/ 
 *
 * 创智俱乐部提供的一些服务：如学号查询课表，查询成绩等等
 *
 */

class Sxisa
{
    /** 学期 */
    private $term;

    /** 学年 */
    private $year;

    /** 学号 */
    private $number;


    public function __construct()
    {
        global $_DI;

        $this->curl = $_DI['curl'];
        $this->redis = $_DI['redis'];
        $this->logger = $_DI['logger'];

        $this->logger->setChannel('sxisa sdk');
        $config = $_DI['config']->get('sxisa');

        $this->url = 'https://api.sxisa.com/nedu/timetable/';
        $this->term = $config['term'];
        $this->year = $config['year'];
    }

    /**
     * @param string $number 
     * @return string json
     */
    public function searchCourse($number): array
    {

        $redisKey = 'course_table_' . $number;


        if ($courses = $this->redis->get($redisKey)) {
            return json_decode($courses, true);
        }

        $data = [
            'number' => $number,
            'term' => $this->term,
            'year' => $this->year,
        ];

        $this->curl->post($this->url, $data, 'json');
        var_dump($this->curl->result);

        if ($this->curl->result) {

            $this->logger->print(__METHOD__, $this->curl->result);

            $matrix = $this->processCourse($this->curl->result['data']);

            $this->redis->set($redisKey, json_encode($matrix));

            return $matrix;
        }

        return [];

    }

    private function processCourse(?array $courses): array
    {
        $dayMap = [
            '日' => '0',
            '一' => '1',
            '二' => '2',
            '三' => '3',
            '四' => '4',
            '五' => '5',
            '六' => '6',
        ];

        $courseMatrix = [
            ['', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', ''],
        ];

        if (NULL === $courses) return $courseMatrix;

        foreach ($courses as $course) {
            $name = $course['name'];
            $day = $dayMap[$course['sections'][0]['day']];
            $sections = $course['sections'][0]['sections'];

            foreach ($sections as $section) {
                $courseMatrix[$day][$section-1] = $name;
            }

        }

        return $courseMatrix;
    }


}
