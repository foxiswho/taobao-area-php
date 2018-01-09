<?php

class area
{
    private $url        = '';
    private $file_name  = 'taobao-area.js';
    private $is_country = true;
    private $make_csv   = false;
    private $make_sql   = true;
    private $ext_data   = [];
    //省
    private $province = [];
    //市
    private $area = [];
    //香港澳门
    private $gang_ao = [];
    //台湾
    private $taiwan = [];
    //马来西亚
    private $ma_lai_xi_ya = [];
    //其他国家
    private $other_countries = [];
    //省市区
    private $province_city = [];
    //扩展
    private $province_city_ext = [];

    /**临时目录
     * @return string
     */
    private function getTmpPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
    }

    /**传入 URL
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = trim($url);
    }

    /**是否显示国家
     * @param bool $is_country
     */
    public function setIsCountry($is_country)
    {
        $this->is_country = $is_country ? true : false;
    }

    /**
     * 下载文件
     */
    private function downLoadUrl()
    {
        $path = $this->getTmpPath();
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                exit("目录创建失败，没有此权限");
            }
        }
        //获取远程文件内存
        $string = file_get_contents($this->url);
        //写入存储文件
        file_put_contents($path . $this->file_name, $string);
    }

    /** 生成 csv文件
     * @param bool $make_csv
     */
    public function setMakeCsv($make_csv)
    {
        $this->make_csv = $make_csv ? true : false;
    }

    /**生成 sql文件
     * @param bool $make_sql
     */
    public function setMakeSql($make_sql)
    {
        $this->make_sql = $make_sql ? true : false;
    }

    /**传入扩展数据
     * @param array $ext_data
     */
    public function setExtData($ext_data)
    {
        $this->ext_data = $ext_data;
    }

    /**
     * 格式化 获取内容
     */
    private function getJsContent()
    {
        $string = file_get_contents($this->getTmpPath() . $this->file_name);
        $area   = explode("\n", $string);
        //        echo $area[0];
        //获得 除香港澳门台湾的省市区
        //preg_match_all("/.*A-G(.*)/",rtrim($area[0],";"),$matches);
        $matches = explode('"A-G"', rtrim($area[0], ";"));
        $matches = explode(';return t=e}(),r=function(t){var e=', $matches[1]);
        //整合
        $this->province = json_decode('{"A-G"' . $matches[0], true);
        $this->area     = json_decode($matches[1], true);
        //        print_r($this->province);
        //        print_r($this->area);
        //获取其他
        $matches = explode("return t=e}(),h=function(e)", $area[1]);
        //统一 标志 ，方便转换成数组
        $str = preg_replace("/,[a-zA-Z]=function/", ",o=function", ltrim($matches[0], "return t=e}(),o=function(t){var e="));
        //转换成数组
        $other = explode(";return t=e}(),o=function(t){var e=", $str);
        //        print_r($other);
        //香港和澳门
        $this->gang_ao = json_decode($other[0], true);
        //台湾
        $this->taiwan = json_decode($other[1], true);
        //马来西亚
        $this->ma_lai_xi_ya = json_decode($other[2], true);
        //他国家
        $this->other_countries = json_decode(rtrim($other[3], ';'), true);
    }

    /**
     * 处理省
     */
    private function processProvince()
    {
        foreach ($this->province as $key => $val) {
            foreach ($val as $item) {
                $add                     = [];
                $add['id']               = $item[0];//ID
                $add['name']             = $item[1][0];//名称
                $add['name_traditional'] = $item[1][1];//繁体名称
                $add['parent_id']        = $item[2];
                $add['type']             = 0;
                $add['type_name']        = '';//类别名称
                $add['other_name']       = '';//类别名称
                $add['name_format']      = '';//格式化全称
                $this->province_city[]   = $add;
            }

        }
    }

    /**
     * 处理市
     */
    private function processArea()
    {
        $html = '';
        $ext  = '';
        foreach ($this->area as $val) {
            if (strpos($val[0], ',') !== false) {
                $id   = explode(',', $val[0]);
                $type = '属于';
                if (preg_match("/(.*)\s属于\s(.*)，(.*)/iu", $val[1][0], $name)) {
                    preg_match("/(.*)\s屬於\s(.*)，(.*)/iu", $val[1][1], $Traditional);
                } else {
                    preg_match("/(.*)\s(.*)，(.*)/iu", $val[1][0], $name);
                    preg_match("/(.*)\s(.*)，(.*)/iu", $val[1][1], $Traditional);
                }
                $name[2]        = str_replace('(', '', $name[2]);
                $name[3]        = str_replace(')', '', $name[3]);
                $Traditional[2] = str_replace('(', '', $Traditional[2]);
                $Traditional[3] = str_replace(')', '', $Traditional[3]);
                //$ext                     .= "$id[0]\t{$name[1]}\t{$Traditional[1]}\t$val[2]\t$val[3]\t$type\t{$name
                //[2]}\n";
                //$ext                     .= "$id[1]\t{$name[1]}\t{$Traditional[1]}\t$val[2]\t$val[3]\t$type\t{$name
                //[3]}\n";
                $add                       = [];
                $add['id']                 = $id[0];//ID
                $add['name']               = $name[1];//名称
                $add['name_traditional']   = $Traditional[1];//繁体名称
                $add['parent_id']          = $val[2];
                $add['type']               = $val[3];
                $add['type_name']          = $type;//类别名称
                $add['other_name']         = $name[2];//别称
                $add['name_format']        = $val[1][0];//格式化全称
                $this->province_city_ext[] = $add;
                $add                       = [];
                $add['id']                 = $id[1];//ID
                $add['name']               = $name[1];//名称
                $add['name_traditional']   = $Traditional[1];//繁体名称
                $add['parent_id']          = $val[2];
                $add['type']               = $val[3];
                $add['type_name']          = $type;//类别名称
                $add['other_name']         = $name[3];//别称
                $add['name_format']        = $val[1][0];//格式化全称
                $this->province_city_ext[] = $add;
            } else {
                if (strpos($val[1][0], '已合并到') !== false) {
                    $type = '已合并到';
                    preg_match("/(.*)\s已合并到(.*)/iu", $val[1][0], $name);
                    preg_match("/(.*)\s已合並到.*/iu", $val[1][1], $Traditional);
                    //                    $ext .= "$val[0]\t{$name[1]}\t{$Traditional[1]}\t$val[2]\t$val[3]\t$type\t{$name[2]}\n";
                    $add                       = [];
                    $add['id']                 = $val[0];//ID
                    $add['name']               = $name[1];//名称
                    $add['name_traditional']   = $Traditional[1];//繁体名称
                    $add['parent_id']          = $val[2];
                    $add['type']               = $val[3];
                    $add['type_name']          = $type;//类别名称
                    $add['other_name']         = $name[2];//别称
                    $add['name_format']        = $val[1][0];//格式化全称
                    $this->province_city_ext[] = $add;
                } elseif (strpos($val[1][0], '已更名为') !== false) {
                    $type = '已更名为';
                    preg_match("/(.*)\s已更名为(.*)/iu", $val[1][0], $name);
                    preg_match("/(.*)\s已更名為.*/iu", $val[1][1], $Traditional);
                    //                    $ext .= "$val[0]\t{$name[1]}\t{$Traditional[1]}\t$val[2]\t$val[3]\t$type\t{$name[2]}\n";
                    $add                       = [];
                    $add['id']                 = $val[0];//ID
                    $add['name']               = $name[1];//名称
                    $add['name_traditional']   = $Traditional[1];//繁体名称
                    $add['parent_id']          = $val[2];
                    $add['type']               = $val[3];
                    $add['type_name']          = $type;//类别名称
                    $add['other_name']         = $name[2];//别称
                    $add['name_format']        = $val[1][0];//格式化全称
                    $this->province_city_ext[] = $add;
                } elseif (strpos($val[1][0], '属于') !== false) {
                    $type = '属于';
                    preg_match("/(.*)\s属于(.*)/iu", $val[1][0], $name);
                    preg_match("/(.*)\s屬於.*/iu", $val[1][1], $Traditional);
                    //                    $ext .= "$val[0]\t{$name[1]}\t{$Traditional[1]}\t$val[2]\t$val[3]\t$type\t{$name[2]}\n";
                    $add                       = [];
                    $add['id']                 = $val[0];//ID
                    $add['name']               = $name[1];//名称
                    $add['name_traditional']   = $Traditional[1];//繁体名称
                    $add['parent_id']          = $val[2];
                    $add['type']               = $val[3];
                    $add['type_name']          = $type;//类别名称
                    $add['other_name']         = $name[2];//别称
                    $add['name_format']        = $val[1][0];//格式化全称
                    $this->province_city_ext[] = $add;
                } elseif (preg_match("/(.*)\s(.*)/iu", $val[1][0], $name)) {
                    $type = '又名';
                    preg_match("/(.*)\s(.*)/iu", $val[1][1], $Traditional);
                    $name[2] = str_replace('(', '', $name[2]);
                    $name[2] = str_replace(')', '', $name[2]);
                    //                    $ext     .= "$val[0]\t{$name[1]}\t{$Traditional[1]}\t$val[2]\t$val[3]\t$type\t{$name[2]}\n";
                    $add                       = [];
                    $add['id']                 = $val[0];//ID
                    $add['name']               = $name[1];//名称
                    $add['name_traditional']   = $Traditional[1];//繁体名称
                    $add['parent_id']          = $val[2];
                    $add['type']               = $val[3];
                    $add['type_name']          = $type;//类别名称
                    $add['other_name']         = $name[2];//别称
                    $add['name_format']        = $val[1][0];//格式化全称
                    $this->province_city_ext[] = $add;
                } else {
                    //                    $html .= "$val[0]\t{$val[1][0]}\t{$val[1][1]}\t$val[2]\t$val[3]\t \t\n";
                    $add                     = [];
                    $add['id']               = $val[0];//ID
                    $add['name']             = $val[1][0];//名称
                    $add['name_traditional'] = $val[1][1];//繁体名称
                    $add['parent_id']        = $val[2];
                    $add['type']             = $val[3];
                    $add['type_name']        = '';//类别名称
                    $add['other_name']       = '';//类别名称
                    $add['name_format']      = '';//格式化全称
                    $this->province_city[]   = $add;
                }

            }
        }
        //        echo $html;
        //        echo "\n\n\n";
        //        //扩展数据
        //        echo $ext;
    }

    /**
     * 处理 香港 澳门
     */
    private function processGangAo()
    {
        $html = '';
        foreach ($this->gang_ao as $val) {
            //            $html .= "$val[0]\t{$val[1][0]}\t{$val[1][1]}\t$val[2]\n";
            $add                     = [];
            $add['id']               = $val[0];//ID
            $add['name']             = $val[1][0];//名称
            $add['name_traditional'] = $val[1][1];//繁体名称
            $add['parent_id']        = $val[2];
            $add['type']             = '';
            $add['type_name']        = '';//类别名称
            $add['other_name']       = '';//别称
            $add['name_format']      = '';//格式化全称
            $this->province_city[]   = $add;
        }
        //        echo $html;
    }

    /**
     * 处理台湾
     */
    private function processTaiWan()
    {
        $html = '';
        foreach ($this->taiwan as $val) {
            //            $html .= "$val[0]\t{$val[1][0]}\t{$val[1][1]}\t$val[2]\n";
            $add                     = [];
            $add['id']               = $val[0];//ID
            $add['name']             = $val[1][0];//名称
            $add['name_traditional'] = $val[1][1];//繁体名称
            $add['parent_id']        = $val[2];
            $add['type']             = '';
            $add['type_name']        = '';//类别名称
            $add['other_name']       = '';//别称
            $add['name_format']      = '';//格式化全称
            $this->province_city[]   = $add;
        }
        //        echo $html;
    }

    /**
     * 国家
     */
    private function processCountry()
    {
        $html = '';
        if ($this->other_countries) {
            $add                     = [];
            $add['id']               = 1;//ID
            $add['name']             = '中国';//名称
            $add['name_traditional'] = '中國';//繁体名称
            $add['name_en']          = 'China';//英文
            $add['parent_id']        = 0;
            $add['type']             = '';
            $add['type_name']        = '';//类别名称
            $add['other_name']       = '';//
            $add['name_format']      = '';//格式化全称
            $this->province_city[]   = $add;
            $add                     = [];
            $add['id']               = 125;//ID
            $add['name']             = '马来西亚';//名称
            $add['name_traditional'] = '馬來西亞';//繁体名称
            $add['name_en']          = 'Malaysia';//英文
            $add['parent_id']        = 0;
            $add['type']             = '';
            $add['type_name']        = '';//类别名称
            $add['other_name']       = '';//
            $add['name_format']      = '';//格式化全称
            $this->province_city[]   = $add;

            foreach ($this->other_countries as $key => $val) {
                //            $html .= "$key\t{$val[0]}\t{$val[1]}\t$val[2]\n";
                $add                     = [];
                $add['id']               = $val[0];//ID
                $add['name']             = $val[1][0];//名称
                $add['name_traditional'] = $val[1][1];//繁体名称
                $add['name_en']          = $val[1][2];//英文
                $add['parent_id']        = 0;
                $add['type']             = '';
                $add['type_name']        = '';//类别名称
                $add['other_name']       = '';//
                $add['name_format']      = '';//格式化全称
                $this->province_city[]   = $add;
            }
        }
        //        echo $html;
    }

    /**
     * 马来西亚
     */
    private function processMaLaiXiYa()
    {
        $html = '';
        foreach ($this->ma_lai_xi_ya as $key => $val) {
            //             $html .= "$val[0]\t{$val[1][0]}\t{$val[1][1]}\t{$val[1][2]}\t$val[2]\n";
            $add                     = [];
            $add['id']               = $val[0];//ID
            $add['name']             = $val[1][0];//名称
            $add['name_traditional'] = $val[1][1];//繁体名称
            $add['name_en']          = $val[1][2];//英文
            $add['parent_id']        = $val[2];
            $add['type']             = 0;
            $add['type_name']        = '';//类别名称
            $add['other_name']       = '';//别称
            $add['name_format']      = '';//格式化全称
            $this->province_city[]   = $add;
        }
        //        echo $html;
    }

    /**
     * 生成CSV文件
     */
    private function makeCsv()
    {
        $sql   = [];
        $sql[] = "id\t名称\t繁体\t英文\t上级ID\t类别\t类别名称\t别名\t全称";
        //省市区
        foreach ($this->province_city as $val) {
            $val['id']               = (int)$val['id'];
            $val['parent_id']        = (int)$val['parent_id'];
            $val['type']             = (int)$val['type'];
            $val['name']             = trim($val['name']);
            $val['name_traditional'] = trim($val['name_traditional']);
            $val['type_name']        = trim($val['type_name']);
            $val['other_name']       = trim($val['other_name']);
            $val['name_en']          = isset($val['name_en']) ? trim($val['name_en']) : '';
            //类别为 0 没有别名
            if ($val['type'] > 0) {
                $val['name_format'] = $val['name'] . "(" . $val['type_name'] . $val['other_name'] . ")";
            } else {
                $val['name_format'] = '';
            }
            $sql[] = "{$val['id']}\t{$val['name']}\t{$val['name_traditional']}\t{$val['name_en']}\t{$val['parent_id']}\t{$val['type']}\t{$val['type_name']}\t{$val['other_name']}\t{$val['name_format']}";
        }
        //扩展
        foreach ($this->province_city_ext as $val) {
            $val['id']               = (int)$val['id'];
            $val['parent_id']        = (int)$val['parent_id'];
            $val['type']             = (int)$val['type'];
            $val['name']             = trim($val['name']);
            $val['name_traditional'] = trim($val['name_traditional']);
            $val['type_name']        = trim($val['type_name']);
            $val['other_name']       = trim($val['other_name']);
            //类别为 0 没有别名
            if ($val['type'] > 0) {
                $val['name_format'] = $val['name'] . "(" . $val['type_name'] . $val['other_name'] . ")";
            } else {
                $val['name_format'] = '';
            }
            $sql[] = "{$val['id']}\t{$val['name']}\t{$val['name_traditional']}\t\t{$val['parent_id']}\t{$val['type']}\t{$val['type_name']}\t{$val['other_name']}\t{$val['name_format']}";
        }
        //
        //写入存储文件
        file_put_contents($this->getTmpPath() . 'area.csv', implode("\n", $sql));
    }

    private function makeSql()
    {
        $sql   = [];
        $sql[] = <<<EOF
CREATE TABLE `area` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` CHAR(50) DEFAULT '' COMMENT '名称',
  `name_traditional` VARCHAR(50) DEFAULT '' COMMENT '繁体名称',
  `name_en` VARCHAR(100) DEFAULT '' COMMENT '英文名称',
  `parent_id` INT(11) DEFAULT '0' COMMENT '上级栏目ID',
  `type` TINYINT(4) DEFAULT '0' COMMENT '类别;0默认;1又名;2;3属于;11已合并到;12已更名为',
  `sort` INT(11) DEFAULT '0' COMMENT '排序',
  `type_name` VARCHAR(50) DEFAULT '' COMMENT '类别名称',
  `other_name` VARCHAR(50) DEFAULT '' COMMENT '根据类别名称填写',
  `name_format` CHAR(80) DEFAULT NULL COMMENT '格式化全称',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`parent_id`,`sort`) USING BTREE,
  KEY `name` (`name`),
  KEY `name_format` (`name_format`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='地区表';

CREATE TABLE `area_ext` (
  `ext_id` INT(11) NOT NULL AUTO_INCREMENT,
  `id` INT(11) DEFAULT '0' COMMENT 'ID',
  `name` CHAR(50) DEFAULT '' COMMENT '名称',
  `name_traditional` VARCHAR(50) DEFAULT '' COMMENT '繁体名称',
  `name_en` VARCHAR(100) DEFAULT '' COMMENT '英文名称',
  `parent_id` INT(11) DEFAULT '0' COMMENT '上级栏目ID',
  `type` TINYINT(4) DEFAULT '0' COMMENT '类别;0默认;1又名;2;3属于;11已合并到;12已更名为',
  `sort` INT(11) DEFAULT '0' COMMENT '排序',
  `type_name` VARCHAR(50) DEFAULT '' COMMENT '类别名称',
  `other_name` VARCHAR(50) DEFAULT '' COMMENT '根据类别名称填写',
  `name_format` CHAR(80) DEFAULT NULL COMMENT '格式化全称',
  PRIMARY KEY (`ext_id`),
  KEY `id` (`id`,`parent_id`,`sort`) USING BTREE,
  KEY `name_format` (`name_format`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='地区扩展表';

EOF;
        foreach ($this->province_city as $val) {
            $val['id']               = (int)$val['id'];
            $val['parent_id']        = (int)$val['parent_id'];
            $val['type']             = (int)$val['type'];
            $val['name']             = trim($val['name']);
            $val['name_traditional'] = trim($val['name_traditional']);
            $val['type_name']        = trim($val['type_name']);
            $val['other_name']       = trim($val['other_name']);
            $val['name_en']          = isset($val['name_en']) ? trim($val['name_en']) : '';
            //类别为 0 没有别名
            if ($val['type'] > 0) {
                $val['name_format'] = $val['name'] . "(" . $val['type_name'] . $val['other_name'] . ")";
            } else {
                $val['name_format'] = '';
            }
            $sql[] = "INSERT INTO `area` (`id`,`name`,`name_traditional`,`name_en`,`parent_id`,`type`,`type_name`,`other_name`,`name_format`)VALUE ('{$val['id']}', '{$val['name']}', '{$val['name_traditional']}', '{$val['name_en']}', '{$val['parent_id']}', '{$val['type']}', '{$val['type_name']}', '{$val['other_name']}', '{$val['name_format']}');";
        }
        //扩展
        foreach ($this->province_city_ext as $val) {
            $val['id']               = (int)$val['id'];
            $val['parent_id']        = (int)$val['parent_id'];
            $val['type']             = (int)$val['type'];
            $val['name']             = trim($val['name']);
            $val['name_traditional'] = trim($val['name_traditional']);
            $val['type_name']        = trim($val['type_name']);
            $val['other_name']       = trim($val['other_name']);
            $val['name_en']          = isset($val['name_en']) ? trim($val['name_en']) : '';
            //类别为 0 没有别名
            if ($val['type'] > 0) {
                $val['name_format'] = $val['name'] . "(" . $val['type_name'] . $val['other_name'] . ")";
            } else {
                $val['name_format'] = '';
            }
            $sql[] = "INSERT INTO `area_ext` (`id`,`name`,`name_traditional`,`name_en`,`parent_id`,`type`,`type_name`,`other_name`,`name_format`)VALUE ('{$val['id']}', '{$val['name']}', '{$val['name_traditional']}', '{$val['name_en']}', '{$val['parent_id']}', '{$val['type']}', '{$val['type_name']}', '{$val['other_name']}', '{$val['name_format']}');";
        }
        //写入存储文件
        file_put_contents($this->getTmpPath() . 'area.sql', implode("\n", $sql));
    }

    /**
     * 处理扩展数据
     */
    private function processExtData()
    {
        if ($this->ext_data) {
            $tmp = explode("\n", $this->ext_data);
            foreach ($tmp as $val) {
                $item = explode("\t", $val);
                if (isset($item[0])) {
                    if ($item[0]) {
                        $add                     = [];
                        $add['id']               = $item[0];//ID
                        $add['name']             = $item[1];//名称
                        $add['name_traditional'] = '';//繁体名称
                        $add['name_en']          = '';//英文
                        $add['parent_id']        = $item[4];
                        $add['type']             = 0;
                        $add['type_name']        = '';//类别名称
                        $add['other_name']       = '';//别称
                        $add['name_format']      = '';//格式化全称
                        $this->province_city[]   = $add;
                    }
                }

            }
        }
    }

    public function process()
    {
        //下载文件
        $this->downLoadUrl();
        //处理内容
        $this->getJsContent();
        //省
        $this->processProvince();
        //市区
        $this->processArea();
        //处理 扩展数据
        $this->processExtData();
        //处理 香港 澳门
        $this->processGangAo();
        //台湾
        $this->processTaiWan();
        if ($this->is_country) {
            $this->processCountry();
            $this->processMaLaiXiYa();
        }
        if ($this->make_csv) {
            $this->makeCsv();
        }
        if ($this->make_sql) {
            $this->makeSql();
        }
        echo "make SUCCESS";
        return true;
    }
}