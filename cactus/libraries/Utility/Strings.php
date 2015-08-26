<?php

namespace Utility;

class Strings {
    const PATTERN_NO_CN = '/^[^一-龥]+$/u';
    const PATTERN_CN = '/[一-龥]/u';
    const PATTERN_KR = '/[갂-줎]+|[줐-쥯]+|[쥱-짛]+|[짞-쪧]+|[쪨-쬊]+|[쬋-쭬]+|[쵡-힝]+/u';
    const PATTERN_RU = '/[А-я]+/u';
    const PATTERN_JP = '/[ぁ-ん]+|[ァ-ヴ]+/u';
    const PATTERN_TH = '/[ก-๛]+/u';
    const PATTERN_AR = '/[؟-ض]+|[ط-ل]+|[م-م]+/u';

	public static function camelize($value) {
		$value = str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
		return str_replace(' ', '/', ucwords(str_replace('/', ' ', $value)));
	}

	public static function decamelize($value, $seperator = '_') {
		return strtolower(preg_replace('/([a-z])([A-Z])/', '$1' . $seperator . '$2', trim($value)));
	}

    public static function getSegmentTime($timeString) {
        return floor(strtotime($timeString) / 1000) * 1000;
    }

	public static function relativeTime($time) {
		$now = time();
		$diff = $now - $time;
		if ($diff < 60) {
			$strTime = "{$diff}秒";
		} else if ($diff < (60 * 60)) {
			$strTime = strval(round($diff / (60))) . "分";
		} else if ($diff < (24 * 60 * 60)) {
			$strTime = strval(round($diff / (60 * 60))) . "小时";
		} else {
			$strTime = strval(round($diff / (24 * 60 * 60))) . "天";
		}
		return $strTime;
	}

    public static function zodiacKanji($time) {
        $date = getdate($time);
        $year = $date['year'];

        foreach (self::$zodiacArray as $zodiac) {
            if ($time >= strtotime($year . '-' . $zodiac['start']) && $time <= strtotime($year . '-' . $zodiac['end'] . ' 23:59:59')) {
                return $zodiac['kanji'];
            }
        }
    }

    /**
     * Predefined zodiac array
     *
     * @var array
     */
     private static $zodiacArray = array(
        array('name' => 'aries',
              'key' => 0, 'kanji' => '白羊座', 'unicode' => '♈', 'start' => '03-21', 'end' => '04-20'
        ),
        array('name' => 'taurus',
              'key' => 1, 'kanji' => '金牛座', 'unicode' => '♉', 'start' => '04-21', 'end' => '05-21'
        ),
        array('name' => 'gemini',
              'key' => 2, 'kanji' => '双子座', 'unicode' => '♊', 'start' => '05-22', 'end' => '06-21'
        ),
        array('name' => 'cancer',
              'key' => 3, 'kanji' => '巨蟹座', 'unicode' => '♋', 'start' => '06-22', 'end' => '07-22'
        ),
        array('name' => 'leo',
              'key' => 4, 'kanji' => '狮子座', 'unicode' => '♌', 'start' => '07-23', 'end' => '08-23'
        ),
        array('name' => 'virgo',
              'key' => 5, 'kanji' => '处女座', 'unicode' => '♍', 'start' => '08-24', 'end' => '09-23'
        ),
        array('name' => 'libra',
              'key' => 6, 'kanji' => '天秤座', 'unicode' => '♎', 'start' => '09-24', 'end' => '10-23'
        ),
        array('name' => 'scorpio',
              'key' => 7, 'kanji' => '天蝎座', 'unicode' => '♏', 'start' => '10-24', 'end' => '11-22'
        ),
        array('name' => 'sagittarius',
              'key' => 8, 'kanji' => '射手座', 'unicode' => ' ♐', 'start' => '11-23', 'end' => '12-21'
        ),
        array('name' => 'capricorn',
              'key' => 9, 'kanji' => '摩羯座', 'unicode' => '♑', 'start' => '12-22', 'end' => '12-31'
        ),
        array('name' => 'aquarius',
              'key' => 10, 'kanji' => '水瓶座', 'unicode' => '♒', 'start' => '01-21', 'end' => '02-19'
        ),
        array('name' => 'pisces',
              'key' => 11, 'kanji' => '双鱼座', 'unicode' => '♓', 'start' => '02-20', 'end' => '03-20'
        ),
        array('name' => 'capricorn',
              'key' => 9, 'kanji' => '摩羯座', 'unicode' => '♑', 'start' => '01-01', 'end' => '01-20'
        ),
    );
}