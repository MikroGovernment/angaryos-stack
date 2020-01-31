<?php

namespace App\BaseModelTraits;

use App\Libraries\ColumnClassificationLibrary;

use App\BaseModel;
use DB;

trait BaseModelGetRelationDataTrait
{
    private function fillRelationData($column)
    {
        $params = helper('get_null_object');
        $params->column = $column;
        $params->record = $this;
        
        ColumnClassificationLibrary::relation(  $this, 
                                                __FUNCTION__, 
                                                $params->column, 
                                                NULL, 
                                                $params);
    }
    
    public function fillRelationDataForDataSource($params)
    {
        $dataSourceCode = get_attr_from_cache('column_data_sources', 'id', $params->relation->column_data_source_id, 'php_code');
                
        $repository = NULL;
        eval(helper('clear_php_code', $dataSourceCode));
        
        $params->record->{$params->column->name . '__relation_data'} = $repository->getRecordsBySourceData(json_encode($params->data_array));
        
        /*if(!function_exists('getFromDataSource'.$params->column->name))
        {
            eval(helper('clear_php_code', $dataSource->php_code));
        }
        
        $params->column->db_type_name = $params->column->getRelationData('column_db_type_id')->name;
        $functionName = 'getFromDataSource'.$params->column->name;
        $params->record->{$params->column->name . '__relation_data'} = $functionName(
                                                                                        __FUNCTION__, 
                                                                                        $params->column->db_type_name, 
                                                                                        json_encode($params->data_array));*/
    }
    
    public function fillRelationDataForJoinTableIds($params)
    {
        $relationTable = get_model_from_cache('column_table_relations', 'id', $params->column->column_table_relation_id);
        $table = $relationTable->getRelationData('relation_table_id');
        
        $temp = new BaseModel($table->name);
        $model = $temp->getQuery();
        
        $temp->addJoinsWithColumns($model, [$params->column], TRUE);
        
        $model->addSelect(DB::raw($table->name.'.id as id'));
        
        $source = $relationTable->relation_source_column;
        if(!strstr($source, '.')) $source = $table->name.'.'.$source;        
        $model->addSelect(DB::raw($source.' as source'));        
        
        $display = $relationTable->relation_display_column;
        if(!strstr($display, '.')) $display = $table->name.'.'.$display;        
        $model->addSelect(DB::raw($display.' as display'));
        
        $model->whereIn($source, $params->data_array)->get();
        
        $temp->addFilters($model, $table->name);
        
        $recs = $model->get();
        foreach($recs as $key => $value)
        {
            $recs[$key]->_source_column = $recs[$key]->source;
            $recs[$key]->_display_column = $recs[$key]->display;
            $recs[$key]->_source_column_name = 'source';
            $recs[$key]->_display_column_name = 'display';
            
            $key = (int)array_search($value->id, $params->data_array);
            $sorted[$key] = $value;
        }
        
        $recs = [];
        for($i = 0; $i < count($sorted); $i++)
            array_push ($recs, $sorted[$i]);
        
        if($params->column->column_db_type_id == $params->relation->column_db_type_id) 
            $recs = $recs[0];
        
        $params->record->{$params->column->name . '__relation_data'} = $recs;
    }
    
    public function fillRelationDataForTableIdAndColumnIds($params)
    {
        $table = get_attr_from_cache('tables', 'id', $params->relation->relation_table_id, 'name');
        $source = get_attr_from_cache('columns', 'id', $params->relation->relation_source_column_id, 'name');
        $display = get_attr_from_cache('columns', 'id', $params->relation->relation_display_column_id, 'name');
        
        $sorted = [];
        $temp = new BaseModel($table);
        $temp = $temp->whereIn($source, $params->data_array)->get();
        foreach($temp as $key => $value)
        {
            $temp[$key]->_source_column = $temp[$key]->{$source};
            $temp[$key]->_display_column = $temp[$key]->{$display};
            $temp[$key]->_source_column_name = $source;
            $temp[$key]->_display_column_name = $display;
            
            $key = (int)array_search($value->id, $params->data_array);
            $sorted[$key] = $value;
        }
        
        $temp = [];
        for($i = 0; $i < count($sorted); $i++)
            array_push ($temp, $sorted[$i]);
        
        if($params->column->column_db_type_id == $params->relation->column_db_type_id) 
            $temp = @$temp[0];
        
        $params->record->{$params->column->name . '__relation_data'} = $temp;
    }
    
    public function fillRelationDataForRelationSql($params)
    {
        if($params->data_array == [])
            $temp = [];
        else
        {   
            $params->data_array = '('.implode(',', $params->data_array).')';
            $sql = $params->record->sql_injection_where(
                    $params->relation->relation_sql, 
                    $params->relation->relation_source_column, 
                    'in', 
                    $params->data_array);

            $temp = DB::select($sql);
        }
        
        foreach($temp as $key => $value)
        {
            $temp[$key]->_source_column = $temp[$key]->{$params->relation->relation_source_column};
            $temp[$key]->_display_column = $temp[$key]->{$params->relation->relation_display_column};
            $temp[$key]->_source_column_name = $params->relation->relation_source_column;
            $temp[$key]->_display_column_name = $params->relation->relation_display_column;
        }
        
        if($params->column->column_db_type_id == $params->relation->column_db_type_id) $temp = @$temp[0];
        
        $params->record->{$params->column->name . '__relation_data'} = $temp; 
    }
    
    private function fill_relation_for_with_join_table_ids($params)
    {
        dd("asdasdasd11");
    }
}