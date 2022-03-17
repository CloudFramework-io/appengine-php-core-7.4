<?php

/**
 * Class CFA to handle WebApps for CloudFrameworkInterface
 * notion: xxxx
 * last_update: 20220226
 */
class CFA
{
    private $core;
    var $data = ['rows'=>[['label'=>'default_row']],'components'=>[]];
    var $labels=[];
    var $colors = null;

    /**
     * CFI constructor.
     * @param Core7 $core
     * @param string $bucket
     */
    function __construct(Core7 &$core, $bucket = '')
    {
        $this->core = $core;
        $this->colors = new CFAColors();
    }


    /**
     * SET rows in the CFA to structure the greed
     * @param string $row_labels labels for each row to be used in the CFA
     * @return CFA
     */
    public function rowLabels(string $row_labels){
        if(!$row_labels) return;

        $this->data['rows'] = [];
        $row_labels = explode(",",$row_labels);
        foreach ($row_labels as $label) {
            $this->data['rows'][] = ['label'=>trim($label)];
        }
        return $this;
    }

    /**
     * Add a class attribute to a specifuc row label
     * @param string $row_label
     * @param string $class
     * @return CFA
     */
    public function addClass(string $row_label, string $class){
        if(!$row_label) return;
        foreach ($this->data['rows'] as $i=>$row) {
            if($row['label']==$row_label) $this->data['rows'][$i]['class'] = $class;
        }
        return $this;
    }



    /**
     * Add a class attribute to a specifuc row label
     * @param string $row_label
     * @param string $class
     * @return CFA
     */
    public function addComponentInLabel(string $label){
        if(!isset($this->labels[$label])) $this->labels[$label] = new CFACompenent();
        return($this->labels[$label]);
    }

    /**
     * Return the CFA structure $this->data
     */
    public function getData($only_label=''){
        foreach ($this->labels as $label=>$content) if(!$only_label || $only_label==$label) {
            $this->data['components'][] = [
                'label'=>$label,
                'component'=>$content->component->type,
                'content'=>$content->component->data
            ];
        }
        return $only_label?['components'=>$this->data['components']]:$this->data;
    }

    /**
     * Return the CFA structure $this->data
     */
    public function getJSON($only_label=''){
        return(json_encode($this->getData($only_label),JSON_PRETTY_PRINT));
    }

}
/*
 * Class to handle fields in CFI
 * last_update: 20200502
 */
class CFACompenent
{

    var $component = null;

    public function header($title='') {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentHeader')
            $this->component = new CFACompenentHeader();
        if($title) $this->component->title($title);
        return($this->component);
    }

    public function boxes() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentBoxes')
            $this->component = new CFACompenentBoxes();
        return($this->component);
    }

    public function html() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentHTML')
            $this->component = new CFACompenentHTML();
        return($this->component);
    }

    public function cols() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentCols')
            $this->component = new CFACompenentCols();
        return($this->component);
    }

    public function panels() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentPanels')
            $this->component = new CFACompenentPanels();
        return($this->component);
    }

    public function divs() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentDivs')
            $this->component = new CFACompenentDivs();
        return($this->component);
    }

    public function titles() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentTitles')
            $this->component = new CFACompenentTitles();
        return($this->component);
    }

    public function buttons() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentButtons')
            $this->component = new CFACompenentButtons();
        return($this->component);
    }

    public function formSelect() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentFormSelect')
            $this->component = new CFACompenentFormSelect();
        return($this->component);
    }

    public function breadcrumb() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentBreadcrumb')
            $this->component = new CFACompenentBreadcrumb();
        return($this->component);
    }

    public function tabs() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentTabs')
            $this->component = new CFACompenentTabs();
        return($this->component);
    }

    public function tags() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentTags')
            $this->component = new CFACompenentTags();
        return($this->component);
    }

    public function alerts() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentAlerts')
            $this->component = new CFACompenentAlerts();
        return($this->component);
    }

    public function searchCards() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentSearchCards')
            $this->component = new CFACompenentSearchCards();
        return($this->component);
    }

    public function calendar() {	
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentCalendar')	
            $this->component = new CFACompenentCalendar();	
        return($this->component);	
    }

    public function accordion() {
        if(!is_object($this->component) || get_class($this->component)!= 'CFACompenentAccordion')
            $this->component = new CFACompenentAccordion();
        return($this->component);
    }
}
/**
 * CFAColors Class component
 */
class CFAColors
{
    var $primary = 'primary';
    var $success = 'success';
    var $warning = 'warning';
    var $info = 'info';
    var $danger = 'danger';
    var $secondary = 'secondary';
}
/**
 * CFACompenentHeader Class component
 */
class CFACompenentHeader
{

    var $type = 'header';
    var $data = [
        'ico'=>null,
        'title'=>null,
        'subtitle'=>null,
        'js-call'=>null,
        'js-ico'=>null,
    ];

    public function icon($data) {$this->data['ico'] = $data; return $this;}
    public function title($data) {$this->data['title'] = $data; return $this;}
    public function subtitle($data) {$this->data['subtitle'] = $data; return $this;}
    public function jsIconCall($js_function,$icon) {$this->data['js-call'] = $js_function;$this->data['js-ico'] = $icon; return $this;}
}




/**
 * CFACompenentButtons Class component
 */
class CFACompenentHTML
{
    var $type = 'html';
    var $data = ['html'=>''];
    public function plain($data) {$this->data['html'].= $data;return $this;}
    public function h1($data,$label='') {$this->data['html'].= "<h1".(($label)?' id="'.$label.'"':'').">{$data}</h1>";return $this;}
    public function h2($data,$label='') {$this->data['html'].= "<h2".(($label)?' id="'.$label.'"':'').">{$data}</h2>";return $this;}
    public function h3($data,$label='') {$this->data['html'].= "<h3".(($label)?' id="'.$label.'"':'').">{$data}</h3>";return $this;}
    public function div($data,$label='') {$this->data['html'].= "<div".(($label)?' id="'.$label.'"':'').">{$data}</div>";return $this;}
    public function p($data,$label='') {$this->data['html'].= "<p".(($label)?' id="'.$label.'"':'').">{$data}</p>";return $this;}
    public function hr($label='') {$this->data['html'].= "<hr".(($label)?' id="'.$label.'"':'')."/>";return $this;}
    public function pre($data,$label='') {$this->data['html'].= "<pre".(($label)?' id="'.$label.'"':'').">{$data}</pre>";return $this;}
    public function textarea($data,$label='') {$this->data['html'].= "<textarea cols='90' rows='10'".(($label)?' id="'.$label.'"':'').">{$data}</textarea>";return $this;}
    public function testComponents($id,$json,$php) {
        $this->data['html'].= "
            <div  class='row'>
            <div  class='col-xl-6'>
            <small>textarea id: {$id}_code</small><br/>
            <textarea cols='90' rows='10' id='{$id}_code'>{$json}</textarea><br>
            <input type='button' onclick=\"CloudFrameWorkCFA.renderComponents(JSON.parse($('#{$id}_code').text()))\" value=\"CloudFrameWorkCFA.renderComponents(JSON.parse($('#{$id}_code').text()))\">
            </div>
            <div  class='col-xl-6'>
            <div  id='{$id}'>".htmlentities("<div  id='{$id}'></div>")."</div>
             </div>
             </div>
            ";
        return $this;
    }

}

/**
 * CFACompenentBoxes Class component
 */
class CFACompenentTitles
{

    var $type = 'titles';
    var $index =0;
    var $data = [];

    public function add($title='') {if(isset($this->data[$this->index]) &&$this->data[$this->index]) $this->index++; if($title) $this->data[$this->index]['title']=$title; return $this;}
    public function type($data) {$this->data[$this->index]['h'] = $data; return $this;}
    public function icon($data) {$this->data[$this->index]['ico'] = $data; return $this;}
    public function jsIconCall($js_function,$icon) {$this->data[$this->index]['js-call'] = $js_function;$this->data[$this->index]['js-ico'] = $icon; return $this;}
    public function title($data) {$this->data[$this->index]['title'] = $data; return $this;}
    public function subtitle($data) {$this->data[$this->index]['subtitle'] = $data; return $this;}
    public function active($data) {$this->data[$this->index]['active'] = (bool)$data; return $this;}
    public function onclick($data) {$this->data[$this->index]['onclick'] = $data; return $this;}
    public function addBadge($title,$color='',$border=false,$pill=false) {if(!isset($this->data[$this->index]['badges'])) $this->data[$this->index]['badges']=[]; $this->data[$this->index]['badges'][] = ['title'=>$title,'color'=>$color,'border'=>(bool)$border,'pill'=>(bool)$pill]; return $this;}
    public function addLeftPhoto($src,$alt='') {if(!isset($this->data[$this->index]['left-photos'])) $this->data[$this->index]['left-photos']=[]; $this->data[$this->index]['left-photos'][] = ['url'=>$src,'alt'=>$alt]; return $this;}
    public function addRightPhoto($src,$alt='') {if(!isset($this->data[$this->index]['right-photos'])) $this->data[$this->index]['right-photos']=[]; $this->data[$this->index]['right-photos'][] = ['url'=>$src,'alt'=>$alt]; return $this;}
    public function addPhoto($src,$alt='') {if(!isset($this->data[$this->index]['photos'])) $this->data[$this->index]['photos']=[]; $this->data[$this->index]['photos'][] = ['url'=>$src,'alt'=>$alt]; return $this;}

}
/**
 * CFACompenentBoxes Class component
 */
class CFACompenentBoxes
{

    var $type = 'boxes';
    var $index =0;
    var $data = [];

    public function add($title='') {if(isset($this->data[$this->index]) &&$this->data[$this->index]) $this->index++; if($title) $this->data[$this->index]['title']=$title; return $this;}
    public function title($data) {$this->data[$this->index]['title'] = $data; return $this;}
    public function ico($data) {$this->data[$this->index]['ico'] = $data; return $this;}
    public function color($data) {$this->data[$this->index]['color'] = $data; return $this;}
    public function total($data) {$this->data[$this->index]['total'] = $data; return $this;}
}
/**
 * CFACompenentCols Class component
 */
class CFACompenentCols
{

    var $type = 'cols';
    var $index =0;
    var $data = [];
    public function add($label='') {if(isset($this->data[$this->index]) &&$this->data[$this->index]) $this->index++; if($label) $this->data[$this->index]['label']=$label; return $this;}
    public function label($data) {$this->data[$this->index]['label'] = $data; return $this;}
    public function size($data) {$this->data[$this->index]['size'] = $data; return $this;}
}
/**
 * CFACompenentPanels Class component
 */
class CFACompenentPanels
{

    var $type = 'panels';
    var $index =0;
    var $data = [];
    public function add($label='') {if(isset($this->data[$this->index]) &&$this->data[$this->index]) $this->index++; if($label) $this->data[$this->index]['label']=$label; return $this;}
    public function label($data) {$this->data[$this->index]['label'] = $data; return $this;}
    public function size($data) {$this->data[$this->index]['size'] = $data; return $this;}
    public function locked($data) {$this->data[$this->index]['locked'] = (bool)$data; return $this;}
    public function collapse($data) {$this->data[$this->index]['collapse'] = (bool)$data; return $this;}
    public function show($data) {$this->data[$this->index]['show'] = (bool)$data; return $this;}
}
/**
 * CFACompenentDivs Class component
 */
class CFACompenentDivs
{

    var $type = 'divs';
    var $index =0;
    var $data = [];
    public function add($label='') {if(isset($this->data[$this->index]) &&$this->data[$this->index]) $this->index++; if($label) $this->data[$this->index]['label']=$label; return $this;}
    public function label($data) {$this->data[$this->index]['label'] = $data; return $this;}
    public function class($data) {$this->data[$this->index]['class'] = $data; return $this;}
    public function hide($data) {$this->data[$this->index]['hide'] = (bool)$data; return $this;}
}
/**
 * CFACompenentButtons Class component
 */
class CFACompenentButtons
{
    var $type = 'buttons';
    var $index =0;
    var $data = [];
    public function add($label='') {if(isset($this->data[$this->index]) &&$this->data[$this->index]) $this->index++; if($label) $this->data[$this->index]['label']=$label; return $this;}
    public function label($data) {$this->data[$this->index]['label'] = $data; return $this;}
    public function title($data) {$this->data[$this->index]['title'] = $data; return $this;}
    public function ico($data) {$this->data[$this->index]['ico'] = $data; return $this;}
    public function color($data) {$this->data[$this->index]['color'] = $data; return $this;}
    public function onclick($data) {$this->data[$this->index]['onclick'] = $data; return $this;}
}
/**
 * CFACompenentFormSelect Class component
 */
class CFACompenentFormSelect
{
    var $type = 'form-select';
    var $data = [
        'label'=>null,
        'title'=>null,
        'onchange'=>null,
        'options'=>[],
    ];
    public function label($data) {$this->data['label'] = $data; return $this;}
    public function title($data) {$this->data['title'] = $data; return $this;}
    public function onchange($data) {$this->data['onchange'] = $data; return $this;}
    public function addOption($value,$option,$selected=false) {$this->data['options'][] = ['value'=>$value,'option'=>$option,'selected'=>(bool)$selected]; return $this;}
}
/**
 * CFACompenentBreadcrumb Class component
 */
class CFACompenentBreadcrumb
{
    var $type = 'breadcrumb';
    var $data = [
        'label'=>null,
        'solid'=>null,
        'color'=>null,
        'elements'=>[],
    ];
    public function label($data) {$this->data['label'] = $data; return $this;}
    public function solid($data) {$this->data['solid'] = (bool)$data; return $this;}
    public function color($data) {$this->data['color'] = $data; return $this;}
    public function textColor($data) {$this->data['text-color'] = $data; return $this;}
    public function concatTitle($data,$javascript='') {$this->data['elements'][] = ['title'=>$data,'href'=>($javascript)?"javascript:{$javascript}":null]; return $this;}
    public function concatIco($ico,$javascript="") {$this->data['elements'][] = ['ico'=>$ico,'href'=>($javascript)?"javascript:{$javascript}":null]; return $this;}
    public function concatPhoto($src,$alt="") {$this->data['elements'][] = ['photo'=>['url'=>$src,'alt'=>$alt]]; return $this;}
}
/**
 * CFACompenentTabs Class component
 */
class CFACompenentTabs
{
    var $type = 'tabs';
    var $index = 0;
    var $data = [];

    public function add($label, $title, $icon = "",$active=false)
    {
        if (isset($this->data[$this->index]) && $this->data[$this->index]) $this->index++;
        $this->data[$this->index]['label'] = $label;
        $this->data[$this->index]['title'] = $title;
        if ($icon) $this->data[$this->index]['ico'] = $icon;
        if ($active) $this->data[$this->index]['active'] = true;
        return $this;
    }
}

/**
 * CFACompenentSearchCards Class component
 */
class CFACompenentSearchCards
{
    var $type = 'search-cards';
    var $index = 0;
    var $data = [
        'search_placeholder'=>null,
        'cards'=>[],
    ];


    public function searchPlaceHolder($data) {$this->data['search_placeholder'] = $data; return $this;}
    public function add() { if (isset($this->data['cards'][$this->index]) && $this->data['cards'][$this->index]) $this->index++;$this->data['cards'][$this->index] = [];return $this;}
    public function avatar($data) {$this->data['cards'][$this->index]['avatar'] = $data; return $this;}
    public function title($data) {$this->data['cards'][$this->index]['title'] = $data; return $this;}
    public function subtitle($data) {$this->data['cards'][$this->index]['subtitle'] = $data; return $this;}
    public function searchTags($data) {$this->data['cards'][$this->index]['tags'] = $data; return $this;}
    public function addBodyLine($title,$ico='') {if(!isset($this->data['cards'][$this->index]['lines'])) $this->data['cards'][$this->index]['lines']=[]; $this->data['cards'][$this->index]['lines'][] = ['title'=>$title,'ico'=>$ico]; return $this;}
    public function addTitleMenu($title,$javascript) {if(!isset($this->data['cards'][$this->index]['menu'])) $this->data['cards'][$this->index]['menu']=[]; $this->data['cards'][$this->index]['menu'][] = ['title'=>$title,'href'=>$javascript]; return $this;}
    public function addSubtitleBadge($title,$color,$border=false) {if(!isset($this->data['cards'][$this->index]['badges'])) $this->data['cards'][$this->index]['badges']=[]; $this->data['cards'][$this->index]['badges'][] = ['title'=>$title,'color'=>$color,'border'=>(bool)$border]; return $this;}
    public function addBottomAvatar($src,$alt='') {if(!isset($this->data['cards'][$this->index]['avatars'])) $this->data['cards'][$this->index]['avatars']=[]; $this->data['cards'][$this->index]['avatars'][] = ['url'=>$src,'alt'=>$alt]; return $this;}
    public function addBottomBrand($title,$ico,$link) {if(!isset($this->data['cards'][$this->index]['brands'])) $this->data['cards'][$this->index]['brands']=[]; $this->data['cards'][$this->index]['brands'][] = ['title'=>$title,'ico'=>$ico,'link'=>$link]; return $this;}
}


/**
 * CFACompenentButtons Class component
 */
class CFACompenentTags
{
    var $type = 'tags';
    var $index =0;
    var $data = [];
    public function add($label='') {if(isset($this->data[$this->index]) &&$this->data[$this->index]) $this->index++; if($label) $this->data[$this->index]['label']=$label; return $this;}
    public function label($data) {$this->data[$this->index]['label'] = $data; return $this;}
    public function title($data) {$this->data[$this->index]['title'] = $data; return $this;}
}


/**
 * CFACompenentButtons Class component
 */
class CFACompenentAlerts
{
    var $type = 'alerts';
    var $index =0;
    var $data = [];
    public function add($label='') {if(isset($this->data[$this->index]) &&$this->data[$this->index]) $this->index++; if($label) $this->data[$this->index]['label']=$label; return $this;}
    public function label($data) {$this->data[$this->index]['label'] = $data; return $this;}
    public function content($data) {$this->data[$this->index]['content'] = $data; return $this;}
    public function color($data) {$this->data[$this->index]['color'] = $data; return $this;}
    public function icon($data) {$this->data[$this->index]['ico'] = $data; return $this;}
    public function onclick($data) {$this->data[$this->index]['onclick'] = $data; return $this;}
    public function addPhoto($src,$alt='') {if(!isset($this->data[$this->index]['photo'])) $this->data[$this->index]['photo']=[]; $this->data[$this->index]['photo'][] = ['url'=>$src,'alt'=>$alt]; return $this;}
    public function jsIconCall($js_function,$icon) {$this->data[$this->index]['js-call'] = $js_function;$this->data[$this->index]['js-ico'] = $icon; return $this;}
    public function addBadge($title,$color='',$border=false,$pill=false) {if(!isset($this->data[$this->index]['badges'])) $this->data[$this->index]['badges']=[]; $this->data[$this->index]['badges'][] = ['title'=>$title,'color'=>$color,'border'=>(bool)$border,'pill'=>(bool)$pill]; return $this;}


}

/**	
 * CFACompenentSearchCards Class component	
 */	
class CFACompenentCalendar	
{	
    var $type = 'calendar';	
    var $index = 0;	
    var $data = [];

    // public function events($data) {$this->data['calendar'][$this->index]['events'] = json_decode('[{"title":"Product daily CFW", "start":"2022-03-07T16:00:00", "description":"Event description", "className":"border-warning bg-warning text-dark"}]'); return $this;}
    public function __construct()
    {
        $this->data['id'] = uniqid('calendar');
    }
    public function setCalendarClass($data) {$this->data['class'] = $data; return $this;}
    public function setCalendarId($data) {$this->data['id'] = $data; return $this;}
    public function add($title,$start,$end='') {if(isset($this->data['events'][$this->index]) && $this->data['events'][$this->index]) $this->index++; $this->data['events'][$this->index]['title']=$title;$this->data['events'][$this->index]['start']=$start; if($end)$this->data['events'][$this->index]['end']=$end; return $this;}
    public function title($data) {$this->data['events'][$this->index]['title'] = $data; return $this;}
    public function description($data) {$this->data['events'][$this->index]['description'] = $data; return $this;}
    public function start($data) {$this->data['events'][$this->index]['start'] = $data; return $this;}
    public function end($data) {$this->data['events'][$this->index]['end'] = $data; return $this;}
    public function javascript($data) {$this->data['events'][$this->index]['url'] = "javascript:".$data; return $this;}
    public function url($data) {$this->data['events'][$this->index]['url'] = $data; return $this;}
    public function color($bg,$text='',$border='') {
        if(!$border) $border=$bg;
        if(!$text) $text='white';
        $this->data['events'][$this->index]['className'] = "bg-{$bg} border-{$border} text-{$text}"; return $this;}

}

/**
 * CFACompenentAccordion Class component
 */
class CFACompenentAccordion
{
    var $type = 'accordion';
    var $index = 0;
    var $data = [];
    public function __construct() { $this->data['id'] = uniqid('accordion');}
    public function add($label) {if(isset($this->data['cards'][$this->index]) &&$this->data['cards'][$this->index]) $this->index++; if($label) $this->data['cards'][$this->index]['label']=$label; return $this;}
    public function title($data) {$this->data['cards'][$this->index]['title'] = $data; return $this;}
    public function subtitle($data) {$this->data['cards'][$this->index]['subtitle'] = $data; return $this;}
}