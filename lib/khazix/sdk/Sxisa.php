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
        $this->logger = $_DI['logger'];
        $this->logger->setChannel('sxisa sdk');

        //计算当前的年份和学期: 6月前是去年的第二学期
        $year = date('Y');
        $month = date('m');

        $this->term = $month > 6 ? 1 : 2;
        $this->year = $month > 6 ? $year :  $year - 1;
        $this->url = 'https://api.sxisa.com/nedu/timetable/';
    }

    /**
     * @param string $number 
     * @return string json
     */
    public function searchCourse($number): array
    {
        $data = [
            'number' => $number,
            'term' => $this->term,
            'year' => $this->year,
        ];

        //try three times;
        $result = $this->curl->post($this->url, $data, 'json');
        if ($result) {
            $matrix = $this->processCourse($result['data']);
            return $matrix;
        } else {
            $this->logger->print(__METHOD__, $this->curl->getErrorMsg());
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
            @$day = $dayMap[$course['sections'][0]['day']];
            @$sections = $course['sections'][0]['sections'];
            if (is_array($sections)){
                foreach ($sections as $section) {
                    $courseMatrix[$day][$section-1] = $name;
                }
            }
        }

        return $courseMatrix;
    }


}
