<?php

class gradeModel extends Model
{
	public function grade_cache() {
        if (false !== F('grade_list')) {
           return F('grade_list');
         }
        else{
        $grade_list = $this->field("grade,min,max")->order("min asc,id asc")->select();
        F('grade_list', $grade_list);
        }
        return F('grade_list');
    }
}