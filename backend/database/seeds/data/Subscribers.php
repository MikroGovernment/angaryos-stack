<?php
use App\BaseModel;


$subscriber_types = [];

$subscriber_types['before'] = 'Kaydetmeden Önce';
$subscriber_types['after'] = 'Kaydettikten Sonra';

$temp = $this->get_base_record();

foreach($subscriber_types as $name => $display_name)
{
    $temp['name'] = $name;
    $temp['display_name'] = $display_name;
    
    $subscriber_types[$name] = new BaseModel('subscriber_types', $temp);
    $subscriber_types[$name]->save();
}




$subscribers = [];
$subscribers['table']['tables'][0] =
[
    'name_basic' => 'Veritabanı tablo işlemleri için trigger',
    'subscriber_type_id' => $subscriber_types['before']->id,
    'php_code' => '<?php
$params = 
[
    "type" => $type,
    "table" => $table,
    "column" => $column,
    "subscriber" => $subscriber,
    "requests" => $requests,
    "user" => $user,
    "record" => $record            
];
$helper = new App\Libraries\TableDBOperationsLibrary();
$return = $helper->TableEvent($params);
?>'
];

$subscribers['table']['tables'][1] =
[
    'name_basic' => 'Tabloyla ilişkili kolon işlemleri için trigger',
    'subscriber_type_id' => $subscriber_types['after']->id,
    'php_code' => '<?php
if($type != \'create\') return;

$helper = new App\Libraries\TableDBOperationsLibrary();
$return = $helper->CreateRelationColumnsForTable($record);
?>'
];

$subscribers['table']['tables'][2] =
[
    'name_basic' => 'Yeni  GeoServer işlemleri için before trigger',
    'subscriber_type_id' => $subscriber_types['after']->id,
    'php_code' => '<?php
$params =
[
    "type"=> $type,
    "table" => $record 
];
$helper = new App\Libraries\TableGeoServerOperationsLibrary();
$return = $helper->TableEvent($params);
?>'
];

$subscribers['table']['tables'][3] =
[
    'name_basic' => 'Yönetici için tam yetki oluşturma trigger',
    'subscriber_type_id' => $subscriber_types['after']->id,
    'php_code' => '<?php
if($type != "create") return;

$helper = new App\Libraries\TableDBOperationsLibrary();
$return = $helper->AddTableFullAuthToAdminUser($record);
?>'
];

$subscribers['table']['columns'][0] =
[
    'name_basic' => 'Veritabanı kolon işlemleri için trigger',
    'subscriber_type_id' => $subscriber_types['before']->id,
    'php_code' => '<?php
$params = 
[
    "type" => $type,
    "table" => $table,
    "column" => $column,
    "subscriber" => $subscriber,
    "requests" => $requests,
    "user" => $user,
    "record" => $record    
];
$helper = new App\Libraries\TableDBOperationsLibrary();
$return = $helper->ColumnEvent($params);
?>'
];

$subscribers['table']['sub_point_types'][0] =
[
    'name_basic' => 'Revize katman işlemleri için trigger',
    'subscriber_type_id' => $subscriber_types['before']->id,
    'php_code' => '<?php
$params = 
[
    "type" => $type,
    "table" => $table,
    "column" => $column,
    "subscriber" => $subscriber,
    "requests" => $requests,
    "user" => $user,
    "record" => $record    
];
$helper = new App\Libraries\CustomLayerOperationsLibrary();
$return = $helper->TableEvent($params);
?>'
];

$subscribers['table']['data_sources'][0] =
[
    'name_basic' => 'Veri kaynağından tabloları ve kolonları okumak için trigger',
    'subscriber_type_id' => $subscriber_types['after']->id,
    'php_code' => '<?php
$params = 
[
    "type" => $type,
    "table" => $table,
    "column" => $column,
    "subscriber" => $subscriber,
    "user" => $user,
    "record" => $record    
];
$helper = new App\Libraries\DataSourceOperationsLibrary();
$return = $helper->TableEvent($params);
?>'
];


$subscribers['column']['profile_picture'][0] =
[
    'name_basic' => 'Dosya upload işlemleri için trigger',
    'subscriber_type_id' => $subscriber_types['before']->id,
    'php_code' => '<?php 
$params =
[
    "columnName"=> $column->name,
    "type" => $type 
];
$helper = new \App\Libraries\FileLibrary();
$return = $helper->fileUploadEvent($params);     
?>'
];

$subscribers['column']['password'][0] =
[
    'name_basic' => 'Şifreleri şifrelemek için trigger',
    'subscriber_type_id' => $subscriber_types['before']->id,
    'php_code' => '<?php
if($type != \'create\' && $type != \'update\') return;

if(strlen($value) > 0) $pass =  \Hash::make($value);
else
{
    if($type == \'update\')
        $pass = get_attr_from_cache($table->name, \'id\', \Request::segment(6), $column->name);
    else 
        $pass == NULL;
}

$return = [$column->name => $pass]; 
?>'
];




foreach($subscribers as $type => $set)
{
    foreach($set as $table => $subs)
    {
        foreach ($subs as $i => $sub)
        {
            $temp = $this->get_base_record();
            $temp['name_basic'] = $sub['name_basic'];
            $temp['subscriber_type_id'] = $sub['subscriber_type_id'];
            $temp['php_code'] = $sub['php_code'];
            
            $subscribers[$type][$table][$i] = new BaseModel('subscribers', $temp);
            $subscribers[$type][$table][$i]->save();
        }   
    }
}

$subscribers['column']['image'][0] = $subscribers['column']['profile_picture'][0];

$subscribers['table']['sub_linestring_types'][0] = $subscribers['table']['sub_point_types'][0];
$subscribers['table']['sub_polygon_types'][0] = $subscribers['table']['sub_point_types'][0];
$subscribers['table']['sub_tables'][0] = $subscribers['table']['sub_point_types'][0];
