<?php
$rootpath = "../";
require_once($rootpath."widgets/widget.php");

$params = array(

	/*
	{
	    "widgetid": "stockchart1",
	    "iframe": "false",
	    "iframeheight": "320",
	    "widget": "chartstock.php"
	}
	*/

    'tbl'       => 'clqitem',											// Table
    'tblid'     => 'id',                                    			// Keyfield
    'tbltype'   => 'daily',                                             // Used to get Title
    'where'     => array('clq_reference|=|´{code}´', 'clq_type|=|´historical´'),  	// WHERE
    'orderby'   => 'id DESC',                         			// ORDER BY - ? Date
    'plot'      => array(

        // 0 = Fld name, 1 = Xtra fld, 2 = Field Type, 3 = 
        'code'     => 'clq_reference',
        'title'    => 'clq_title',
        'labels'   => 'clq_extra|date|date|56:Date',
        'values'   => 'clq_extra|close|numeric|40:Values',

    )
);

?>
<div class="" style="width: 300px; height:340px; overflow: auto; padding:0px; margin: 0px; float:left;">
<?php echo $clqw->displayStock($params) ?>
</div>