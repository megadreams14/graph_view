<?php

/*
 * Optlsで利用するグラフを生成する
 * format
 *
 * array(
 *     'title' => null,
 *     'xAxis' => array(
 *        'categories' => array('見出し', '見出し', ...)
 *     ),
 *     'series' => array(
 *          array('name' => null, 'color' => null, 'data' => array())        
 *     )
 * );
 * 
 * categoriesの長さと'series'の中の'data'の長さは同じである必要がある
 * 
 */

//PHP側でサイト別の場合はサイト別の合計を出力

class Graph {
    public $graph;
    public $graph_data;
    public $min = 1000000;
    public $max = 10000000;
    
    public $site_list = array(
//        'OOS', 'GNS', 'TRS', 'HNS', 'OQS', 'KJS', 'O2S'
        'サイト１', 'サイト２', 'サイト３', 'サイト４', 'サイト５', 'サイト６', 'サイト７'
    );
    public $pf_list = array(
        'GREE', 'Mobage', 'Mixi', 'Ameba', 'iOS', 'GooglePlay'
    );
    
    //各サイトのグラフの色
    public $color_list = array(
        /*
        'site'     => array(
            'OOS' => '#7E1717',
            'GNS' => '#694585',
            'TRS' => null,
            'HNS' => null,
            'OQS' => '#9acd32',
            'KJS' => '#ffa500',
            'O2S' => null,
        ),
        */
        'platform' => array(
            'GREE'       => null,
            'Mobage'     => null,
            'Mixi'       => null,
            'Ameba'      => null,
            'iOS'        => null,
            'GooglePlay' => null
        ),
        'site'     => array(
            'サイト１' => '#7E1717',
            'サイト２' => '#694585',
            'サイト３' => null,
            'サイト４' => null,
            'サイト５' => '#9acd32',
            'サイト６' => '#ffa500',
            'サイト７' => null,
        ),
              
    );
    
    public function __construct() {

    }
    
    public function create_test_data ($type) {
        if ($type === 'site') {
            $name_list = $this->site_list;
        } else {
            $name_list = $this->pf_list;            
        }
        
        $payment_data = array();
        for ($i = 0; $i < 10; $i++) {
            $data_array = array();
            foreach ($name_list as $name) {
                $data_array[$name] = rand($this->min, $this->max);                
            }
            $payment_data[date("Ymd", strtotime('+' . $i .' day'))] = $data_array;        
        }
        
        return $payment_data;
    }
    
    /*
     * @pram $payment_reports  array DBから取得した売上データ
     * @pram $type string サイト別，プラットフォーム別，複数指定のどれか
     * @pram $select_list array 選択したリスト
     */
     private function format_conversion($payment_reports, $type, $select_list) {
        $data = array();

        //1行ずつのレコードを date->key->keyの形に直している
        foreach ($payment_reports as $report) {
            //日付でグループ化を行う
            $date = date('Y-m-d', strtotime($report['report_date']));
            
            //データの初期化を行う（途中からデータがある場合，左詰めになってしまって日付がおかしくなるから）
            if (isset($data[$date]) === false) {
                foreach ($select_list as $name) {
                    $data[$date][$name] = 0;
                }
            }
            
            
            //売上データを代入する
            $sales = $report['sales']['sp'] + $report['sales']['fp'];
            
            //プラットフォーム、サイト別に分類する
            if ($type === 'site') {
                $site_name = $report['locate']['site_name'];
                
                //今後ここは要らなくなる　予定
                if (isset($data[$date][$site_name]) === false) {
                    $data[$date][$site_name] = 0;
                }
                $data[$date][$site_name] += (int)$sales;
                
            } else if ($type === 'platform') {
                $pf_name   = $report['locate']['platform'];

                //今後ここは要らなくなる　予定                
                if (isset($data[$date][$pf_name]) === false) {
                    $data[$date][$pf_name] = 0;
                }
                
                $data[$date][$pf_name] += (int)$sales;

            } else {
                //複数PF,Site指定の際に実行される
                
            }
        }
        return $data;

    }
    
    public function get_payment_reports_graph($report_data, $type, $select_list = null) { 

        //引数で受け取ったデータは，日別PF別SITE別の売上情報であるため，変換する作業が必要
        /*
        if (isset($select_list) === false) {
            if ($type === 'site') {
                $select_list = $this->site_list;                
            } else if ($type === 'platform') {
                $select_list = $this->pf_list;                
            }
        }
        $payment_report = $this->format_conversion($report_data, $select_list);
        */
        $payment_report = $this->create_test_data($type);
       
        
        //日付を取り出す
        $date_list = array();
        //各サイトorPFの名前を取り出す
        $name_list = array();
        $series_data = array();
        //1日の売上げデータを配列で格納
        $tota_sum_list = array();
        
        //売上集計データから情報の取り出し
        foreach ($payment_report as $date => $payment_data) {
            $date_list[] = date('m/d', strtotime($date));
            $day_sum = 0;
            foreach ($payment_data as $name => $val) {
                if (isset($series_data[$name]) === false) {
                    $series_data[$name] = array();
                    $name_list[] = $name;
                }
                $series_data[$name][] = $val;
                //１日の合計金額を計算している
                $day_sum += $val;
            }   
            $tota_sum_list[] = $day_sum;
        }

        //グラフに描画する値のフォーマットを作成する
        $series_list = array();
        $series_list[] = array(
            'type'  => 'column',
            'yAxis' => 1,
            'name'  => 'total',
            'data'  => $tota_sum_list
        );
        
        foreach ($name_list as $name) {
            $series_list[] = array(
                'name'  => $name,
                'color' => $this->color_list[$type][$name],
                'data'  => $series_data[$name]
            );
        }
        
        if ($type === 'site') {
            $graph_title = 'サイト別売上';
        } else if ($type === 'platform') {
            $graph_title = 'プラットフォーム別売上';
        } else {
            //複数PF,Site指定の時に実行
        }

        $graph = array(
            'title' => $graph_title,
            'xAxis' => array(
                'categories' => $date_list
            ),
            'series' => $series_list,
        );
//        $graph = json_encode($graph);
        
        return $graph;
    }
    

    
    
    
    
    //以下テスト用コード
    public function get_daily_payment_reports_111($type = null) { 
        $graph = array(
            'title' => 'サイト別売上',
            'xAxis' => array(
                'categories' => array('2013/01/01', '2013/01/02', '2013/01/03', '2013/01/04', '2013/01/05', '2013/01/06', '2013/01/07')
            ),
            'series' => array(
                array('name' => 'OOS', 'color' => '#7E1717', 'data' => $this->rand_fnc()),
                array('name' => 'GNS', 'color' => '#694585', 'data' => $this->rand_fnc()),
                array('name' => 'TRS', 'data' => $this->rand_fnc()),
                array('name' => 'HNS', 'data' => $this->rand_fnc()),
                array('name' => 'OQS', 'color' => '#9acd32', 'data' => $this->rand_fnc()),
                array('name' => 'KJS', 'color' => '#ffa500', 'data' => $this->rand_fnc()),
                array('name' => 'O2S', 'color' => '#690714', 'data' => $this->rand_fnc()),
                
            ),
        );
//        $graph = json_encode($graph);
        return $graph;
    }    
    private function rand_fnc () {
        $num = 7;
        $array = array();
        for ($i =0; $i < $num; $i++) {
            $array[] = rand(1000090, 1500000);
        }
        return $array;
    }
}