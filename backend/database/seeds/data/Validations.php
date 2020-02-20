<?php
use App\BaseModel;
//MimeTypeExtensionGuesser 

$validations = [];

$validations['numeric_min']['display_name'] = 'En az (nümerik)';
$validations['files_type']['display_name'] = 'Yalnızca resim';
$validations['files_count']['display_name'] = 'Dosya sayısı';
$validations['no_self']['display_name'] = 'Yetki grubuna kendini ekleyemesin';
$validations['name_not_start_deleted']['display_name'] = 'İsim "deleted_" ile başlayamasın';
$validations['no_change']['display_name'] = 'Data değiştirilemez';
$validations['valid_validations']['display_name'] = 'Olmayan doğrulama kuralı yazılamaz!';
$validations['column_table_relation_control']['display_name'] = 'İlişkili kolon ilişki kontrolü';
$validations['cron']['display_name'] = 'Crontab sözdizimi kontrolü';
$validations['select_updated_at']['display_name'] = 'Güncelleyen kullanıcı kolonu kontrolü';



$validations['numeric_min']['php_code'] = '<?php
$return = (int)$value >= (int)$parameters[0];
?>';

$validations['files_type']['php_code'] = '<?php

if(is_array($value))
    foreach($value as $file)
    {
        $control = FALSE;
        if(is_array($parameters))
            foreach($parameters as $param)
            {
                if(strstr($file->getMimeType(), $param))
                {
                    $control = TRUE;
                    break;
                }
            }
        
        if($control == FALSE)
        {
            $return = FALSE;
            return;
        }
    }

$return = TRUE;
?>';

$validations['files_count']['php_code'] = '<?php
if(!is_array($value)) return TRUE;
    
if($parameters[0] == \'<\')
    $return = (count($value) < $parameters[1]);
else if($parameters[0] == \'>\')
    $return = (count($value) > $parameters[1]);
else
    $return = (count($value) == $parameters[0]);
?>';

$validations['no_self']['php_code'] = '<?php
if(\Request::segment(7) != \'update\' || \Request::segment(5) != \'auth_groups\')
{
    $return = TRUE;
    return;
}

$id = (int)\Request::segment(6);
$value = json_decode($value);

$return = !in_array($id, $value);
?>';


$validations['name_not_start_deleted']['php_code'] = '<?php
global $pipe;
if($pipe["table"] != "tables" && $pipe["table"] != "columns")
{
    $return = TRUE;
    return;
}

$return = (substr($value, 0, 8) != "deleted_");
?>';

$validations['no_change']['php_code'] = '<?php 
if(\Request::segment(7) != \'update\') 
{
    $return = TRUE;
    return;
}

$id = (int)\Request::segment(6);

global $pipe;

$temp = get_attr_from_cache($pipe[\'table\'], \'id\', $id, \'data_filter_type_id\');
$return = ($temp == $value);
?>';

$validations['valid_validations']['php_code'] = '<?php 
$temp = explode(\':\', $value)[0];
$temp = get_attr_from_cache(\'validations\', \'name\', $temp, \'id\');
$return = ($temp != NULL);
?>';

$validations['column_table_relation_control']['php_code'] = '<?php
$relationGuiTypeIds = [];
array_push($relationGuiTypeIds, get_attr_from_cache(\'column_gui_types\', \'name\', \'select\', \'id\'));
array_push($relationGuiTypeIds, get_attr_from_cache(\'column_gui_types\', \'name\', \'multiselect\', \'id\'));
array_push($relationGuiTypeIds, get_attr_from_cache(\'column_gui_types\', \'name\', \'multiselectdragdrop\', \'id\'));

$guiTypeId = \Request::input(\'column_gui_type_id\');
if(!in_array($guiTypeId, $relationGuiTypeIds)) 
{
    $return = TRUE;
    return;
}

$return  = (strlen($value) > 0);

?>';

$validations['cron']['php_code'] = '<?php
$result = preg_match( "/^((?:[1-9]?\d|\*)\s*(?:(?:[\/-][1-9]?\d)|(?:,[1-9]?\d)+)?\s*){5}$/", $value, $matches); 
$return  = (count($matches) == 2);
?>';

$validations['select_updated_at']['php_code'] = '<?php

$return = FALSE;

if($value == \'[]\') return;

$value = json_decode($value);
foreach($value as $columnRelationId)
{
    $columnId = get_attr_from_cache(\'data_source_col_relations\', \'id\', $columnRelationId, \'column_id\');
    $columnName = get_attr_from_cache(\'columns\', \'id\', $columnId, \'name\');
    if($columnName == \'updated_at\')
    {
        $return = TRUE;
        break;
    }
}
?>';

$validations['numeric_min']['error_message'] = 'Değer en az :parameters[0] olmalıdır';
$validations['files_type']['error_message'] = 'Dosya tipi yalnızca :parameters[0] olabilir.';
$validations['files_count']['error_message'] = 'Dosya sayısı yalnızca :parameters[0] olabilir.';
$validations['no_self']['error_message'] = 'Yetkiye kendisini ekleyemezsiniz!';
$validations['name_not_start_deleted']['error_message'] = 'İsim "deleted_" ile başlayamaz';
$validations['no_change']['error_message'] = 'Bu veri değiştirilemez.';
$validations['valid_validations']['error_message'] = 'Böyle bir doğrulama kuralı yok!';
$validations['column_table_relation_control']['error_message'] = 'İlişkili kolon için bir data ilişkisi seçmelisiniz!';
$validations['cron']['error_message'] = 'Geçerisiz bir zamanlayıcı girdiniz! (cron syntax)';
$validations['select_updated_at']['error_message'] = 'Güncelleyen kullanıcı kolonu seçilmelidir!';
        

$temp = $this->get_base_record();

foreach($validations as $name => $array)
{
    $temp['name'] = $name;
    $temp['display_name'] = $array['display_name'];
    $temp['php_code'] = $array['php_code'];
    $temp['error_message'] = $array['error_message'];
    
    $validations[$name] = new BaseModel('validations', $temp);
    $validations[$name]->save();
}




$column_validations = [];
$column_validations['required'] = NULL;
$column_validations['integer'] = NULL;
$column_validations['numeric'] = NULL;
$column_validations['unique'] = NULL;
$column_validations['date_format:"Y-m-d H:i:s"'] = NULL;
$column_validations['date_format:"Y-m-d"'] = NULL;
$column_validations['date_format:"H:i:s"'] = NULL;
$column_validations['boolean'] = NULL;
$column_validations['boolean_custom'] = NULL;
$column_validations['email'] = NULL;
$column_validations['numeric_min:1'] = NULL;
$column_validations['min:5'] = NULL;
$column_validations['files_type:image'] = NULL;
$column_validations['files_count:1'] = NULL;
$column_validations['no_self'] = NULL;
$column_validations['active_url'] = NULL;
$column_validations['unique:users,email'] = NULL;
$column_validations['url'] = NULL;
$column_validations['ip'] = NULL;
$column_validations['json'] = NULL;
$column_validations['nullable'] = NULL;
$column_validations['no_self'] = NULL;
$column_validations['name_not_start_deleted'] = NULL;
$column_validations['no_change'] = NULL;
$column_validations['valid_validations'] = NULL;
$column_validations['column_table_relation_control'] = NULL;
$column_validations['cron'] = NULL;
$column_validations['select_updated_at'] = NULL;

$temp = $this->get_base_record();

foreach($column_validations as $validation => $null)
{
    $temp['validation_with_params'] = $validation;
    
    $column_validations[$validation] = new BaseModel('column_validations', $temp);
    $column_validations[$validation]->save();
}