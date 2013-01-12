<?php

/**
 * The Welcome Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 * 
 * @package  app
 * @extends  Controller
 */
require_once 'graph.php';
class Controller_Top extends Controller_Common
{

    public function action_index() {        
        $graph_type = \Input::get('graph_type');        
        if ($graph_type !== 'site' && $graph_type !== 'platform') {
            $graph_type = 'site';
        }
        
        $graph = new Graph();
        //本来ならば引数に売上データを渡したい
        $report_data = null;
        $select_list = null;
        $this->view_data['graph_data'] = $graph->get_payment_reports_graph($report_data, $graph_type, $select_list);
        $this->viewContent('top/index', 'グラフ');
    }
}
