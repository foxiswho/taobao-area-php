<?php
/**
 * Created by PhpStorm.
 * User: fox
 * Date: 2018/1/11
 * Time: 下午1:59
 */

class makeJsData
{
    private $path     = '';
    private $city     = [];
    private $city_ext = [];

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = trim($path);
    }

    /**
     * @param array $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @param array $city_ext
     */
    public function setCityExt($city_ext)
    {
        $this->city_ext = $city_ext;
    }

    public function process()
    {
        $city = '';
        if ($this->city) {
            $data = [];
            foreach ($this->city as $item) {
                $add                        = [];
                $add[]                      = $item['id'];
                $add[]                      = $item['name'];
                $add[]                      = $item['parent_id'];
                $add[]                      = (int)$item['type'];
                $add[]                      = trim($item['type_name']);
                $add[]                      = trim($item['other_name']);
                $data[$item['parent_id']][] = $add;
            }
            if ($this->city_ext) {
                foreach ($this->city_ext as $item) {
                    $add                        = [];
                    $add[]                      = $item['id'];
                    $add[]                      = $item['name'];
                    $add[]                      = $item['parent_id'];
                    $add[]                      = (int)$item['type'];
                    $add[]                      = trim($item['type_name']);
                    $add[]                      = trim($item['other_name']);
                    $data[$item['parent_id']][] = $add;
                }
            }
            $city = json_encode($data, JSON_UNESCAPED_UNICODE);
            $city = ltrim($city, '{');
            $city = rtrim($city, '}');
        }
        $time = date('Y-m-d H:i:s');
        $str  = <<<EOF
/* 
 * Auto Make Date: {$time}
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as anonymous module.
        define('ChineseDistricts', [], factory);
    } else {
        // Browser globals.
        factory();
    }
})(function () {
    var ChineseDistricts = {"1":{"A-G":[[110000,"北京",1,0,"",""],[340000,"安徽",1,0,"",""],[350000,"福建",1,0,"",""],[440000,"广东",1,0,"",""],[450000,"广西",1,0,"",""],[500000,"重庆",1,0,"",""],[520000,"贵州",1,0,"",""],[620000,"甘肃",1,0,"",""],[820000,"澳门",1,0,"",""]],"H-K":[[130000,"河北",1,0,"",""],[220000,"吉林",1,0,"",""],[230000,"黑龙江",1,0,"",""],[320000,"江苏",1,0,"",""],[360000,"江西",1,0,"",""],[410000,"河南",1,0,"",""],[420000,"湖北",1,0,"",""],[430000,"湖南",1,0,"",""],[460000,"海南",1,0,"",""]],"L-S":[[140000,"山西",1,0,"",""],[150000,"内蒙古",1,0,"",""],[210000,"辽宁",1,0,"",""],[310000,"上海",1,0,"",""],[370000,"山东",1,0,"",""],[510000,"四川",1,0,"",""],[610000,"陕西",1,0,"",""],[630000,"青海",1,0,"",""],[640000,"宁夏",1,0,"",""]],"T-Z":[[120000,"天津",1,0,"",""],[330000,"浙江",1,0,"",""],[530000,"云南",1,0,"",""],[540000,"西藏",1,0,"",""],[650000,"新疆",1,0,"",""],[710000,"台湾",1,0,"",""],[810000,"香港",1,0,"",""]]},{$city}}
;
    if (typeof window !== 'undefined') {
        window.ChineseDistricts = ChineseDistricts;
    }
    return ChineseDistricts;
});
EOF;
        //写入存储文件
        file_put_contents($this->path . 'city-picker.data.js', $str);
        echo "make js data  Success  ";
        return true;
    }
}