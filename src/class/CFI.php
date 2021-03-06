<?php

/**
 * [$cfi = $this->core->loadClass('CFI');] Class CFI to handle CFO app for CloudFrameworkInterface
 * https://www.notion.so/cloudframework/CFI-PHP-Class-c26b2a1dd2254ddd9e663f2f8febe038
 * last_update: 20200502
 * @package CoreClasses
 */
class CFI
{
    private $core;
    private $fields = [];
    private $buttons = [];
    var $json_object=['title'=>'Pending'
        ,'allow_copy'=>false
        ,'allow_delete'=>false
        ,'allow_display'=>false
        ,'allow_update'=>false
        ,'fields'=>[],
        'buttons'=>[],
        'close'=>'Cancel'
    ];

    /**
     * CFI constructor.
     * @param Core7 $core
     * @param string $bucket
     */
    function __construct (Core7 &$core, $bucket='')
    {
        $this->core = $core;
        $this->initApp('Set a title for the CFO');
    }

    /**
     * Init a CFI app
     * @param $title
     */
    public function initApp($title) {
        $this->json_object=[
            'title'=>$title,
            'allow_copy'=>false,
            'allow_delete'=>false,
            'allow_display'=>false,
            'allow_update'=>false,
            'tabs'=>[],
            'fields'=>[],
            'buttons'=>[],
            'close'=>'Cancel'
        ];
        $this->json_object['title']=$title;
        $this->fields = [];
    }

    /**
     * Return the App structure: $this->json_object
     */
    public function returnData(){return $this->getApp();}
    public function getApp() { return $this->json_object;}

    /**
     * Change title of the App
     * @param $title
     */
    public function setTile($title) {$this->json_object['title']=$title;}

    /**
     * Change title of the App
     * @param $title
     */
    public function addTab($title,$icon='home') {
        $index = count($this->json_object['tabs']);
        $this->json_object['tabs'][]=['title'=>$title,'ico'=>$icon,'active'=>($index==0?true:false),'index'=>$index];
    }

    /**
     * @param $field
     * @return CFIField
     */
    private function getField($field) {
        if(!isset($this->fields[$field])) $this->fields[$field] = new CFIField($this, $field);
        return $this->fields[$field];
    }

    /**
     * Return a CFIField field
     * @param $field
     * @return CFIField
     */
    public function field($field) { return $this->getField($field);}

    /**
     * Delete a field
     * @param $field
     */
    public function delete($field) { if(isset($this->fields[$field])) unset($this->fields[$field]); if(isset($this->json_object['fields'][$field])) unset($this->json_object['fields'][$field]);}

    /**
     * Internal method to return a button
     * @param $button
     * @return CFIButton
     */
    private function getButton($button) {
        if(!isset($this->buttons[$button])) $this->buttons[$button] = new CFIButton($this, $button);
        return $this->buttons[$button];
    }

    /**
     * Return a CFIButton $button
     * @param $button_title
     * @return CFIButton
     */
    public function button($button_title='Button') { return $this->getButton($button_title);}

    /**
     * set the title for close button
     * @param $title
     */
    public function closeButton($title) { $this->json_object['close']=$title;}
}

/*
 * Class to handle fields in CFI
 * last_update: 20200502
 */
class CFIField {

    private $cfi;
    private $field;

    /**
     * CFI constructor.
     * @param Core7 $core
     * @param string $bucket
     */
    function __construct (CFI &$cfi, $field)
    {
        $this->cfi = $cfi;
        $this->field = $field;
        $this->cfi->json_object['fields'][$this->field] = ['field'=>$field];
    }

    /**
     * Set a value for the field
     * @param $value
     * @return CFIField $this
     */
    public function value($value) { $this->cfi->json_object['fields'][$this->field]['value'] = $value; return $this;}

    /**
     * Set a title for the field
     * @param $title
     * @return CFIField $this
     */
    public function title($title) { $this->cfi->json_object['fields'][$this->field]['name'] = $title; return $this;}

    /**
     * Set a title for the field
     * @param int $n_tab Number of tab the field has to be shown 0..n
     * @return CFIField $this
     */
    public function tab(int $n_tab) {
        $n_tab = intval($n_tab);
        if(isset($this->cfi->json_object['tabs'][$n_tab]))
            $this->cfi->json_object['fields'][$this->field]['tab'] = $n_tab;
        return $this;
    }

    /**
     * Set if the field to readonly
     * @param boolean $read_only optional params. By default true
     * @return CFIField $this
     */
    public function readOnly($read_only=true) { $this->cfi->json_object['fields'][$this->field]['read_only'] = $read_only; return $this;}

    /**
     * Set if we will create a new row after he field
     * @param boolean $new_row optional params. By default true
     * @return CFIField $this
     */
    public function newRow($new_row=true) { $this->cfi->json_object['fields'][$this->field]['new_row'] = $new_row; return $this;}

    /**
     * Set if we will create a new row after he field
     * @param boolean $allow_empty optional params. By default true
     * @return CFIField $this
     */
    public function allowEmpty($allow_empty=true) { $this->cfi->json_object['fields'][$this->field]['allow_empty'] = $allow_empty; return $this;}

    /**
     * Set if the field to disabled and it will not be sent in the form submit
     * @param boolean $read_only optional params. By default true
     * @return CFIField $this
     */
    public function disabled($disabled=true) { $this->cfi->json_object['fields'][$this->field]['disabled'] = $disabled; return $this;}

    /**
     * Set if the field has to be represented as an image
     * @param bool $image
     * @param int $image_with_pixels
     * @param int $image_height_pixels
     * @return CFIField $this
     */
    public function image($image=true,int $image_with_pixels=0,int $image_height_pixels=0) {
        $this->cfi->json_object['fields'][$this->field]['image'] = $image;
        if($image_with_pixels) $this->cfi->json_object['fields'][$this->field]['image_width'] = $image_with_pixels;
        if($image_height_pixels) $this->cfi->json_object['fields'][$this->field]['image_height'] = $image_height_pixels;

        return $this;
    }

    /**
     * Set if the field to type json
     * @param string $title optional title
     * @return CFIField $this
     */
    public function json($title='') {
        $this->cfi->json_object['fields'][$this->field]['type'] = 'json';
        if($title) $this->cfi->json_object['fields'][$this->field]['name'] = $title;
        return $this;
    }

    /**
     * Set if the field to type textarea
     * @param string $title optional title
     * @return CFIField $this
     */
    public function textarea($title='') {
        $this->cfi->json_object['fields'][$this->field]['type'] = 'textarea';
        if($title) $this->cfi->json_object['fields'][$this->field]['name'] = $title;
        return $this;}
    // Deprecated by error
    public function texarea() { $this->cfi->json_object['fields'][$this->field]['type'] = 'textarea'; return $this;}

    /**
     * Set if the field to type select
     * @return CFIField $this
     */
    public function select(array $values,$defaultvalue='') {
        $this->cfi->json_object['fields'][$this->field]['type'] = 'select';
        $this->cfi->json_object['fields'][$this->field]['values'] = $values;
        if($defaultvalue)
            $this->cfi->json_object['fields'][$this->field]['defaultvalue'] = $defaultvalue;
        return $this;
    }
    /**
     * Set if the field to type select
     * @return CFIField $this
     */
    public function html($defaultvalue='') {
        if($defaultvalue)
            $this->cfi->json_object['fields'][$this->field]['defaultvalue'] = $defaultvalue;
        $this->cfi->json_object['fields'][$this->field]['type'] = 'html';
        return $this;
    }
    /**
     * Set if the field to type select
     * @return CFIField $this
     */
    public function multiselect(array $values,$defaultvalue='') {
        $this->cfi->json_object['fields'][$this->field]['type'] = 'multiselect';
        $this->cfi->json_object['fields'][$this->field]['values'] = $values;
        if($defaultvalue)
            $this->cfi->json_object['fields'][$this->field]['defaultvalue'] = $defaultvalue;
        return $this;
    }

    /**
     * Set if the field will be connected with external SQL table/view
     * @return CFIField $this
     */
    public function connectWithBigQuery($table,$fields,$linked_field) {
        $this->cfi->json_object['fields'][$this->field]['external_values'] = 'bq';
        $this->cfi->json_object['fields'][$this->field]['entity'] = $table;
        $this->cfi->json_object['fields'][$this->field]['fields'] = $fields;
        $this->cfi->json_object['fields'][$this->field]['linked_field'] = $linked_field;
        return $this;
    }

    /**
     * Set if the field to type iframe
     * @param $height integer optinal iframe height: default 400
     * @return CFIField $this
     */
    public function iframe($height=400) {
        $this->cfi->json_object['fields'][$this->field]['type'] = 'iframe';
        $this->cfi->json_object['fields'][$this->field]['iframe_height'] = $height;
        return $this;}

    /**
     * Set if the url for certain types like iframe
     * @param $value
     * @return CFIField $this
     */
    public function url($value) {
        if($this->cfi->json_object['fields'][$this->field]['type'] = 'iframe') {
            $this->cfi->json_object['fields'][$this->field]['iframe_url'] =$value;
        } else {
            $this->cfi->json_object['fields'][$this->field]['url'] =$value;
        }
        return $this;
    }

    /**
     * Set if the url for certain types like iframe
     * @param $value string content to be included in the iframe.Normally a HTML
     * @return CFIField $this
     */
    public function content($value) {
        if($this->cfi->json_object['fields'][$this->field]['type'] = 'iframe') {
            $this->cfi->json_object['fields'][$this->field]['iframe_content'] =$value;
        }
        return $this;
    }
}
/*
 * Class to handle buttons in CFI
 * last_update: 20200502
 */
class CFIButton {

    private $cfi;
    private $button;

    /**
     * CFI constructor.
     * @param Core7 $core
     * @param string $bucket
     */
    function __construct (CFI &$cfi, $button)
    {
        $this->cfi = $cfi;
        $this->button = $button;
        $this->cfi->json_object['buttons'][] = ['title'=>$button,'type'=>'form'];
        $this->button = &$this->cfi->json_object['buttons'][count($this->cfi->json_object['buttons'])-1];
    }

    /**
     * Set a value for the field
     * @param $value
     * @return CFIButton $this
     */
    public function title($title) { $this->button['title'] = $title; return $this;}

    /**
     * Set button color
     * @param $value
     * @return CFIButton $this
     */
    public function color($color) { $this->button['color'] = $color; return $this;}

    /**
     * Set button align
     * @param $align
     * @return CFIButton $this
     */
    public function align($align) { $this->button['align'] = $align; return $this;}

    /**
     * Assign url and method for an API call
     * @param $url
     * @param string $method optinal var to assign the type of call: GET, POST, PUT, DELETE
     * @return CFIButton $this
     */
    public function url($url,$method='GET') { $this->button['method'] = strtoupper($method);$this->button['url'] = $url; return $this;}

}
