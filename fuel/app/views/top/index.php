<style>
    label {
        display:inline;
    }
    footer {
        position: fixed !important;
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 20px;
        background-color: #000;
        text-align: right;
        color: #999;
    }    
</style>
<script type="text/javascript">

var makeHighChartYAxisLabel = function(value) {
    var ret;
    if (Math.floor(value / Math.pow(10, 8)) >= 1) {
        ret = Math.floor(value / Math.pow(10, 8)) + ' 億円';
    } else if (Math.floor(value / Math.pow(10, 5)) >= 1) {
        ret = Math.floor(value / Math.pow(10, 5)) + ' 万円';
    } else {
        ret = Math.floor(value / Math.pow(10, 3)) + ' 千円';
    }
    return ret;
}


function chart(graphData, grapType) {
    var chart;    
    var chart_format = {
        chart: {
            renderTo: 'container1',
            type: grapType,
            marginRight: 130,
            marginBottom: 45            
        },
        title: {
            text: graphData['title'],
            x: -20 //center
        },
        subtitle: {
            text: '売上情報一覧グラフ',
            x: -20
        },
        xAxis: graphData['xAxis'],
        yAxis:[
            {
                title: {
                    text: '合計金額'
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold',
                        color: (Highcharts.theme && Highcharts.theme.textColor) || 'red'
                    }
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }],
                labels: {
                    formatter: function() {
                        return makeHighChartYAxisLabel(this.value);
                    }
                }
            }
        ],
        tooltip: {
            formatter: function() {
                return '<b>'+ this.x +'</b><br/>'+
                    this.series.name +': '+ this.y;
            }
        },        
        legend: {
            align: 'center',
            x: 0,
            verticalAlign: 'bottom',
            y: 15,
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white',
            borderColor: '#CCC',
            borderWidth: 1,
            shadow: false
            
        },

        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
//                    backgroundColor: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'rgba(0,0,0,0.4)'
                }
            }
        }
    }        
    
    //見た目とデータを入れ替えたいのでここで条件分岐
    if (grapType === 'column') {
        
        //積み上げグラフの場合totalデータは含んではいけないのでそのデータだけ省く
        //totalデータは，先頭に付与されているので，先頭以外のデータを取得する
        chart_format['series'] = graphData['series'].slice(1);
    
    } else if (grapType === 'line') {
        chart_format['series'] = graphData['series']
    console.log(chart_format['series']);

        //棒グラフの合計金額の表示設定
        chart_format['yAxis'][0]['stackLabels']['enabled'] = false;
        
        //棒グラフの金額表示設定
        chart_format['plotOptions']['column']['dataLabels']['enabled'] = false;
        

        chart_format['yAxis'].push({
                title: {
                    text: '日別合計金額'
                },
                labels: {
                    formatter: function() {
                        return makeHighChartYAxisLabel(this.value);
                    }
                },
                min: 0,
                opposite: true
            });
            
    } else {
        return ;
    }
    

    
    chart = new Highcharts.Chart(chart_format);
    
}
</script>
<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand">グラフ切り替え</a>
        </div>
    </div>
</div>
<div class="container">
    <h1>グラフ切り替えテスト</h1>
    <h2>検索用</h2>
    <form>
        <div class="row">
            <div class="span2">
                <?php (\Input::get('graph_type') == 'site')? $checked = 'checked': $checked = ''; ?>
                <input id="radio_site" type="radio" name="type" value="site" <?php echo $checked;?>>
                <label for="radio_site">サイト別</label>
            </div>
            <div class="span2">
                <?php (\Input::get('graph_type') == 'platform')? $checked = 'checked':$checked ='';?>
                <input id="radio_pl" type="radio" name="type" value="platform" <?php echo $checked;?>>
                <label for="radio_pl">プラットフォーム別 </label>
            </div>
        </div>
        <div class="row">
            <div class="span2">
                <input id="radio_grap_column" type="radio" name="graph" value="1" checked>
                <label for="radio_grap_column">積み上げグラフ</label>
            </div>
            <div class="span2">
                <input id="radio_grap_line" type="radio" name="graph" value="2">
                <label for="radio_grap_line">折れ線グラフ</label>
            </div>
        </div>
    </form>
    <div id="container1" style="min-width: 300px; height: 450px; margin: 0 auto" stroke-width="0.000001"></div>
    </div>

</body>
<script>
$(function() {
    
    var graphData = <?php echo json_encode($view_data['graph_data']); ?>;
    chart(graphData, 'column');
    
    $('input[name="graph"]').change(function (){
        var num = Number(this.value);
        if (num === 1) {
            console.log('積み上げグラフ');
            chart(graphData, 'column');
            
        } else if (num === 2) {
            console.log('折れ線グラフ');            
            chart(graphData, 'line');
        }
    });
    
    $('input[name="type"]').change(function (){
        location.href = '?graph_type=' + this.value;
    });    
});
</script>
</html>
