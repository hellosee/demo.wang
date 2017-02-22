<?php
/*
 * MCMS Copyright (c) 2012-2013 ZhangYiYeTai Inc.
 *
 *  http://www.mcms.cc
 *
 * The program developed by loyjers core architecture, individual all rights reserved,
 * if you have any questions please contact loyjers@126.com
 */

class Vars {
    private $fields =array();
    public function __construct($global_vars) {
        $this->fields = $global_vars;
    }
    /**
     * 增加或者重设一个节点
     * @param $node 节点名称 如 yesno
     * @param $field 节点内容 如 array(array('value'=>'','txt'=>'','txt_color'=>''),...)
     */
    public function set_fields($node,$field){
        $this->fields[$node]=$field;
    }
    public function get_fields($node=''){
        return $node=='' ? $this->fields : $this->fields[$node];
    }
    //某个借点插入一项
    public function set_field($node,$field,$pos=0){
        $tmp=$this->get_fields($node);
        array_splice($tmp,$pos,0,array($field));
        $this->set_fields($node,$tmp);
    }
    /**
     * 返回某个节点的某个值对应的数组数组
     * @param $node 节点名称
     * @param $value 节点值
     */
    public function get_field($node, $value,$is_false=false) {
        foreach($this->fields[$node] as $v) {
            if ($v['value'] == $value) {
                return $v;
            }
        }
        if($is_false == true) return false;
        return array('value' => '', 'txt' => '-', 'txt_color' => '');
    }

    /**
     * 根据值，返回某个节点某个值对应的文本或者HTML
     * @param $node 节点名称
     * @param $value 节点值
     * @param $type 返回字符串类型，txt或者html
     */
    public function get_field_str($node, $value, $type = 'txt') {
        $field = $this->get_field($node, $value,true); //print_r($field);
        if($field === false) return $field;
        if ($type == 'txt') {
            return $field['txt'];
        } else {
            return '<font style="color:' . $field['txt_color'] . '">' . $field['txt'] . '</font>';
        }
    }
    /**
     * 输出HTML表单
     * @param $params 参数数组 array('node'=>'','type'=>'','default'=>'')
     * @param =>type 表单类型 select,checkbox,radio
     * @param =>node    节点
     * @param =>default 默认选中
     * @param =>name    表单名称后缀，用于一个页面多次出现时候区分
     * @param =>alias 别名，用于同值但是文字相同的表单
     * @param =>stype 模拟下拉框的样式
     * @param =>on 表单函数 click,change等
     */
    public function input_str($params) {
        // 初始化
        $node = isset($params['node'])?$params['node']:'';
        $type = isset($params['type'])?$params['type']:'select';
        $default = isset($params['default'])?$params['default']:'';
        $name = isset($params['name'])?$params['name']:'';
        $on = isset($params['on'])?$params['on']:'';
        $alias = isset($params['alias'])?$params['alias']:'';
        $style = isset($params['style'])?$params['style']:'style="width:120px"';

        // 下拉框
        if ($type == 'select') {
            $html = '<select name="' . ($alias==''?$node.$name:$alias.$name) . '" '.$on.' id="' . $node . $name . '">';
            foreach($this->fields[$node] as $f) {
                $select = '';
                if (strlen($default) > 0 && $f['value'] == $default) $select = ' selected';
                $html .= '<option value="' . $f['value'] . '"' . $select . '>' . $f['txt'] . '</option>';
            }
            $html .= '</select>';
            return $html;
        }
        // 单选框
        if ($type == 'radio') {
            $html = '';
            foreach($this->fields[$node] as $f) {
                $select = '';
                if (strlen($default) > 0 && $f['value'] == $default) $select = ' checked';
                $html .= '&nbsp;&nbsp;<input type="radio" '.$on.' name="' . ($alias==''?$node.$name:$alias.$name) . '" value="' . $f['value'] . '"' . $select . '>&nbsp;' . $f['txt'] . '';
            }
            return $html;
        }
        // 复选框
        if ($type == 'checkbox') {
            $html = '';
            foreach($this->fields[$node] as $f) {
                $select = '';
                $df_val=explode(',',$default);
                if (strlen($default) > 0 && in_array($f['value'],$df_val)) $select = ' checked';
                $html .= '<span class="cbx_wrap"><input '.$on.' type="checkbox"  class="' . ($alias==''?$node.$name:$alias.$name) . '" name="' . ($alias==''?$node.$name:$alias.$name) . '" value="' . $f['value'] . '"' . $select . '><label for="' . $node . $name . '">&nbsp;&nbsp;' . $f['txt'] . '&nbsp;&nbsp;</label></span>';
            }
            return $html;
        }
        // 模拟下拉单选框
        if($type=='select_single'){
            $html = '<div class="sel_box" onclick="select_single(event,this);return false;" '.$style.'>';
            $html .= '    <a href="javascript:void(0);" class="txt_box" id="txt_box">';
            $html .= '        <div class="sel_inp" id="sel_inp">'.$this->get_field_str($node,$default).'</div>';
            $html .= '        <input type="hidden" name="'.($alias==''?$node.$name:$alias.$name).'" id="'.($alias==''?$node.$name:$alias.$name).'" value="'.$default.'" class="sel_subject_val">';
            $html .= '    </a>';
            $html .= '    <div class="sel_list" id="sel_list" style="display:none;">';
            foreach($this->fields[$node] as $f) {
                $select = '';
                if (strlen($default) > 0 && $f['value'] === $default) $select = 'current';
                $html .= '        <a href="javascript:void(0);" onclick="'.(empty($on)?'':$on).'" value="' . $f['value'] . '" class="'.$select.'" >' . $f['txt'] . '</a>';
            }
            $html .= '    </div>';
            $html .= '</div>';
            return $html;
        }
        // 模拟下拉多选框
        if($type=='select_multi'){
            $html = '<div class="sel_box duo_sel_box"  '.$style.'>';
            $html .= '        <input type="hidden" name="'.($alias==''?$node.$name:$alias.$name).'" id="'.($alias==''?$node.$name:$alias.$name).'" value="'.$default.'" class="sel_subject_val">';
            $html .= '<div class="sel_list" id="sel_list">';
            foreach($this->fields[$node] as $f) {
                $select = '';
                if (strlen($default) > 0 && in_array($f['value'], explode(',',$default))) $select = 'current';
                $html .= '        <a href="javascript:void(0);" onclick="select_multi(event,this);'.(empty($on)?'':$on).'" value="' . $f['value'] . '" class="'.$select.'">' . $f['txt'] . '</a>';
            }
            $html .= '</div>';
            $html .= '</div>';
            return $html;
            }
        return '-';
    }

    /**
     * 输出代码值下拉框，对应 mcms_code 表
     * @param $params
     */
    public function input_code_select($params){
        $node = isset($params['node'])?$params['node']:'';//节点变量名
        $path = isset($params['path'])?$params['path']:'';//多级下拉菜单
        $default = isset($params['default'])?$params['default']:'';//默认值
        $childs=explode(',',$path);
        $childs_val=explode(',',$default);
        if($default=='') {
            foreach($childs as $k=>$v){
                $childs_val[$k]='';
            }
        }

        $html='<select id="'.$childs[0].'" name="'.$childs[0].'">';
        $html.='<option value="">请选择</option>';
        foreach($params['node'] as $k=>$v){
            $selected_str='';
            if($childs_val[0]==$k) $selected_str=' selected';
            $html.='<option value="'.$k.'"'.$selected_str.'>'.$v['txt'].'</option>';
        }
        $html.='</select>';
        //开始子级菜单

        $i=0;
        foreach($childs as $child){
            if($i>0){
                $html.='&nbsp;&nbsp;<select id="'.$child.'" name="'.$child.'">';
                $html.='<option value="">请选择</option>';
                if($i==1){
                    $nlist=isset($node[$childs_val[0]])?$node[$childs_val[0]]['son']:array();
                    foreach($nlist as $k=>$v){
                        $selected_str='';
                        if($childs_val[1]==$k) $selected_str=' selected';
                        $html.='<option value="'.$k.'"'.$selected_str.'>'.$v['txt'].'</option>';
                    }
                }
                if($i==2){
                    $nlist=isset($node[$childs_val[0]]['son'][$childs_val[1]])?$node[$childs_val[0]]['son'][$childs_val[1]]['son']:array();
                    foreach($nlist as $k=>$v){
                        $selected_str='';
                        if($childs_val[2]==$k) $selected_str=' selected';
                        $html.='<option value="'.$k.'"'.$selected_str.'>'.$v['txt'].'</option>';
                    }
                }
                if($i==3){
                    $nlist=isset($node[$childs_val[0]]['son'][$childs_val[1]]['son'][$child_val[2]])?$node[$childs_val[0]]['son'][$childs_val[1]]['son'][$child_val[2]]['son']:array();
                    foreach($nlist as $k=>$v){
                        $selected_str='';
                        if($childs_val[3]==$k) $selected_str=' selected';
                        $html.='<option value="'.$k.'"'.$selected_str.'>'.$v['txt'].'</option>';
                    }
                }
                $html.='</select>';
            }
            $i++;
        }
        return $html;
    }
    /**
     * 输出checkbox复选框，对应 mcms_code 表
     * @param $params
     */
    public function input_code_checkbox($params){
        $node = isset($params['node'])?$params['node']:'';//节点变量名
        $name = isset($params['name'])?$params['name']:'';//checkbox
        $default = isset($params['default'])?$params['default']:'';//默认值
        $arr=explode(',',$default);
        $html='';
        foreach($node as $k=>$v){
            $select='';
            if (in_array($k,$arr)) $select = ' checked';
            $html .= '<span style="white-space:nowrap;display:inline-block;"><input type="checkbox" name="' . $name . '" value="' . $k . '"' . $select . '>&nbsp;' . $v['txt'] . '&nbsp;&nbsp;</span>';
        }
        return $html;
    }


}
